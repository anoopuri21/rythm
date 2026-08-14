<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\WishlistService;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

final class WishlistBadge extends Component
{
    public int $count = 0;

    public function mount(WishlistService $wishlists): void
    {
        $user = auth()->user();

        if ($user !== null) {
            $this->count = $wishlists->countFor($user->id);
        }
    }

    #[On('wishlist-updated')]
    public function refresh(WishlistService $wishlists): void
    {
        $user = auth()->user();

        if ($user !== null) {
            $this->count = $wishlists->countFor($user->id);
        }
    }

    public function render(): View
    {
        return view('livewire.wishlist-badge');
    }
}
