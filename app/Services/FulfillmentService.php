<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Models\ShipmentItem;
use App\Models\User;
use App\Support\AdminAccess;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class FulfillmentService
{
    /** @var array<string, list<string>> */
    private const TRANSITIONS = [
        Shipment::STATUS_DRAFT => [Shipment::STATUS_READY, Shipment::STATUS_CANCELLED],
        Shipment::STATUS_READY => [Shipment::STATUS_DISPATCHED, Shipment::STATUS_CANCELLED],
        Shipment::STATUS_DISPATCHED => [Shipment::STATUS_DELIVERED],
        Shipment::STATUS_DELIVERED => [],
        Shipment::STATUS_CANCELLED => [],
    ];

    /**
     * @param  array<int, int>  $quantities  order_item_id => quantity
     * @param  array{carrier?:?string,awb?:?string,tracking_url?:?string,note?:?string}  $details
     */
    public function create(
        Order $order,
        array $quantities,
        string $idempotencyKey,
        User $actor,
        array $details = [],
    ): Shipment {
        $this->authorize($actor);
        $idempotencyKey = trim($idempotencyKey);

        if ($idempotencyKey === '' || mb_strlen($idempotencyKey) > 100) {
            throw new RuntimeException('A fulfillment identity of at most 100 characters is required.');
        }

        if ($quantities === []) {
            throw new RuntimeException('At least one order item is required.');
        }

        foreach ($quantities as $itemId => $quantity) {
            if (! is_int($itemId) || ! is_int($quantity) || $quantity < 1) {
                throw new RuntimeException('Shipment items and quantities must be positive integers.');
            }
        }

        $details = $this->validateDetails($details);

        return DB::transaction(function () use ($order, $quantities, $idempotencyKey, $actor, $details): Shipment {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $existing = Shipment::query()
                ->where('idempotency_key', $idempotencyKey)
                ->with('items')
                ->first();

            if ($existing !== null) {
                if ($existing->order_id !== $lockedOrder->id) {
                    throw new RuntimeException('Fulfillment identity belongs to another order.');
                }

                $existingQuantities = $existing->items
                    ->mapWithKeys(fn (ShipmentItem $item): array => [$item->order_item_id => $item->quantity])
                    ->all();
                ksort($existingQuantities);
                $requestedQuantities = $quantities;
                ksort($requestedQuantities);

                if ($existingQuantities !== $requestedQuantities) {
                    throw new RuntimeException('Fulfillment identity was replayed with different items.');
                }

                return $existing;
            }

            if (! $lockedOrder->isPaid() || in_array($lockedOrder->status, [Order::STATUS_CANCELLED, Order::STATUS_REFUNDED], true)) {
                throw new RuntimeException('Only a paid, active order can be fulfilled.');
            }

            $orderItems = OrderItem::query()
                ->where('order_id', $lockedOrder->id)
                ->whereIn('id', array_keys($quantities))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($orderItems->count() !== count($quantities)) {
                throw new RuntimeException('Every shipment item must belong to the order.');
            }

            $shipment = Shipment::query()->create(array_merge($details, [
                'order_id' => $lockedOrder->id,
                'idempotency_key' => $idempotencyKey,
                'status' => Shipment::STATUS_DRAFT,
                'created_by' => $actor->id,
            ]));

            foreach ($quantities as $itemId => $quantity) {
                $item = $orderItems->get($itemId);
                $allocated = (int) ShipmentItem::query()
                    ->where('order_item_id', $itemId)
                    ->whereHas('shipment', fn ($query) => $query->where('status', '!=', Shipment::STATUS_CANCELLED))
                    ->sum('quantity');

                if ($allocated + $quantity > $item->qty) {
                    throw new RuntimeException('Shipment quantity cannot exceed the unallocated order quantity.');
                }

                $shipment->items()->create([
                    'order_item_id' => $itemId,
                    'quantity' => $quantity,
                ]);
            }

            ShipmentEvent::query()->create([
                'shipment_id' => $shipment->id,
                'from_status' => null,
                'to_status' => Shipment::STATUS_DRAFT,
                'reason' => 'Manual fulfillment record created',
                'actor_id' => $actor->id,
            ]);

            return $shipment->load('items', 'events');
        });
    }

    /**
     * @param  array{carrier?:?string,awb?:?string,tracking_url?:?string}  $details
     */
    public function transition(
        Shipment $shipment,
        string $toStatus,
        string $reason,
        User $actor,
        array $details = [],
    ): Shipment {
        $this->authorize($actor);
        $reason = trim($reason);

        if (mb_strlen($reason) < 5 || mb_strlen($reason) > 500) {
            throw new RuntimeException('A transition reason between 5 and 500 characters is required.');
        }

        $details = $this->validateDetails($details);

        return DB::transaction(function () use ($shipment, $toStatus, $reason, $actor, $details): Shipment {
            $locked = Shipment::query()->whereKey($shipment->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === $toStatus) {
                return $locked;
            }

            if (! in_array($toStatus, self::TRANSITIONS[$locked->status] ?? [], true)) {
                throw new RuntimeException("Invalid fulfillment transition from {$locked->status} to {$toStatus}.");
            }

            if ($toStatus === Shipment::STATUS_DISPATCHED && empty($details['carrier']) && empty($locked->carrier)) {
                throw new RuntimeException('A carrier reference is required before dispatch.');
            }

            $from = $locked->status;
            $updates = array_merge($details, ['status' => $toStatus]);

            if ($toStatus === Shipment::STATUS_DISPATCHED) {
                $updates['dispatched_at'] = now();
            }

            if ($toStatus === Shipment::STATUS_DELIVERED) {
                $updates['delivered_at'] = now();
            }

            $locked->update($updates);
            $locked->events()->create([
                'from_status' => $from,
                'to_status' => $toStatus,
                'reason' => $reason,
                'actor_id' => $actor->id,
            ]);
            $this->synchronizeOrder($locked->order);

            return $locked->fresh(['items', 'events']);
        });
    }

    private function synchronizeOrder(Order $order): void
    {
        $active = $order->shipments()
            ->where('status', '!=', Shipment::STATUS_CANCELLED)
            ->get();

        if ($active->isEmpty()) {
            return;
        }

        $orderedQuantity = (int) $order->items()->sum('qty');
        $allocatedQuantity = (int) ShipmentItem::query()
            ->whereHas('shipment', fn ($query) => $query
                ->where('order_id', $order->id)
                ->where('status', '!=', Shipment::STATUS_CANCELLED))
            ->sum('quantity');

        if ($allocatedQuantity === $orderedQuantity
            && $active->every(fn (Shipment $record): bool => $record->status === Shipment::STATUS_DELIVERED)) {
            $order->update(['status' => Order::STATUS_DELIVERED]);

            return;
        }

        if ($active->contains(fn (Shipment $record): bool => in_array(
            $record->status,
            [Shipment::STATUS_DISPATCHED, Shipment::STATUS_DELIVERED],
            true,
        ))) {
            $order->update(['status' => Order::STATUS_SHIPPED]);
        }
    }

    /**
     * @param  array<string, mixed>  $details
     * @return array<string, ?string>
     */
    private function validateDetails(array $details): array
    {
        $limits = ['carrier' => 100, 'awb' => 120, 'tracking_url' => 500, 'note' => 2000];
        $clean = [];

        foreach ($limits as $key => $limit) {
            if (! array_key_exists($key, $details)) {
                continue;
            }

            $value = $details[$key] === null ? null : trim((string) $details[$key]);

            if ($value !== null && mb_strlen($value) > $limit) {
                throw new RuntimeException("{$key} is too long.");
            }

            if ($key === 'tracking_url' && $value !== null && $value !== ''
                && filter_var($value, FILTER_VALIDATE_URL) === false) {
                throw new RuntimeException('Tracking URL must be a valid URL.');
            }

            $clean[$key] = $value === '' ? null : $value;
        }

        return $clean;
    }

    private function authorize(User $actor): void
    {
        if (! $actor->hasAdminPermission(AdminAccess::ORDERS_MANAGE)) {
            throw new RuntimeException('Order-management permission is required to manage fulfillment.');
        }
    }
}
