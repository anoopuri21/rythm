<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Support\Collection;

/**
 * Auth-only wishlist: toggle, membership check, list and move-to-cart.
 */
final class WishlistService
{
    public function toggle(int $userId, int $productId): bool
    {
        $existing = Wishlist::query()
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($existing !== null) {
            $existing->delete();

            return false;
        }

        Wishlist::create([
            'user_id' => $userId,
            'product_id' => $productId,
        ]);

        return true;
    }

    public function contains(int $userId, int $productId): bool
    {
        return Wishlist::query()
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();
    }

    /**
     * @return Collection<int, Product> wishlisted products, eager-loaded
     */
    public function productsFor(int $userId): Collection
    {
        return Product::query()
            ->active()
            ->withAvailableVariantStock()
            ->whereIn('id', Wishlist::query()->where('user_id', $userId)->pluck('product_id'))
            ->with(['brand', 'media'])
            ->orderByDesc(
                Wishlist::select('created_at')
                    ->whereColumn('wishlists.product_id', 'products.id')
                    ->where('user_id', $userId)
                    ->latest()
                    ->limit(1)
            )
            ->get();
    }

    public function countFor(int $userId): int
    {
        return (int) Wishlist::query()->where('user_id', $userId)->count();
    }

    /**
     * Move a wishlisted product into the cart (stock-guarded).
     *
     * @throws \RuntimeException when stock is unavailable
     */
    public function moveToCart(int $userId, int $productId, CartService $cart): bool
    {
        $wish = Wishlist::query()
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($wish === null) {
            return false;
        }

        $product = $wish->product;

        if ($product === null || ! $product->is_active) {
            $wish->delete();

            return false;
        }

        $firstVariant = $product->variants()->where('is_active', true)->first();
        $cart->addItem($product, $firstVariant, 1);
        $wish->delete();

        return true;
    }
}
