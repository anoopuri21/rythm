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

    /**
     * @return array<string, mixed>
     */
    public function optionsMap(): array
    {
        $options = $this->options;

        return is_array($options) ? $options : [];
    }

    /**
     * Display colour from attribute pivot (preferred) or options JSON.
     */
    public function colorHex(): ?string
    {
        if ($this->relationLoaded('attributeValues')) {
            foreach ($this->attributeValues as $attrValue) {
                if ($attrValue->attribute?->type === 'color' && filled($attrValue->color_hex)) {
                    return (string) $attrValue->color_hex;
                }
            }
        }

        $hex = $this->optionsMap()['color_hex'] ?? null;
        if (is_string($hex) && preg_match('/^#([A-Fa-f0-9]{6})$/', $hex) === 1) {
            return $hex;
        }

        return null;
    }

    public function colorName(): ?string
    {
        if ($this->relationLoaded('attributeValues')) {
            foreach ($this->attributeValues as $attrValue) {
                if ($attrValue->attribute?->type === 'color' && filled($attrValue->value)) {
                    return (string) $attrValue->value;
                }
            }
        }

        $name = $this->optionsMap()['color'] ?? null;

        return is_string($name) && $name !== '' ? $name : null;
    }

    /**
     * Specs for PDP (excludes reserved colour keys).
     *
     * @return array<string, string>
     */
    public function specList(): array
    {
        $specs = [];
        foreach ($this->optionsMap() as $key => $value) {
            if (! is_string($key) || in_array($key, ['color', 'color_hex'], true)) {
                continue;
            }
            if ($value === null || $value === '') {
                continue;
            }
            $specs[$key] = is_scalar($value) ? (string) $value : json_encode($value);
        }

        return $specs;
    }

    /**
     * Short line for cart/checkout (e.g. "Sunburst · Gloss").
     */
    public function optionSummary(): string
    {
        $parts = array_filter([
            $this->colorName(),
            ...array_values($this->specList()),
        ], fn ($part): bool => is_string($part) && $part !== '');

        if ($parts === []) {
            return (string) $this->name;
        }

        return implode(' · ', array_unique($parts));
    }

    /**
     * @return list<string>
     */
    public function galleryUrls(): array
    {
        return $this->getMedia('variant_gallery')
            ->map(fn (Media $media): string => $media->getUrl())
            ->filter()
            ->values()
            ->all();
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
