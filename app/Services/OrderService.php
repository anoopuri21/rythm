<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\CheckoutData;
use App\Events\CommerceNotificationRequested;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\User;
use App\Payment\PaymentResult;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Order lifecycle: creation from a validated cart (server-side totals,
 * immutable snapshots), payment recording, status transitions with an
 * audit history, and stock reservation on successful payment.
 */
final class OrderService
{
    public function __construct(
        private readonly CouponService $coupons,
        private readonly SiteSettingsService $settings,
        private readonly RefundService $refunds,
        private readonly InventoryService $inventory,
        private readonly OrderStateMachine $states,
    ) {}

    public function createFromCheckout(Cart $cart, CheckoutData $data, int $userId): Order
    {
        try {
            return DB::transaction(function () use ($cart, $data, $userId): Order {
                if ($data->idempotencyKey !== null && $data->idempotencyKey !== '') {
                    $existing = Order::query()
                        ->where('idempotency_key', $data->idempotencyKey)
                        ->lockForUpdate()
                        ->first();

                    if ($existing !== null) {
                        if ($existing->user_id !== $userId) {
                            throw new RuntimeException('Checkout attempt does not belong to this account.');
                        }

                        return $existing;
                    }
                }

                $items = $cart->items()
                    ->with(['product', 'variant'])
                    ->lockForUpdate()
                    ->get();

                if ($items->isEmpty()) {
                    throw new RuntimeException('Your cart is empty.');
                }

                // Prices, coupon, shipping and tax are all derived on the server
                // inside this transaction. Livewire properties are display state,
                // never a trusted source of money values.
                $subtotal = 0.0;
                $unitPrices = [];

                foreach ($items as $item) {
                    if ($item->product === null || ! $item->product->is_active) {
                        throw new RuntimeException('A product in your cart is no longer available.');
                    }

                    if ($item->variant !== null && ! $item->variant->is_active) {
                        throw new RuntimeException("{$item->product->name} option is no longer available.");
                    }

                    $availableStock = $item->variant?->stock ?? $item->product->stock;
                    if ($availableStock < $item->qty) {
                        throw new RuntimeException("Not enough stock for {$item->product->name}.");
                    }

                    $unitPrice = (float) ($item->variant?->effectivePrice($item->product) ?? $item->product->price);
                    $unitPrices[$item->id] = $unitPrice;
                    $subtotal += $unitPrice * $item->qty;
                }

                $subtotal = round($subtotal, 2);
                $discount = 0.0;
                $couponCode = $data->couponCode !== null ? strtoupper(trim($data->couponCode)) : null;

                if ($couponCode !== null && $couponCode !== '') {
                    $discount = $this->coupons->validateAndApply($couponCode, $subtotal, lockForUpdate: true)['discount'];
                }

                $shippingFee = $this->shippingFeeFor($subtotal);
                $taxSnapshots = $this->taxSnapshotsFor(
                    $items,
                    $unitPrices,
                    $discount,
                    $data->shippingAddress,
                );
                $tax = round((float) collect($taxSnapshots)->sum('tax_amount_snapshot'), 2);
                $total = max(0.0, round($subtotal - $discount + $shippingFee + $tax, 2));

                $order = Order::create([
                    'order_number' => $this->generateOrderNumber(),
                    'idempotency_key' => $data->idempotencyKey,
                    'user_id' => $userId,
                    'email' => User::query()->find($userId)?->email,
                    'status' => Order::STATUS_PENDING,
                    'payment_status' => Order::PAYMENT_UNPAID,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'coupon_code' => $couponCode,
                    'shipping_fee' => $shippingFee,
                    'tax' => $tax,
                    'total' => $total,
                    'currency' => $data->currency,
                    'shipping_address' => $data->shippingAddress,
                    'billing_address' => $data->billingAddress,
                    'notes' => trim(($data->notes ?? '').($couponCode !== null && $couponCode !== '' ? " [Coupon: {$couponCode}]" : '')),
                    'placed_at' => now(),
                ]);

                foreach ($items as $item) {
                    $unitPrice = $unitPrices[$item->id];

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'name' => $item->product->name,
                        'sku' => $item->variant?->sku ?? $item->product->sku,
                        ...$taxSnapshots[$item->id],
                        'options' => $item->variant?->options,
                        'unit_price' => $unitPrice,
                        'qty' => $item->qty,
                        'total' => round($unitPrice * $item->qty, 2),
                    ]);
                }

                $this->recordInitialStatus($order);

                return $order;
            });
        } catch (QueryException $exception) {
            // Two requests can race before either absent idempotency row can
            // be locked. The unique index is the final arbiter; after the
            // losing transaction rolls back, return the winner's order.
            if ($data->idempotencyKey !== null && $data->idempotencyKey !== '') {
                $existing = Order::query()
                    ->where('idempotency_key', $data->idempotencyKey)
                    ->first();

                if ($existing !== null) {
                    if ($existing->user_id !== $userId) {
                        throw new RuntimeException('Checkout attempt does not belong to this account.');
                    }

                    return $existing;
                }
            }

            throw $exception;
        }
    }

    public function recordPaymentInitiation(Order $order, string $gatewayOrderId): Payment
    {
        return DB::transaction(function () use ($order, $gatewayOrderId): Payment {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $existing = Payment::query()
                ->where('gateway', 'razorpay')
                ->where('gateway_order_id', $gatewayOrderId)
                ->first();

            if ($existing !== null) {
                if ($existing->order_id !== $lockedOrder->id) {
                    throw new RuntimeException('Gateway order already belongs to another order.');
                }

                return $existing;
            }

            if ($lockedOrder->coupon_code !== null && $lockedOrder->coupon_usage_recorded_at === null) {
                $coupon = $this->coupons->validateAndApply(
                    $lockedOrder->coupon_code,
                    (float) $lockedOrder->subtotal,
                    lockForUpdate: true,
                )['coupon'];
                $coupon->increment('used_count');
                $lockedOrder->update(['coupon_usage_recorded_at' => now()]);
            }

            return Payment::create([
                'gateway' => 'razorpay',
                'gateway_order_id' => $gatewayOrderId,
                'order_id' => $lockedOrder->id,
                'amount' => $lockedOrder->total,
                'currency' => $lockedOrder->currency,
                'status' => Payment::STATUS_INITIATED,
            ]);
        });
    }

    /**
     * Record a provider-confirmed authorization without granting paid state,
     * confirming the order, or moving inventory.
     */
    public function markPaymentAuthorized(
        Order $order,
        PaymentResult $result,
        string $gatewayOrderId,
    ): bool {
        return DB::transaction(function () use ($order, $result, $gatewayOrderId): bool {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->isPaid() || $lockedOrder->isCancelled()) {
                return false;
            }

            $payment = Payment::query()
                ->where('order_id', $lockedOrder->id)
                ->where('gateway', 'razorpay')
                ->where('gateway_order_id', $gatewayOrderId)
                ->lockForUpdate()
                ->first();

            if ($payment === null) {
                throw new RuntimeException('Payment initiation record not found.');
            }

            if ($payment->status === Payment::STATUS_AUTHORIZED
                && $payment->gateway_payment_id === $result->gatewayPaymentId) {
                return false;
            }

            if ($payment->status === Payment::STATUS_PAID) {
                return false;
            }

            $payment->update([
                'gateway_payment_id' => $result->gatewayPaymentId,
                'amount' => $lockedOrder->total,
                'currency' => $lockedOrder->currency,
                'status' => Payment::STATUS_AUTHORIZED,
            ]);
            $lockedOrder->update(['payment_status' => Order::PAYMENT_AUTHORIZED]);

            return true;
        });
    }

    /**
     * Finalize a captured payment exactly once.
     *
     * @return bool true only when this call performed the first transition
     */
    public function markPaid(Order $order, PaymentResult $result, ?string $gatewayOrderId = null): bool
    {
        return DB::transaction(function () use ($order, $result, $gatewayOrderId): bool {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->isPaid()) {
                return false;
            }

            if ($lockedOrder->isCancelled()) {
                throw new RuntimeException('A cancelled order cannot be marked as paid.');
            }

            $paymentQuery = Payment::query()
                ->where('order_id', $lockedOrder->id)
                ->where('gateway', 'razorpay');

            if ($gatewayOrderId !== null && $gatewayOrderId !== '') {
                $paymentQuery->where('gateway_order_id', $gatewayOrderId);
            } else {
                $paymentQuery->where('status', Payment::STATUS_INITIATED)->latest();
            }

            $payment = $paymentQuery->lockForUpdate()->first();

            if ($payment === null) {
                throw new RuntimeException('Payment initiation record not found.');
            }

            $payment->update([
                'gateway_payment_id' => $result->gatewayPaymentId,
                'amount' => $lockedOrder->total,
                'currency' => $lockedOrder->currency,
                'status' => Payment::STATUS_PAID,
            ]);

            // Inventory is changed and ledgered only inside this first paid transition.
            foreach ($lockedOrder->items()->with(['product', 'variant'])->get() as $item) {
                $this->inventory->capture($lockedOrder, $item);
            }

            $fromStatus = $lockedOrder->status;

            $lockedOrder->update([
                'payment_status' => Order::PAYMENT_PAID,
                'status' => Order::STATUS_CONFIRMED,
            ]);

            // The pending entry created with the order is the same audit
            // record that becomes confirmed when payment is captured. Updating
            // it avoids recording a duplicate status row for one checkout.
            $initialStatus = OrderStatusHistory::query()
                ->where('order_id', $lockedOrder->id)
                ->whereNull('from')
                ->where('to', Order::STATUS_PENDING)
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if ($initialStatus !== null) {
                $initialStatus->update([
                    'from' => $fromStatus,
                    'to' => Order::STATUS_CONFIRMED,
                    'note' => 'Payment captured',
                    'actor' => auth()->check() ? 'customer' : 'system',
                ]);
            } else {
                $this->transitionStatus($lockedOrder, Order::STATUS_CONFIRMED, 'Payment captured', from: $fromStatus);
            }

            CommerceNotificationRequested::dispatch(
                "order:{$lockedOrder->id}:confirmed",
                'order.confirmed',
                $lockedOrder->id,
            );

            return true;
        });
    }

    public function markFailed(Order $order, PaymentResult $result): void
    {
        DB::transaction(function () use ($order, $result): void {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->isPaid()) {
                return;
            }

            $payment = $lockedOrder->payments()
                ->where('status', Payment::STATUS_INITIATED)
                ->latest()
                ->lockForUpdate()
                ->first();

            if ($payment !== null) {
                $payment->update([
                    'gateway_payment_id' => $result->gatewayPaymentId,
                    'status' => Payment::STATUS_FAILED,
                    'payload' => ['message' => $result->message],
                ]);
            }

            $lockedOrder->update(['payment_status' => Order::PAYMENT_FAILED]);

            if ($payment !== null) {
                CommerceNotificationRequested::dispatch(
                    "payment:{$payment->id}:failed",
                    'payment.failed',
                    $lockedOrder->id,
                );
            }
        });
    }

    private function recordInitialStatus(Order $order): void
    {
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'from' => null,
            'to' => Order::STATUS_PENDING,
            'note' => 'Order placed',
            'actor' => auth()->check() ? 'customer' : 'system',
        ]);
    }

    public function transitionStatus(Order $order, string $to, ?string $note = null, ?string $actor = null, ?string $from = null): void
    {
        // Explicit $from wins — call sites must capture the previous
        // status BEFORE mutating the model (otherwise history is lost).
        $from = $from ?? $order->status;

        if ($from === $to) {
            return;
        }

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'from' => $from,
            'to' => $to,
            'note' => $note,
            'actor' => $actor ?? (auth()->check() ? 'customer' : 'system'),
        ]);
    }

    public function generateOrderNumber(): string
    {
        return 'RYM-'.now()->format('Y').'-'.strtoupper(
            substr((string) bin2hex(random_bytes(3)), 0, 6)
        );
    }

    /**
     * Move an order to a new status (admin or customer), writing audit
     * history and queueing a status email for user-facing transitions.
     *
     * @throws RuntimeException on invalid transitions
     */
    public function changeStatus(Order $order, string $to, ?string $note = null, bool $notify = true): void
    {
        $this->states->assertTransition($order->status, $to);

        $from = $order->status;

        DB::transaction(function () use ($order, $to, $note, $from, $notify): void {
            $order->update(['status' => $to]);
            $this->transitionStatus($order, $to, $note, from: $from);

            if ($notify && in_array($to, [
                Order::STATUS_PROCESSING,
                Order::STATUS_SHIPPED,
                Order::STATUS_DELIVERED,
                Order::STATUS_CANCELLED,
            ], true)) {
                CommerceNotificationRequested::dispatch(
                    "order:{$order->id}:status:{$to}",
                    "order.{$to}",
                    $order->id,
                );
            }
        });
    }

    /**
     * Cancel an order as the customer (only pending/confirmed).
     * Restores stock if the payment was captured, queues a mail.
     *
     * @throws RuntimeException when cancellation is not allowed
     */
    public function cancelByUser(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if (! in_array($lockedOrder->status, [Order::STATUS_PENDING, Order::STATUS_CONFIRMED], true)) {
                throw new RuntimeException('This order can no longer be cancelled.');
            }
            if ($lockedOrder->payment_status === Order::PAYMENT_AUTHORIZED) {
                throw new RuntimeException('Payment authorization is settling. Please wait before cancelling.');
            }

            $wasPaid = $lockedOrder->isPaid();
            $from = $lockedOrder->status;

            $lockedOrder->update(['status' => Order::STATUS_CANCELLED]);

            if ($wasPaid) {
                $this->refunds->requestForCancellation($lockedOrder);

                foreach ($lockedOrder->items as $item) {
                    $this->inventory->restoreForCancellation($lockedOrder, $item);
                }
            } else {
                $this->releaseCouponUsage($lockedOrder);
            }

            $this->transitionStatus($lockedOrder, Order::STATUS_CANCELLED, 'Cancelled by customer', from: $from);

            CommerceNotificationRequested::dispatch(
                "order:{$lockedOrder->id}:status:cancelled",
                'order.cancelled',
                $lockedOrder->id,
            );
        });
    }

    private function releaseCouponUsage(Order $order): void
    {
        if ($order->coupon_code === null
            || $order->coupon_usage_recorded_at === null
            || $order->coupon_usage_released_at !== null) {
            return;
        }

        Coupon::query()
            ->where('code', $order->coupon_code)
            ->where('used_count', '>', 0)
            ->lockForUpdate()
            ->decrement('used_count');

        $order->update(['coupon_usage_released_at' => now()]);
    }

    private function shippingFeeFor(float $subtotal): float
    {
        $flat = max(0.0, $this->settings->getFloat('shipping_flat_fee', 0.0));
        $freeAbove = max(0.0, $this->settings->getFloat('shipping_free_above', 0.0));

        return $freeAbove > 0 && $subtotal >= $freeAbove ? 0.0 : $flat;
    }

    /**
     * Build immutable line-level tax evidence without assuming a jurisdictional rule.
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\CartItem>  $items
     * @param  array<int, float>  $unitPrices
     * @param  array<string, mixed>  $shippingAddress
     * @return array<int, array<string, mixed>>
     */
    private function taxSnapshotsFor($items, array $unitPrices, float $discount, array $shippingAddress): array
    {
        $enabled = $this->settings->get('tax_rules_enabled', '0') === '1';
        $globalRate = $this->settings->getFloat('tax_rate', 0.0);
        if ($enabled && ($globalRate < 0 || $globalRate > 100)) {
            throw new RuntimeException('Configured default tax rate must be between 0 and 100.');
        }
        $subtotalCents = (int) round($items->sum(
            fn ($item): float => $unitPrices[$item->id] * $item->qty,
        ) * 100);
        $discountCentsTotal = min($subtotalCents, max(0, (int) round($discount * 100)));
        $remainingDiscountCents = $discountCentsTotal;
        $snapshots = [];
        $lastIndex = $items->count() - 1;

        foreach ($items->values() as $index => $item) {
            $grossCents = (int) round($unitPrices[$item->id] * $item->qty * 100);
            $discountCents = $index === $lastIndex
                ? $remainingDiscountCents
                : min($remainingDiscountCents, (int) round(
                    $subtotalCents > 0 ? $discountCentsTotal * ($grossCents / $subtotalCents) : 0,
                ));
            $remainingDiscountCents -= $discountCents;
            $taxableCents = max(0, $grossCents - $discountCents);
            $configuredRate = $item->product->tax_rate === null
                ? $globalRate
                : (float) $item->product->tax_rate;
            if ($enabled && ($configuredRate < 0 || $configuredRate > 100)) {
                throw new RuntimeException('Configured product tax rate must be between 0 and 100.');
            }
            $appliedRate = $enabled ? $configuredRate : null;
            $taxCents = $appliedRate === null ? 0 : (int) round($taxableCents * ($appliedRate / 100));

            $snapshots[$item->id] = [
                'hsn_code_snapshot' => $item->product->hsn_code,
                'tax_classification_snapshot' => $item->product->tax_classification,
                'tax_rate_snapshot' => $appliedRate,
                'taxable_amount_snapshot' => $enabled ? $taxableCents / 100 : null,
                'tax_amount_snapshot' => $taxCents / 100,
                'tax_calculation_enabled_snapshot' => $enabled,
                'tax_destination_region_snapshot' => $enabled
                    ? (isset($shippingAddress['state']) ? trim((string) $shippingAddress['state']) : null)
                    : null,
            ];
        }

        return $snapshots;
    }
}
