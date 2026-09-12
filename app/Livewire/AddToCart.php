<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\BackInStockSubscriptionService;
use App\Services\CartService;
use Illuminate\View\View;
use Livewire\Component;
use RuntimeException;

final class AddToCart extends Component
{
    public Product $product;

    public ?int $variantId = null;

    public int $qty = 1;

    public ?string $error = null;

    public bool $added = false;

    public bool $notifyConsent = false;

    public bool $notifySuccess = false;

    public ?string $notifyError = null;

    public function mount(Product $product): void
    {
        $this->product = $this->loadProductGraph($product);

        if ($this->product->variants->isNotEmpty()) {
            $this->variantId = $this->product->variants->first()->id;
        }

        $this->dispatchVariantGallery();
    }

    public function selectVariant(int $variantId): void
    {
        $this->variantId = $variantId;
        $this->qty = 1;
        $this->error = null;
        $this->added = false;
        $this->notifyConsent = false;
        $this->notifySuccess = false;
        $this->notifyError = null;
        $this->dispatchVariantGallery();
    }

    public function setQty(int $qty): void
    {
        $this->qty = max(1, min(99, $qty));
        $this->error = null;
    }

    public function add(): void
    {
        $this->error = null;

        $variant = null;

        if ($this->variantId !== null) {
            $variant = $this->product->variants->firstWhere('id', $this->variantId);

            if ($variant === null) {
                $this->error = 'Please choose a valid option.';

                return;
            }
        }

        try {
            app(CartService::class)->addItem($this->product, $variant, $this->qty);
            $this->added = true;
            $this->dispatch('cart-updated');
        } catch (RuntimeException $e) {
            $this->error = $e->getMessage();
        }
    }

    public function requestStockNotification(): void
    {
        $this->notifyError = null;
        $this->notifySuccess = false;
        $user = auth()->user();

        if (! $user instanceof User) {
            $this->notifyError = 'Please log in to request a stock-availability email.';

            return;
        }

        $variant = $this->variantId !== null
            ? $this->product->variants->firstWhere('id', $this->variantId)
            : null;

        if ($this->variantId !== null && $variant === null) {
            $this->notifyError = 'Please choose a valid option.';

            return;
        }

        try {
            app(BackInStockSubscriptionService::class)->subscribe(
                $user,
                $this->product,
                $variant,
                $this->notifyConsent,
            );
            $this->notifySuccess = true;
            $this->notifyConsent = false;
        } catch (RuntimeException $exception) {
            $this->notifyError = $exception->getMessage();
        }
    }

    public function render(): View
    {
        $this->product = $this->loadProductGraph($this->product);

        // If selected variant is no longer available, auto-select first available
        if ($this->variantId !== null) {
            $selectedVariant = $this->product->variants->firstWhere('id', $this->variantId);
            if ($selectedVariant === null && $this->product->variants->isNotEmpty()) {
                $this->variantId = $this->product->variants->first()->id;
            }
        } elseif ($this->product->variants->isNotEmpty()) {
            $this->variantId = $this->product->variants->first()->id;
        }

        $variant = $this->variantId !== null
            ? $this->product->variants->firstWhere('id', $this->variantId)
            : null;

        $stock = $variant !== null ? $variant->stock : $this->product->stock;
        $price = $variant !== null ? (float) $variant->effectivePrice($this->product) : (float) $this->product->price;
        $compareAt = (float) ($this->product->compare_at_price ?? 0);

        $variantsForUi = $this->product->variants
            ->filter(fn (ProductVariant $v): bool => $v->stock > 0 && $v->is_active)
            ->map(fn (ProductVariant $v): array => [
                'id' => $v->id,
                'name' => $v->name,
                'stock' => $v->stock,
                'is_active' => $v->is_active,
                'price' => (float) $v->effectivePrice($this->product),
                'color_hex' => $v->colorHex(),
                'color_name' => $v->colorName(),
                'summary' => $v->optionSummary(),
                'images' => $v->galleryUrls(),
                'specs' => $v->specList(),
            ])
            ->values();

        return view('livewire.add-to-cart', [
            'variant' => $variant,
            'stock' => $stock,
            'price' => $price,
            'compareAt' => $compareAt,
            'variantsWithColor' => $variantsForUi,
            'variantSpecs' => $variant?->specList() ?? [],
            'galleryImages' => $this->galleryFor($variant),
        ]);
    }

    private function loadProductGraph(Product $product): Product
    {
        return $product->load([
            'variants' => fn ($q) => $q
                ->where('is_active', true)
                ->where('stock', '>', 0)
                ->orderBy('id'),
            'variants.attributeValues.attribute',
            'variants.media',
            'brand',
            'media',
        ]);
    }

    /**
     * @return list<string|null>
     */
    private function galleryFor(?ProductVariant $variant): array
    {
        if ($variant !== null) {
            $urls = $variant->galleryUrls();
            if ($urls !== []) {
                return $urls;
            }
        }

        $productGallery = $this->product->galleryImages();

        return $productGallery !== [] ? $productGallery : [null];
    }

    private function dispatchVariantGallery(): void
    {
        $variant = $this->variantId !== null
            ? $this->product->variants->firstWhere('id', $this->variantId)
            : null;

        $this->dispatch('rythme-variant-updated', images: $this->galleryFor($variant));
    }
}
