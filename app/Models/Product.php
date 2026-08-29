<?php

declare(strict_types=1);

namespace App\Models;

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
#[Fillable(['category_id', 'brand_id', 'name', 'slug', 'sku', 'short_description', 'description', 'price', 'compare_at_price', 'stock', 'low_stock_threshold', 'is_active', 'is_featured', 'featured_rank', 'is_trending', 'meta_title', 'meta_description'])]
class Product extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $casts = [
        'price' => 'decimal:2',
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

    public function importSource(): HasOne
    {
        return $this->hasOne(ProductImportSource::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ProductQuestion::class);
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
        $this->addMediaCollection('gallery');
        $this->addMediaCollection('og')
            ->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // Conversions run through the bounded, stop-when-empty scheduled
        // worker; no persistent shared-hosting daemon is required.
        $this->addMediaConversion('thumb-webp')
            ->width(480)
            ->height(480)
            ->format('webp')
            ->quality(82)
            ->queued();

        $this->addMediaConversion('gallery-webp')
            ->width(1200)
            ->height(1200)
            ->format('webp')
            ->quality(84)
            ->queued();
    }

    /**
     * Best available product image URL.
     *
     * 1. Spatie media (admin-uploaded / attached), if any.
     * 2. Committed public asset: public/images/products/{slug}.jpg
     *    (reset-proof — travels with the git repo, needs no storage disk).
     * 3. null — caller decides the final placeholder.
     */
    public function heroImage(): ?string
    {
        $media = $this->getFirstMedia('gallery');
        if ($media !== null) {
            return $media->hasGeneratedConversion('gallery-webp')
                ? $media->getUrl('gallery-webp')
                : $media->getUrl();
        }

        $file = 'images/products/'.$this->slug.'.jpg';

        return is_file(public_path($file)) ? '/'.$file : null;
    }

    public function thumbnailImage(): ?string
    {
        $media = $this->getFirstMedia('gallery');

        if ($media !== null) {
            return $media->hasGeneratedConversion('thumb-webp')
                ? $media->getUrl('thumb-webp')
                : $media->getUrl();
        }

        return $this->heroImage();
    }

    /** Gallery image URLs (media first, committed fallback, else []). */
    public function galleryImages(): array
    {
        $urls = $this->getMedia('gallery')
            ->map(fn (Media $media): string => $media->hasGeneratedConversion('gallery-webp')
                ? $media->getUrl('gallery-webp')
                : $media->getUrl())
            ->values()
            ->all();

        if ($urls === [] && ($fallback = $this->heroImage()) !== null) {
            $urls = [$fallback];
        }

        return $urls;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
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
