<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\CartItem;
use App\Services\AddressService;
use App\Services\CartService;
use App\Services\GstCalculator;
use Illuminate\View\View;
use Livewire\Component;
use RuntimeException;

final class CartPage extends Component
{
    /** @var array<int, array{qty:int}> */
    public array $quantities = [];

    public function mount(CartService $cart): void
    {
        $this->syncQuantities($cart->items());
    }

    public function updateQty(int $itemId, int $qty, CartService $cart): void
    {
        $item = CartItem::find($itemId);

        if ($item === null || $item->cart_id !== $cart->getOrCreateCart()->id) {
            return;
        }

        try {
            $cart->updateQty($item, $qty);
        } catch (RuntimeException $e) {
            session()->flash('cart-error', $e->getMessage());
        }

        $this->syncQuantities($cart->items());
        $this->dispatch('cart-updated');
    }

    public function remove(int $itemId, CartService $cart): void
    {
        $item = CartItem::find($itemId);

        if ($item !== null && $item->cart_id === $cart->getOrCreateCart()->id) {
            $cart->removeItem($item);
        }

        $this->syncQuantities($cart->items());
        $this->dispatch('cart-updated');
    }

    public function clear(CartService $cart): void
    {
        $cart->clear();
        $this->quantities = [];
        $this->dispatch('cart-updated');
    }

    private function syncQuantities($items): void
    {
        $this->quantities = $items
            ->mapWithKeys(fn (CartItem $item): array => [$item->id => ['qty' => $item->qty]])
            ->all();
    }

    public function render(CartService $cart, GstCalculator $gst, AddressService $addresses): View
    {
        $items = $cart->items();
        $totals = $cart->totals();
        $destination = null;
        if (auth()->check()) {
            $destination = $addresses->forUser((int) auth()->id())->firstWhere('is_default', true)?->state
                ?? $addresses->forUser((int) auth()->id())->first()?->state;
        }

        $unitPrices = [];
        foreach ($items as $item) {
            $unitPrices[$item->id] = (float) ($item->variant?->effectivePrice($item->product) ?? $item->product->price);
        }

        $gstQuote = $gst->snapshotsFor($items, $unitPrices, 0.0, $destination)['quote'];
        $settings = app(\App\Services\SiteSettingsService::class);
        $shippingFee = $this->shippingFeeFor((float) $totals['subtotal'], $settings);

        return view('livewire.cart-page', [
            'items' => $items,
            'totals' => $totals,
            'gstQuote' => $gstQuote,
            'shippingFee' => $shippingFee,
            'grandTotal' => round((float) $totals['subtotal'] + $shippingFee + $gstQuote->total, 2),
        ]);
    }

    private function shippingFeeFor(float $subtotal, \App\Services\SiteSettingsService $settings): float
    {
        $flat = $settings->getFloat('shipping_flat_fee', 0.0);
        $freeAbove = $settings->getFloat('shipping_free_above', 0.0);

        if ($freeAbove > 0 && $subtotal >= $freeAbove) {
            return 0.0;
        }

        return $flat;
    }
}
