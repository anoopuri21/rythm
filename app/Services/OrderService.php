<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\CheckoutData;
use App\Mail\OrderConfirmationMail;
use App\Mail\OrderStatusMail;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Payment\PaymentResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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
    ) {}

    public function createFromCheckout(Cart $cart, CheckoutData $data, int $userId): Order
    {
        return DB::transaction(function () use ($cart, $data, $userId): Order {
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
            $tax = $this->taxFor($subtotal - $discount);
            $total = max(0.0, round($subtotal - $discount + $shippingFee + $tax, 2));

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $userId,
                'email' => auth()->user()?->email,
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
                    'options' => $item->variant?->options,
                    'unit_price' => $unitPrice,
                    'qty' => $item->qty,
                    'total' => round($unitPrice * $item->qty, 2),
                ]);
            }

            $this->transitionStatus($order, Order::STATUS_PENDING, 'Order placed');

            return $order;
        });
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

            $this->transitionStatus($lockedOrder, Order::STATUS_CONFIRMED, 'Payment captured', from: $fromStatus);

            if ($lockedOrder->email !== null) {
                Mail::to($lockedOrder->email)->queue(new OrderConfirmationMail($lockedOrder));
            }

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
        });
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
    public function changeStatus(Order $order, string $to, ?string $note = null): void
    {
        $allowed = match ($order->status) {
            Order::STATUS_CONFIRMED => [Order::STATUS_PROCESSING, Order::STATUS_SHIPPED, Order::STATUS_CANCELLED],
            Order::STATUS_PROCESSING => [Order::STATUS_SHIPPED, Order::STATUS_CANCELLED],
            Order::STATUS_SHIPPED => [Order::STATUS_DELIVERED, Order::STATUS_CANCELLED],
            default => [],
        };

        if (! in_array($to, $allowed, true)) {
            throw new RuntimeException(
                "Cannot move order from '{$order->status}' to '{$to}'."
            );
        }

        $from = $order->status;

        DB::transaction(function () use ($order, $to, $note, $from): void {
            $order->update(['status' => $to]);
            $this->transitionStatus($order, $to, $note, from: $from);

            // Queue notification for user-facing statuses.
            if (in_array($to, [
                Order::STATUS_SHIPPED,
                Order::STATUS_DELIVERED,
                Order::STATUS_CANCELLED,
            ], true) && $order->email !== null) {
                Mail::to($order->email)->queue(new OrderStatusMail($order, $to));
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

            if ($lockedOrder->email !== null) {
                Mail::to($lockedOrder->email)->queue(new OrderStatusMail($lockedOrder, Order::STATUS_CANCELLED));
            }
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

    private function taxFor(float $discountedSubtotal): float
    {
        $rate = max(0.0, $this->settings->getFloat('tax_rate', 0.0));

        return round(max(0.0, $discountedSubtotal) * ($rate / 100), 2);
    }
}
