<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\CartService;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

final class CartBadge extends Component
{
    public int $count = 0;

    public function mount(CartService $cart): void
    {
        $this->count = $cart->count();
    }

    #[On('cart-updated')]
    public function refresh(CartService $cart): void
    {
        $this->count = $cart->count();
    }

    public function render(): View
    {
        return view('livewire.cart-badge');
    }
}
