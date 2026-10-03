<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\SanitizedHtml;
use App\Support\ImageStore;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Table('products')]
#[Fillable(['category_id', 'brand_id', 'name', 'slug', 'sku', 'hsn_code', 'tax_classification', 'tax_rate', 'short_description', 'description', 'price', 'compare_at_price', 'stock', 'low_stock_threshold', 'is_active', 'is_featured', 'featured_rank', 'is_trending', 'image', 'gallery', 'og_image', 'meta_title', 'meta_description'])]
class Product extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $casts = [
        'description' => SanitizedHtml::class,
        'gallery' => 'array',
        'price' => 'decimal:2',
        'tax_rate' => 'decimal:4',
        'compare_at_price' => 'decimal:2',
        'stock' => 'integer',
        'low_stock_threshold' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'featured_rank' => 'integer',
        'is_trending' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::updating(function (Product $product): void {
            if (! $product->isDirty('is_active') || ! $product->is_active) {
                return;
            }

            $source = $product->importSource()->first();
            if ($source === null) {
                return;
            }

            $hasStock = $product->stock > 0 || $product->variants()->where('is_active', true)->where('stock', '>', 0)->exists();

            if ($source->publication_reviewed_at === null
                || $source->commercial_use_approved_at === null
                || (float) $product->price <= 0
                || ! $hasStock
                || $product->image === null) {
                throw new \DomainException('Imported products require reviewed content, an approved local image, a positive price and verified real stock before activation.');
            }
        });

        // Uploaded files belong to the row: drop the ones it no longer points at,
        // and all of them once the row is gone for good (a soft-deleted product
        // keeps its images so a restore brings them back).
        static::updated(function (Product $product): void {
            ImageStore::deleteMany(array_diff($product->originalImageUrls(), $product->imageUrls()));
        });

        static::forceDeleted(function (Product $product): void {
            ImageStore::deleteMany($product->imageUrls());
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(ProductAttributeValue::class, 'product_attribute_value_product');
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function merchandisingRules(): HasMany
    {
        return $this->hasMany(ProductMerchandisingRule::class, 'source_product_id');
    }

    public function backInStockSubscriptions(): HasMany
    {
        return $this->hasMany(BackInStockSubscription::class);
    }

    public function importSource(): HasOne
    {
        return $this->hasOne(ProductImportSource::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function wishlistedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'wishlists')->withTimestamps();
    }

    public function seoEntry(): MorphOne
    {
        return $this->morphOne(SeoEntry::class, 'seoable');
    }

    /** Discount percentage (0 when no compare_at_price). */
    public function discountPercent(): int
    {
        if (! $this->compare_at_price || $this->compare_at_price <= $this->price) {
            return 0;
        }

        return (int) round((($this->compare_at_price - $this->price) / $this->compare_at_price) * 100);
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->low_stock_threshold;
    }

    /**
     * Main product image URL.
     *
     * 1. `products.image` — the URL of the file the admin uploaded
     *    (`/uploads/products/…`, stored in the row).
     * 2. Committed public asset: public/images/products/{slug}.jpg
     *    (reset-proof — travels with the git repo, needs no upload at all).
     * 3. null — caller decides the final placeholder.
     */
    public function heroImage(): ?string
    {
        return $this->image ?: $this->committedImageUrl();
    }

    /**
     * Card-sized product image URL.
     *
     * Uploads are served as stored — there is no conversion queue any more, so
     * the card uses the same file as the product page (the storefront scales it
     * with CSS). Kept as its own method so the views stay readable.
     */
    public function thumbnailImage(): ?string
    {
        return $this->heroImage();
    }

    /**
     * Every image of the product, main one first (extra gallery images follow).
     * Falls back to the committed asset when nothing was uploaded.
     *
     * @return list<string>
     */
    public function galleryImages(): array
    {
        return array_values(array_unique(array_filter([
            $this->heroImage(),
            ...array_map(
                fn ($url): ?string => is_string($url) ? ImageStore::url($url) : null,
                (array) ($this->gallery ?? []),
            ),
        ], fn (?string $url): bool => is_string($url) && $url !== '')));
    }

    /** Social-share image: the explicit `og_image` upload, else the main image. */
    public function ogImage(): ?string
    {
        return $this->og_image ?: $this->heroImage();
    }

    /**
     * Every uploaded-image URL this row owns (used to clean up replaced files).
     *
     * @return list<string>
     */
    public function imageUrls(): array
    {
        return array_values(array_filter([
            $this->image,
            $this->og_image,
            ...array_map(
                fn ($url): ?string => is_string($url) ? ImageStore::url($url) : null,
                (array) ($this->gallery ?? []),
            ),
        ], fn (?string $url): bool => is_string($url) && $url !== ''));
    }

    /** The URLs this row owned before the current save. @return list<string> */
    public function originalImageUrls(): array
    {
        $gallery = $this->getRawOriginal('gallery');

        if (is_string($gallery)) {
            $decoded = json_decode($gallery, true);
            $gallery = is_array($decoded) ? $decoded : [];
        }

        return array_values(array_filter([
            (string) $this->getRawOriginal('image'),
            (string) $this->getRawOriginal('og_image'),
            ...array_map(
                fn ($url): string => is_string($url) ? (string) ImageStore::url($url) : '',
                (array) ($gallery ?? []),
            ),
        ], fn (string $url): bool => $url !== ''));
    }

    /** Every written image value is normalised to the canonical URL (App\Support\ImageStore). */
    public function setImageAttribute(?string $value): void
    {
        $this->attributes['image'] = ImageStore::url($value);
    }

    public function setOgImageAttribute(?string $value): void
    {
        $this->attributes['og_image'] = ImageStore::url($value);
    }

    /** @param  list<string>|string|null  $value */
    public function setGalleryAttribute(mixed $value): void
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : [];
        }

        $urls = array_values(array_unique(array_filter(array_map(
            fn ($url): ?string => is_string($url) ? ImageStore::url($url) : null,
            (array) ($value ?? []),
        ), fn (?string $url): bool => is_string($url) && $url !== '')));

        $this->attributes['gallery'] = $urls === [] ? null : json_encode($urls);
    }

    private function committedImageUrl(): ?string
    {
        $file = 'images/products/'.$this->slug.'.jpg';

        return is_file(public_path($file)) ? '/'.$file : null;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where(function (Builder $available): void {
            $available->where('stock', '>', 0)
                ->orWhereHas('variants', fn (Builder $variant): Builder => $variant
                    ->where('is_active', true)
                    ->where('stock', '>', 0));
        });
    }

    public function scopeWithAvailableVariantStock(Builder $query): Builder
    {
        return $query->withExists([
            'variants as has_available_variant_stock' => fn (Builder $variant): Builder => $variant
                ->where('is_active', true)
                ->where('stock', '>', 0),
        ]);
    }

    public function hasAvailableStock(): bool
    {
        if ($this->stock > 0) {
            return true;
        }

        if (array_key_exists('has_available_variant_stock', $this->attributes)) {
            return (int) $this->attributes['has_available_variant_stock'] > 0;
        }

        if ($this->relationLoaded('variants')) {
            return $this->variants->contains(fn (ProductVariant $variant): bool => $variant->is_active && $variant->stock > 0);
        }

        return $this->variants()
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->exists();
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeTrending(Builder $query): Builder
    {
        return $query->where('is_trending', true);
    }

    public function scopeWhereCategory(Builder $query, int|string $category): Builder
    {
        return $query->where('category_id', $category);
    }
}
