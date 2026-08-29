<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\ReturnReason;
use App\Models\ReturnRequest;
use App\Models\ReturnRequestItem;
use App\Models\User;
use App\Support\AdminAccess;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class ReturnRequestService
{
    /** @var array<string, list<string>> */
    private const TRANSITIONS = [
        ReturnRequest::STATUS_REQUESTED => [ReturnRequest::STATUS_UNDER_REVIEW, ReturnRequest::STATUS_REJECTED],
        ReturnRequest::STATUS_UNDER_REVIEW => [ReturnRequest::STATUS_APPROVED, ReturnRequest::STATUS_REJECTED],
        ReturnRequest::STATUS_APPROVED => [ReturnRequest::STATUS_RECEIVED],
        ReturnRequest::STATUS_RECEIVED => [ReturnRequest::STATUS_CLOSED],
        ReturnRequest::STATUS_REJECTED => [],
        ReturnRequest::STATUS_CLOSED => [],
        ReturnRequest::STATUS_CANCELLED => [],
    ];

    public function __construct(
        private readonly SiteSettingsService $settings,
        private readonly RefundService $refunds,
    ) {}

    /** @param array<int, int> $quantities order_item_id => quantity */
    public function create(
        Order $order,
        ReturnReason $reason,
        array $quantities,
        string $idempotencyKey,
        User $customer,
        ?string $note = null,
    ): ReturnRequest {
        $idempotencyKey = trim($idempotencyKey);
        $note = $note === null ? null : trim($note);

        if ($order->user_id !== $customer->id) {
            throw new RuntimeException('Only the order owner can request a return.');
        }
        if ($idempotencyKey === '' || mb_strlen($idempotencyKey) > 100) {
            throw new RuntimeException('A return request identity of at most 100 characters is required.');
        }
        if ($note !== null && mb_strlen($note) > 2000) {
            throw new RuntimeException('Return request note is too long.');
        }
        if ($quantities === []) {
            throw new RuntimeException('At least one order item is required.');
        }
        foreach ($quantities as $itemId => $quantity) {
            if (! is_int($itemId) || ! is_int($quantity) || $quantity < 1) {
                throw new RuntimeException('Return items and quantities must be positive integers.');
            }
        }

        return DB::transaction(function () use ($order, $reason, $quantities, $idempotencyKey, $customer, $note): ReturnRequest {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $existing = ReturnRequest::query()->where('idempotency_key', $idempotencyKey)->with('items')->first();

            if ($existing !== null) {
                $existingQuantities = $existing->items
                    ->mapWithKeys(fn (ReturnRequestItem $item): array => [$item->order_item_id => $item->quantity])
                    ->all();
                $requestedQuantities = $quantities;
                ksort($existingQuantities);
                ksort($requestedQuantities);

                if ($existing->order_id !== $lockedOrder->id
                    || $existing->user_id !== $customer->id
                    || $existing->return_reason_id !== $reason->id
                    || $existing->customer_note !== ($note === '' ? null : $note)
                    || $existingQuantities !== $requestedQuantities) {
                    throw new RuntimeException('Return request identity was replayed with different data.');
                }

                return $existing;
            }

            $this->assertEligible($lockedOrder, $reason);
            $orderItems = OrderItem::query()
                ->where('order_id', $lockedOrder->id)
                ->whereIn('id', array_keys($quantities))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($orderItems->count() !== count($quantities)) {
                throw new RuntimeException('Every return item must belong to the order.');
            }

            foreach ($quantities as $itemId => $quantity) {
                $previouslyRequested = (int) ReturnRequestItem::query()
                    ->where('order_item_id', $itemId)
                    ->whereHas('returnRequest', fn ($query) => $query->whereNotIn('status', [
                        ReturnRequest::STATUS_REJECTED,
                        ReturnRequest::STATUS_CANCELLED,
                    ]))
                    ->sum('quantity');

                if ($previouslyRequested + $quantity > $orderItems->get($itemId)->qty) {
                    throw new RuntimeException('Return quantity cannot exceed the remaining eligible order quantity.');
                }
            }

            $request = ReturnRequest::query()->create([
                'order_id' => $lockedOrder->id,
                'user_id' => $customer->id,
                'return_reason_id' => $reason->id,
                'request_number' => 'RMA-'.strtoupper(substr(hash('sha256', $idempotencyKey), 0, 20)),
                'idempotency_key' => $idempotencyKey,
                'status' => ReturnRequest::STATUS_REQUESTED,
                'reason_snapshot' => $reason->name,
                'customer_note' => $note === '' ? null : $note,
            ]);

            foreach ($quantities as $itemId => $quantity) {
                $request->items()->create(['order_item_id' => $itemId, 'quantity' => $quantity]);
            }
            $request->events()->create([
                'from_status' => null,
                'to_status' => ReturnRequest::STATUS_REQUESTED,
                'reason' => 'Customer submitted return request',
                'actor_id' => $customer->id,
            ]);

            return $request->load(['items.orderItem', 'events']);
        });
    }

    public function transition(ReturnRequest $request, string $toStatus, string $reason, User $actor): ReturnRequest
    {
        $this->authorizeTransition($toStatus, $actor);
        $reason = trim($reason);
        if (mb_strlen($reason) < 5 || mb_strlen($reason) > 500) {
            throw new RuntimeException('A return transition reason between 5 and 500 characters is required.');
        }

        return DB::transaction(function () use ($request, $toStatus, $reason, $actor): ReturnRequest {
            $locked = ReturnRequest::query()->whereKey($request->id)->lockForUpdate()->firstOrFail();
            if ($locked->status === $toStatus) {
                return $locked;
            }
            if (! in_array($toStatus, self::TRANSITIONS[$locked->status] ?? [], true)) {
                throw new RuntimeException("Invalid return transition from {$locked->status} to {$toStatus}.");
            }

            $from = $locked->status;
            $updates = ['status' => $toStatus];
            if ($toStatus === ReturnRequest::STATUS_APPROVED) {
                $updates['approved_at'] = now();
            } elseif ($toStatus === ReturnRequest::STATUS_RECEIVED) {
                $updates['received_at'] = now();
            } elseif ($toStatus === ReturnRequest::STATUS_CLOSED) {
                $updates['closed_at'] = now();
            }
            $locked->update($updates);
            $locked->events()->create([
                'from_status' => $from,
                'to_status' => $toStatus,
                'reason' => $reason,
                'actor_id' => $actor->id,
            ]);

            return $locked->fresh(['items.orderItem', 'events.actor']);
        });
    }

    public function requestPendingRefund(
        ReturnRequest $request,
        float $amount,
        string $reason,
        User $financeUser,
    ): Refund {
        if (! $financeUser->hasAdminPermission(AdminAccess::FINANCE_MANAGE)) {
            throw new RuntimeException('Finance permission is required to create a return-linked refund.');
        }
        return DB::transaction(function () use ($request, $amount, $reason, $financeUser): Refund {
            $locked = ReturnRequest::query()->whereKey($request->id)->lockForUpdate()->firstOrFail();
            if ($locked->refund_id !== null) {
                return Refund::query()->findOrFail($locked->refund_id);
            }
            if (! in_array($locked->status, [ReturnRequest::STATUS_APPROVED, ReturnRequest::STATUS_RECEIVED], true)) {
                throw new RuntimeException('A refund can be linked only after logistical return approval.');
            }

            $payment = Payment::query()
                ->where('order_id', $locked->order_id)
                ->whereIn('status', [Payment::STATUS_PAID, Payment::STATUS_REFUNDED])
                ->latest('id')
                ->first();
            if ($payment === null) {
                throw new RuntimeException('A captured payment is required before a return-linked refund can be requested.');
            }

            $refund = $this->refunds->request(
                $payment,
                $amount,
                $reason,
                $financeUser,
                'return:'.$locked->id.':refund',
            );
            $locked->update(['refund_id' => $refund->id]);

            return $refund;
        });
    }

    public function cancelByCustomer(ReturnRequest $request, User $customer): ReturnRequest
    {
        if ($request->user_id !== $customer->id) {
            throw new RuntimeException('Only the return request owner can cancel it.');
        }
        if ($request->status !== ReturnRequest::STATUS_REQUESTED) {
            throw new RuntimeException('Only a newly requested return can be cancelled by the customer.');
        }

        return DB::transaction(function () use ($request, $customer): ReturnRequest {
            $locked = ReturnRequest::query()->whereKey($request->id)->lockForUpdate()->firstOrFail();
            if ($locked->status === ReturnRequest::STATUS_CANCELLED) {
                return $locked;
            }
            if ($locked->status !== ReturnRequest::STATUS_REQUESTED) {
                throw new RuntimeException('Only a newly requested return can be cancelled by the customer.');
            }
            $locked->update(['status' => ReturnRequest::STATUS_CANCELLED]);
            $locked->events()->create([
                'from_status' => ReturnRequest::STATUS_REQUESTED,
                'to_status' => ReturnRequest::STATUS_CANCELLED,
                'reason' => 'Customer cancelled return request',
                'actor_id' => $customer->id,
            ]);

            return $locked->fresh(['items.orderItem', 'events']);
        });
    }

    private function assertEligible(Order $order, ReturnReason $reason): void
    {
        $windowDays = (int) $this->settings->get('return_window_days', '0');
        if ($this->settings->get('returns_enabled', '0') !== '1' || $windowDays < 1) {
            throw new RuntimeException('Customer returns are not currently enabled.');
        }
        if (! $reason->is_active) {
            throw new RuntimeException('The selected return reason is not available.');
        }
        if ($order->status !== Order::STATUS_DELIVERED) {
            throw new RuntimeException('A return can be requested only after delivery is recorded.');
        }

        $deliveredAt = $order->shipments()->whereNotNull('delivered_at')->max('delivered_at')
            ?? $order->statusHistory()->where('to', Order::STATUS_DELIVERED)->max('created_at');
        if ($deliveredAt === null || now()->greaterThan(\Illuminate\Support\Carbon::parse($deliveredAt)->addDays($windowDays))) {
            throw new RuntimeException('This order is outside the configured return eligibility window.');
        }
    }

    private function authorizeTransition(string $toStatus, User $actor): void
    {
        $mayTriage = $actor->hasAdminPermission(AdminAccess::INTERACTIONS_MANAGE)
            || $actor->hasAdminPermission(AdminAccess::ORDERS_MANAGE);
        if ($toStatus === ReturnRequest::STATUS_UNDER_REVIEW && $mayTriage) {
            return;
        }
        if ($actor->hasAdminPermission(AdminAccess::ORDERS_MANAGE)) {
            return;
        }

        throw new RuntimeException('Order-management permission is required for this return transition.');
    }
}
