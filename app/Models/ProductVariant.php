<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\SyncsResolvedMediaUrls;
use App\Models\Contracts\HasResolvedMediaUrls;
use App\Support\MediaDisk;
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
class ProductVariant extends Model implements HasMedia, HasResolvedMediaUrls
{
    use HasFactory;
    use InteractsWithMedia;
    use SyncsResolvedMediaUrls;

    protected $casts = [
        'options' => 'array',
        'gallery_urls' => 'array',
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
     * PDP gallery URLs — the stored column first (WebP conversion once the
     * queue generated it, else the original), Media Library for unsynced rows.
     *
     * @return list<string>
     */
    public function galleryUrls(): array
    {
        $urls = $this->gallery_urls ?? [];

        if ($urls === []) {
            $urls = $this->getMedia('variant_gallery')
                ->map(fn (Media $media): string => $media->getAvailableUrl(['variant-gallery-webp']))
                ->filter()
                ->values()
                ->all();
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
            'gallery_urls' => $this->getMedia('variant_gallery')
                ->map(fn (Media $media): string => $media->getAvailableUrl(['variant-gallery-webp']))
                ->values()
                ->all(),
            'thumbnail_url' => $this->getFirstMedia('variant_gallery')?->getAvailableUrl(['variant-thumb-webp']),
        ];
    }

    public function registerMediaCollections(): void
    {
        // Spatie MediaCollection only supports its own API (useDisk, singleFile,
        // acceptsMimeTypes, onlyKeepLatest, …). `multiple()/image()/maxFiles()`
        // belong to Filament's FileUpload component and crash here — keep this
        // registration minimal; the "images only, max 6" rules are enforced on
        // the admin form (ProductResource → Variant images).
        //
        // Disk: variant images are product images, so they follow the same
        // Cloudinary rollout as the product gallery (docs/cloudinary-media.md).
        $this->addMediaCollection('variant_gallery')
            ->useDisk(MediaDisk::forCollection('variant_gallery'));
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // Cloudinary delivers both sizes itself (App\Models\Media maps this
        // model's conversion names to delivery transformations), so nothing is
        // queued and the original is never pulled back onto this server.
        if (MediaDisk::isCloudinary($media?->disk)) {
            return;
        }

        // Two sizes, same convention as Product: a small WebP for thumbnails
        // and a 1200px WebP for the PDP gallery (the storefront swaps the
        // gallery to these images when a variant is selected, so serving the
        // untouched original — up to 5 MB — would break the page budget).
        $this->addMediaConversion('variant-thumb-webp')
            ->width(240)
            ->height(240)
            ->format('webp')
            ->quality(80)
            ->queued();

        $this->addMediaConversion('variant-gallery-webp')
            ->width(1200)
            ->height(1200)
            ->format('webp')
            ->quality(84)
            ->queued();
    }

    /**
     * First image for this variant (stored column first, media fallback).
     */
    public function thumbnailImage(): ?string
    {
        return $this->thumbnail_url
            ?? $this->getFirstMedia('variant_gallery')?->getAvailableUrl(['variant-thumb-webp']);
    }
}
