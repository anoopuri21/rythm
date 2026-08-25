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
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Table('products')]
#[Fillable(['category_id', 'brand_id', 'name', 'slug', 'sku', 'short_description', 'description', 'price', 'compare_at_price', 'stock', 'low_stock_threshold', 'is_active', 'is_featured', 'meta_title', 'meta_description'])]
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
        if ($media = $this->getFirstMediaUrl('gallery')) {
            return $media;
        }

        $file = 'images/products/'.$this->slug.'.jpg';

        return is_file(public_path($file)) ? '/'.$file : null;
    }

    /** Gallery image URLs (media first, committed fallback, else []). */
    public function galleryImages(): array
    {
        $urls = $this->getMedia('gallery')
            ->map(fn ($m) => $m->getUrl())
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
