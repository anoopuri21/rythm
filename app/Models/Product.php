<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\SanitizedHtml;
use App\Models\Concerns\SyncsResolvedMediaUrls;
use App\Models\Contracts\HasResolvedMediaUrls;
use App\Support\MediaDisk;
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
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[Table('products')]
#[Fillable(['category_id', 'brand_id', 'name', 'slug', 'sku', 'hsn_code', 'tax_classification', 'tax_rate', 'short_description', 'description', 'price', 'compare_at_price', 'stock', 'low_stock_threshold', 'is_active', 'is_featured', 'featured_rank', 'is_trending', 'meta_title', 'meta_description'])]
class Product extends Model implements HasMedia, HasResolvedMediaUrls
{
    use HasFactory;
    use InteractsWithMedia;
    use SoftDeletes;
    use SyncsResolvedMediaUrls;

    protected $casts = [
        'description' => SanitizedHtml::class,
        'gallery_urls' => 'array',
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
            $mediaApproved = $product->getMedia('gallery')->isNotEmpty()
                && $product->getMedia('gallery')->every(fn ($media): bool => (bool) $media->getCustomProperty('commercial_use_approved', false));

            if ($source->publication_reviewed_at === null
                || $source->commercial_use_approved_at === null
                || (float) $product->price <= 0
                || ! $hasStock
                || ! $mediaApproved) {
                throw new \DomainException('Imported products require reviewed content, approved local media, a positive price and verified real stock before activation.');
            }
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

    public function registerMediaCollections(): void
    {
        // Products + variants are the phase-1 Cloudinary collections
        // (docs/cloudinary-media.md): `MediaDisk` returns 'cloudinary' while the
        // rollout is on, MEDIA_DISK otherwise. Rows uploaded before the switch
        // keep the disk they were stored on, so their URLs do not change.
        $this->addMediaCollection('gallery')
            ->useDisk(MediaDisk::forCollection('gallery'));

        $this->addMediaCollection('og')
            ->singleFile()
            ->useDisk(MediaDisk::forCollection('og'));
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // Cloudinary generates these sizes at delivery time (App\Models\Media
        // maps the conversion names to delivery transformations). Queuing a
        // conversion here would download the original back to this server, burn
        // CPU on a WebP copy and re-upload it — for an image that is already
        // served from the CDN. So cloud media register no conversions at all.
        if (MediaDisk::isCloudinary($media?->disk)) {
            return;
        }

        // Gallery only: the `og` collection is handed to crawlers as-is (social
        // scrapers want JPEG/PNG), so generating 480/1200 WebP copies for it
        // would burn queue CPU and disk on shared hosting for nothing.
        //
        // Conversions run through the bounded, stop-when-empty scheduled
        // worker; no persistent shared-hosting daemon is required.
        $this->addMediaConversion('thumb-webp')
            ->performOnCollections('gallery')
            ->width(480)
            ->height(480)
            ->format('webp')
            ->quality(82)
            ->queued();

        $this->addMediaConversion('gallery-webp')
            ->performOnCollections('gallery')
            ->width(1200)
            ->height(1200)
            ->format('webp')
            ->quality(84)
            ->queued();
    }

    /**
     * Best available product image URL (large, for the product page / social).
     *
     * 1. The stored `gallery_urls` column (kept in sync by MediaUrlObserver) —
     *    the WebP conversion once the queue has generated it, else the original.
     * 2. Media Library, for rows the column has not been resolved for yet.
     * 3. Committed public asset: public/images/products/{slug}.jpg
     *    (reset-proof — travels with the git repo, needs no storage disk).
     * 4. null — caller decides the final placeholder.
     *
     * @see docs/media-architecture.md → M-7
     */
    public function heroImage(): ?string
    {
        return $this->gallery_urls[0]
            ?? $this->getFirstMedia('gallery')?->getAvailableUrl(['gallery-webp'])
            ?? $this->committedImageUrl();
    }

    /** Card-sized product image URL (same fallback chain as heroImage()). */
    public function thumbnailImage(): ?string
    {
        return $this->thumbnail_url
            ?? $this->getFirstMedia('gallery')?->getAvailableUrl(['thumb-webp'])
            ?? $this->committedImageUrl();
    }

    /** Social-share image URL (`og` collection), else the hero image. */
    public function ogImage(): ?string
    {
        return $this->og_image_url
            ?? $this->getFirstMedia('og')?->getUrl()
            ?? $this->heroImage();
    }

    /**
     * Gallery image URLs (stored column first, media fallback, committed fallback, else []).
     *
     * @return list<string>
     */
    public function galleryImages(): array
    {
        $urls = $this->gallery_urls ?? [];

        if ($urls === []) {
            $urls = $this->getMedia('gallery')
                ->map(fn (Media $media): string => $media->getAvailableUrl(['gallery-webp']))
                ->values()
                ->all();
        }

        if ($urls === [] && ($fallback = $this->committedImageUrl()) !== null) {
            $urls = [$fallback];
        }

        return array_values($urls);
    }

    /**
     * The values the URL columns must hold (source of truth = the collections).
     *
     * @see docs/media-architecture.md → M-7
     */
    public function resolvedMediaUrls(): array
    {
        return [
            'gallery_urls' => $this->getMedia('gallery')
                ->map(fn (Media $media): string => $media->getAvailableUrl(['gallery-webp']))
                ->values()
                ->all(),
            'thumbnail_url' => $this->getFirstMedia('gallery')?->getAvailableUrl(['thumb-webp']),
            'og_image_url' => $this->getFirstMedia('og')?->getUrl(),
        ];
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
