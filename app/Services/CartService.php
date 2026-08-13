<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Cart operations for guest (session) and authenticated users.
 * Business rules: price snapshot at add, stock guard at every mutation,
 * guest cart merges into user cart on login (see Login listener).
 */
final class CartService
{
    private const SESSION_KEY = 'rythme.cart.session';

    public function getOrCreateCart(): Cart
    {
        $user = auth()->user();

        if ($user !== null) {
            return Cart::firstOrCreate(['user_id' => $user->id]);
        }

        $sessionId = session()->get(self::SESSION_KEY);

        if ($sessionId === null || Cart::where('session_id', $sessionId)->doesntExist()) {
            $sessionId = Str::uuid()->toString();
            session()->put(self::SESSION_KEY, $sessionId);
        }

        return Cart::firstOrCreate(['session_id' => $sessionId]);
    }

    /**
     * @return Collection<int, CartItem> items with product/brand/media loaded
     */
    public function items(): Collection
    {
        return $this->getOrCreateCart()
            ->items()
            ->with(['product.brand', 'product.media', 'variant'])
            ->get();
    }

    public function count(): int
    {
        return (int) $this->getOrCreateCart()->items()->sum('qty');
    }

    /** @return array{subtotal: float, count: int} */
    public function totals(): array
    {
        $items = $this->items();

        return [
            'subtotal' => (float) $items->sum(fn (CartItem $item): float => (float) $item->unit_price * $item->qty),
            'count' => (int) $items->sum('qty'),
        ];
    }

    /**
     * @throws RuntimeException when the product/variant is unavailable or stock is insufficient
     */
    public function addItem(Product $product, ?ProductVariant $variant, int $qty = 1): CartItem
    {
        $qty = max(1, $qty);

        if (! $product->is_active) {
            throw new RuntimeException('This product is no longer available.');
        }

        $stock = $variant !== null ? $variant->stock : $product->stock;
        $unitPrice = $variant !== null
            ? $variant->effectivePrice()
            : (string) $product->price;

        $cart = $this->getOrCreateCart();
        $existing = CartItem::query()
            ->where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant?->id)
            ->first();

        $newQty = ($existing?->qty ?? 0) + $qty;

        if ($newQty > $stock) {
            throw new RuntimeException(
                $stock > 0
                    ? "Only {$stock} in stock."
                    : 'This item is currently out of stock.'
            );
        }

        if ($existing !== null) {
            $existing->update(['qty' => $newQty]);

            return $existing->fresh();
        }

        return CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'qty' => $newQty,
            'unit_price' => $unitPrice,
        ]);
    }

    /**
     * @throws RuntimeException when stock is insufficient
     */
    public function updateQty(CartItem $item, int $qty): CartItem
    {
        $qty = max(1, min(99, $qty));

        $stock = $item->variant_id !== null
            ? $item->variant->stock
            : $item->product->stock;

        if ($qty > $stock) {
            throw new RuntimeException(
                $stock > 0
                    ? "Only {$stock} in stock."
                    : 'This item is currently out of stock.'
            );
        }

        $item->update(['qty' => $qty]);

        return $item->fresh();
    }

    public function removeItem(CartItem $item): void
    {
        $item->delete();
    }

    public function clear(): void
    {
        $this->getOrCreateCart()->items()->delete();
    }

    /**
     * Merge a guest session cart into the authenticated user's cart
     * (qty sums, stock-capped). Called on login via the Auth listener.
     */
    public function mergeGuestCart(?string $sessionId): void
    {
        $user = auth()->user();

        if ($user === null || $sessionId === null) {
            return;
        }

        $guestCart = Cart::where('session_id', $sessionId)->first();

        if ($guestCart === null) {
            return;
        }

        $userCart = Cart::firstOrCreate(['user_id' => $user->id]);

        foreach ($guestCart->items as $guestItem) {
            $product = $guestItem->product;
            $variant = $guestItem->variant;

            if ($product === null || ! $product->is_active) {
                continue;
            }

            $stock = $variant !== null ? $variant->stock : $product->stock;
            $existing = CartItem::query()
                ->where('cart_id', $userCart->id)
                ->where('product_id', $guestItem->product_id)
                ->where('product_variant_id', $guestItem->product_variant_id)
                ->first();

            $mergedQty = min(($existing?->qty ?? 0) + $guestItem->qty, max(0, $stock));

            if ($mergedQty <= 0) {
                continue;
            }

            if ($existing !== null) {
                $existing->update(['qty' => $mergedQty]);
            } else {
                CartItem::create([
                    'cart_id' => $userCart->id,
                    'product_id' => $guestItem->product_id,
                    'product_variant_id' => $guestItem->product_variant_id,
                    'qty' => $mergedQty,
                    'unit_price' => $guestItem->unit_price,
                ]);
            }
        }

        $guestCart->items()->delete();
        $guestCart->delete();
        session()->forget(self::SESSION_KEY);
    }
}
