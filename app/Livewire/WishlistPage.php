<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\CartService;
use App\Services\WishlistService;
use Illuminate\View\View;
use Livewire\Component;
use RuntimeException;

final class WishlistPage extends Component
{
    public ?string $error = null;

    public function remove(int $productId, WishlistService $wishlists): void
    {
        $user = auth()->user();

        if ($user === null) {
            return;
        }

        $wishlists->toggle($user->id, $productId);
        $this->dispatch('wishlist-updated');
    }

    public function moveToCart(int $productId, WishlistService $wishlists, CartService $cart): void
    {
        $user = auth()->user();

        if ($user === null) {
            $this->redirect(route('login'));

            return;
        }

        try {
            $moved = $wishlists->moveToCart($user->id, $productId, $cart);

            if ($moved) {
                $this->dispatch('wishlist-updated');
                $this->dispatch('cart-updated');
                $this->error = null;
            } else {
                $this->error = 'This item is no longer available.';
            }
        } catch (RuntimeException $e) {
            $this->error = $e->getMessage();
        }
    }

    public function render(WishlistService $wishlists): View
    {
        $user = auth()->user();

        return view('livewire.wishlist-page', [
            'products' => $user !== null ? $wishlists->productsFor($user->id) : collect(),
        ]);
    }
}
