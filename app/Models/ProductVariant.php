<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[Table('product_variants')]
#[Fillable(['product_id', 'name', 'options', 'sku', 'price_override', 'stock', 'is_active'])]
class ProductVariant extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $casts = [
        'options' => 'array',
        'price_override' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductAttributeValue::class,
            'product_attribute_value_product_variant',
        );
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    /**
     * Effective price: variant override or parent product price.
     * The parent product is passed explicitly to avoid lazy loading.
     */
    public function effectivePrice(Product $product): string
    {
        return $this->price_override ?? (string) $product->price;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('variant_gallery')
            ->multiple()
            ->image()
            ->maxFiles(6)
            ->acceptAllMimeTypes();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('variant-thumb-webp')
            ->width(240)
            ->height(240)
            ->format('webp')
            ->quality(80)
            ->queued();
    }

    /**
     * Get the first image for this variant.
     */
    public function thumbnailImage(): ?string
    {
        $media = $this->getFirstMedia('variant_gallery');

        if ($media !== null) {
            return $media->hasGeneratedConversion('variant-thumb-webp')
                ? $media->getUrl('variant-thumb-webp')
                : $media->getUrl();
        }

        return null;
    }
}
