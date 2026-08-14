<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\WishlistService;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Heart toggle shown on product cards and the product page.
 * Guests are redirected to login (wishlist is auth-only).
 */
final class WishlistButton extends Component
{
    public int $productId;

    public bool $active = false;

    public ?string $variant = 'card'; // card | page

    public function mount(WishlistService $wishlists): void
    {
        $user = auth()->user();

        if ($user !== null) {
            $this->active = $wishlists->contains($user->id, $this->productId);
        }
    }

    public function toggle(WishlistService $wishlists): void
    {
        $user = auth()->user();

        if ($user === null) {
            $this->redirect(route('login'));

            return;
        }

        $this->active = $wishlists->toggle($user->id, $this->productId);
        $this->dispatch('wishlist-updated');
    }

    public function render(): View
    {
        return view('livewire.wishlist-button');
    }
}
