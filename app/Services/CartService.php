<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Cart operations for the current guest/user session. Phase C ships the
 * add-item path (product page); Phase D expands: update, remove, merge,
 * totals, drawer + badge + cart page.
 */
final class CartService
{
    private const SESSION_KEY = 'rythme.cart.session';

    public function getOrCreateCart(): Cart
    {
        $user = auth()->user();

        if ($user !== null) {
            $cart = Cart::firstOrCreate(['user_id' => $user->id]);

            return $cart;
        }

        $sessionId = session()->get(self::SESSION_KEY);

        if ($sessionId === null || Cart::where('session_id', $sessionId)->doesntExist()) {
            $sessionId = Str::uuid()->toString();
            session()->put(self::SESSION_KEY, $sessionId);
        }

        return Cart::firstOrCreate(['session_id' => $sessionId]);
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

        $existing = CartItem::query()
            ->where('cart_id', $this->getOrCreateCart()->id)
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
            'cart_id' => $this->getOrCreateCart()->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'qty' => $newQty,
            'unit_price' => $unitPrice,
        ]);
    }

    public function count(): int
    {
        return (int) $this->getOrCreateCart()->items()->sum('qty');
    }

    /** @return array{subtotal: float, count: int} */
    public function totals(): array
    {
        $items = $this->getOrCreateCart()->items()->with('product')->get();

        return [
            'subtotal' => (float) $items->sum(fn (CartItem $item): float => (float) $item->unit_price * $item->qty),
            'count' => (int) $items->sum('qty'),
        ];
    }
}
