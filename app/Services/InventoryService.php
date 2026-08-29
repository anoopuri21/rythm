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
     * Atomically decrement the exact stock source and append its immutable
     * ledger entry. Safe both standalone and inside the order transaction.
     */
    public function capture(Order $order, OrderItem $item): void
    {
        DB::transaction(function () use ($order, $item): void {
            Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $key = "order:{$order->id}:item:{$item->id}:capture";

            if ($this->movementExists($key)) {
                return;
            }

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
                throw new RuntimeException("Inventory source for {$item->name} is missing.");
            }

            if ($updated !== 1) {
                throw new RuntimeException("Not enough stock for {$item->name}.");
            }

            $this->record($order, $item, $key, $productId, $variantId, -$item->qty, $balance,
                InventoryMovement::TYPE_ORDER_CAPTURE, 'Stock captured after payment confirmation');
        });
    }

    /**
     * Atomically restore the source used by a captured item and append its
     * ledger entry. Duplicate cancellation calls become no-ops.
     */
    public function restoreForCancellation(Order $order, OrderItem $item): void
    {
        DB::transaction(function () use ($order, $item): void {
            Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $key = "order:{$order->id}:item:{$item->id}:cancellation";

            if ($this->movementExists($key)) {
                return;
            }

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
                throw new RuntimeException("Inventory source for {$item->name} is missing.");
            }

            if ($updated !== 1) {
                throw new RuntimeException("Inventory source for {$item->name} no longer exists.");
            }

            $this->record($order, $item, $key, $productId, $variantId, $item->qty, $balance,
                InventoryMovement::TYPE_ORDER_CANCELLATION, 'Stock restored after paid order cancellation');
        });
    }

    private function movementExists(string $key): bool
    {
        return InventoryMovement::query()
            ->where('idempotency_key', $key)
            ->lockForUpdate()
            ->exists();
    }

    private function record(
        Order $order,
        OrderItem $item,
        string $key,
        ?int $productId,
        ?int $variantId,
        int $delta,
        int $balance,
        string $type,
        string $reason,
    ): void {
        InventoryMovement::create([
            'product_id' => $productId,
            'product_variant_id' => $variantId,
            'order_id' => $order->id,
            'type' => $type,
            'quantity_delta' => $delta,
            'balance_after' => $balance,
            'idempotency_key' => $key,
            'reference_type' => 'order_item',
            'reference_id' => $item->id,
            'reason' => $reason,
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
