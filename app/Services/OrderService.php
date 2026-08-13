<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\CheckoutData;
use App\Mail\OrderConfirmationMail;
use App\Models\Cart;
use App\Models\CartItem;
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
    public function createFromCheckout(Cart $cart, CheckoutData $data, int $userId): Order
    {
        $items = $cart->items()->with(['product', 'variant'])->get();

        if ($items->isEmpty()) {
            throw new RuntimeException('Your cart is empty.');
        }

        return DB::transaction(function () use ($cart, $items, $data, $userId): Order {
            // Recompute totals server-side — never trust client values.
            $subtotal = 0.0;

            foreach ($items as $item) {
                if ($item->product === null || ! $item->product->is_active) {
                    throw new RuntimeException('A product in your cart is no longer available.');
                }

                $subtotal += (float) $item->unit_price * $item->qty;
            }

            $discount = max(0.0, (float) $data->discount);
            $shippingFee = max(0.0, (float) $data->shippingFee);
            $tax = max(0.0, (float) $data->tax);
            $total = max(0.0, round($subtotal - $discount + $shippingFee + $tax, 2));

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $userId,
                'email' => auth()->user()?->email,
                'status' => Order::STATUS_PENDING,
                'payment_status' => Order::PAYMENT_UNPAID,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping_fee' => $shippingFee,
                'tax' => $tax,
                'total' => $total,
                'currency' => $data->currency,
                'shipping_address' => $data->shippingAddress,
                'billing_address' => $data->billingAddress,
                'notes' => $data->notes,
                'placed_at' => now(),
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'name' => $item->product->name,
                    'sku' => $item->variant?->sku ?? $item->product->sku,
                    'options' => $item->variant?->options,
                    'unit_price' => $item->unit_price,
                    'qty' => $item->qty,
                    'total' => round((float) $item->unit_price * $item->qty, 2),
                ]);
            }

            $this->transitionStatus($order, Order::STATUS_PENDING, 'Order placed');

            return $order;
        });
    }

    public function recordPaymentInitiation(Order $order, string $gatewayOrderId): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'gateway' => 'razorpay',
            'gateway_order_id' => $gatewayOrderId,
            'amount' => $order->total,
            'currency' => $order->currency,
            'status' => Payment::STATUS_INITIATED,
        ]);
    }

    public function markPaid(Order $order, PaymentResult $result, ?string $gatewayOrderId = null): void
    {
        DB::transaction(function () use ($order, $result, $gatewayOrderId): void {
            $payment = $order->payments()
                ->where('status', Payment::STATUS_INITIATED)
                ->latest()
                ->first()
                ?? new Payment(['order_id' => $order->id, 'gateway' => 'razorpay']);

            $payment->fill([
                'gateway_payment_id' => $result->gatewayPaymentId,
                'gateway_order_id' => $gatewayOrderId ?? $payment->gateway_order_id,
                'amount' => $order->total,
                'currency' => $order->currency,
                'status' => Payment::STATUS_PAID,
            ])->save();

            // Reserve stock with an optimistic lock — never oversell.
            foreach ($order->items as $item) {
                if ($item->product === null) {
                    continue;
                }

                $updated = $item->product_id !== null
                    ? DB::table('products')->where('id', $item->product_id)->where('stock', '>=', $item->qty)->decrement('stock', $item->qty)
                    : false;

                if (! $updated && $item->product_id !== null) {
                    $current = DB::table('products')->where('id', $item->product_id)->value('stock');

                    if ((int) $current < $item->qty) {
                        throw new RuntimeException("Not enough stock for {$item->name}.");
                    }
                }
            }

            $fromStatus = $order->status;

            $order->update([
                'payment_status' => Order::PAYMENT_PAID,
                'status' => Order::STATUS_CONFIRMED,
            ]);

            $this->transitionStatus($order, Order::STATUS_CONFIRMED, 'Payment captured', from: $fromStatus);

            // Non-blocking: confirmation email goes through the queue
            // (routed to the 'emails' queue in AppServiceProvider).
            if ($order->email !== null) {
                Mail::to($order->email)->queue(new OrderConfirmationMail($order));
            }
        });
    }

    public function markFailed(Order $order, PaymentResult $result): void
    {
        DB::transaction(function () use ($order, $result): void {
            $order->payments()->create([
                'gateway' => 'razorpay',
                'gateway_payment_id' => $result->gatewayPaymentId,
                'amount' => $order->total,
                'currency' => $order->currency,
                'status' => Payment::STATUS_FAILED,
                'payload' => ['message' => $result->message],
            ]);

            $order->update(['payment_status' => Order::PAYMENT_FAILED]);
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
        return 'RYM-' . now()->format('Y') . '-' . strtoupper(
            substr((string) bin2hex(random_bytes(3)), 0, 6)
        );
    }
}
