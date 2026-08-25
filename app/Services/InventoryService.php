<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class InventoryService
{
    /**
     * Decrement the exact stock source and append its immutable ledger entry.
     * The caller owns the surrounding transaction and order row lock.
     */
    public function capture(Order $order, OrderItem $item): void
    {
        if ($item->product_variant_id !== null) {
            $updated = DB::table('product_variants')
                ->where('id', $item->product_variant_id)
                ->where('is_active', true)
                ->where('stock', '>=', $item->qty)
                ->decrement('stock', $item->qty);

            $productId = null;
            $variantId = $item->product_variant_id;
            $balance = $this->variantBalance($variantId);
        } elseif ($item->product_id !== null) {
            $updated = DB::table('products')
                ->where('id', $item->product_id)
                ->where('is_active', true)
                ->where('stock', '>=', $item->qty)
                ->decrement('stock', $item->qty);

            $productId = $item->product_id;
            $variantId = null;
            $balance = $this->productBalance($productId);
        } else {
            return;
        }

        if ($updated !== 1) {
            throw new RuntimeException("Not enough stock for {$item->name}.");
        }

        InventoryMovement::create([
            'product_id' => $productId,
            'product_variant_id' => $variantId,
            'order_id' => $order->id,
            'type' => InventoryMovement::TYPE_ORDER_CAPTURE,
            'quantity_delta' => -$item->qty,
            'balance_after' => $balance,
            'idempotency_key' => "order:{$order->id}:item:{$item->id}:capture",
            'reference_type' => 'order_item',
            'reference_id' => $item->id,
            'reason' => 'Stock captured after payment confirmation',
            'occurred_at' => now(),
        ]);
    }

    /**
     * Restore the source used by a captured item and append its ledger entry.
     * The caller owns the surrounding transaction and order row lock.
     */
    public function restoreForCancellation(Order $order, OrderItem $item): void
    {
        if ($item->product_variant_id !== null) {
            $updated = DB::table('product_variants')
                ->where('id', $item->product_variant_id)
                ->increment('stock', $item->qty);

            $productId = null;
            $variantId = $item->product_variant_id;
            $balance = $this->variantBalance($variantId);
        } elseif ($item->product_id !== null) {
            $updated = DB::table('products')
                ->where('id', $item->product_id)
                ->increment('stock', $item->qty);

            $productId = $item->product_id;
            $variantId = null;
            $balance = $this->productBalance($productId);
        } else {
            return;
        }

        if ($updated !== 1) {
            throw new RuntimeException("Inventory source for {$item->name} no longer exists.");
        }

        InventoryMovement::create([
            'product_id' => $productId,
            'product_variant_id' => $variantId,
            'order_id' => $order->id,
            'type' => InventoryMovement::TYPE_ORDER_CANCELLATION,
            'quantity_delta' => $item->qty,
            'balance_after' => $balance,
            'idempotency_key' => "order:{$order->id}:item:{$item->id}:cancellation",
            'reference_type' => 'order_item',
            'reference_id' => $item->id,
            'reason' => 'Stock restored after paid order cancellation',
            'occurred_at' => now(),
        ]);
    }

    private function productBalance(int $productId): int
    {
        return (int) DB::table('products')->where('id', $productId)->value('stock');
    }

    private function variantBalance(int $variantId): int
    {
        return (int) DB::table('product_variants')->where('id', $variantId)->value('stock');
    }
}
