<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use RuntimeException;

final class CartDrawer extends Component
{
    public bool $open = false;

    /** @var array<int, array{qty:int}> */
    public array $quantities = [];

    public function mount(CartService $cart): void
    {
        $this->syncQuantities($cart->items());
    }

    #[On('cart-updated')]
    #[On('cart-drawer-toggle')]
    public function refresh(CartService $cart, ?bool $toggle = null): void
    {
        if ($toggle === true) {
            $this->open = ! $this->open;
        }

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

    private function syncQuantities($items): void
    {
        $this->quantities = $items
            ->mapWithKeys(fn (CartItem $item): array => [$item->id => ['qty' => $item->qty]])
            ->all();
    }

    public function render(CartService $cart): View
    {
        $items = $cart->items();
        $totals = $cart->totals();

        return view('livewire.cart-drawer', [
            'items' => $items,
            'totals' => $totals,
        ]);
    }
}
