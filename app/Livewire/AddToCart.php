<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Product;
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
        $this->product = $product->load(['variants' => fn ($q) => $q
            ->where('is_active', true)
            ->orderBy('id'), 'brand', 'media']);

        if ($product->variants->isNotEmpty()) {
            $this->variantId = $product->variants->first()->id;
        }
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
        $variant = $this->variantId !== null
            ? $this->product->variants->firstWhere('id', $this->variantId)
            : null;

        $stock = $variant !== null ? $variant->stock : $this->product->stock;
        $price = $variant !== null ? (float) $variant->effectivePrice($this->product) : (float) $this->product->price;
        $compareAt = $variant !== null
            ? (float) ($variant->price_override !== null ? $this->product->compare_at_price ?? 0 : 0)
            : (float) ($this->product->compare_at_price ?? 0);

        return view('livewire.add-to-cart', [
            'variant' => $variant,
            'stock' => $stock,
            'price' => $price,
            'compareAt' => $compareAt,
        ]);
    }
}
