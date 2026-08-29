<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class PaymentRetryService
{
    private const MAX_PAYMENT_ATTEMPTS = 3;

    public function reserve(Order $order, int $userId): Payment
    {
        return DB::transaction(function () use ($order, $userId): Payment {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->user_id !== $userId) {
                throw new RuntimeException('This order does not belong to your account.');
            }

            if ($lockedOrder->isPaid() || $lockedOrder->status !== Order::STATUS_PENDING) {
                throw new RuntimeException('This order is not eligible for another payment attempt.');
            }

            if (! in_array($lockedOrder->payment_status, [Order::PAYMENT_UNPAID, Order::PAYMENT_FAILED], true)) {
                throw new RuntimeException('This order is not eligible for another payment attempt.');
            }

            $initiated = $lockedOrder->payments()
                ->where('status', Payment::STATUS_INITIATED)
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if ($initiated !== null) {
                if (str_starts_with((string) $initiated->gateway_order_id, 'pending_retry_')) {
                    throw new RuntimeException('A payment attempt is already being prepared. Check the order again before retrying.');
                }

                return $initiated;
            }

            if ($lockedOrder->payments()->count() >= self::MAX_PAYMENT_ATTEMPTS) {
                throw new RuntimeException('The payment-attempt limit has been reached. Contact support with your order number.');
            }

            $payment = $lockedOrder->payments()->create([
                'gateway' => 'razorpay',
                'gateway_order_id' => 'pending_retry_'.Str::uuid(),
                'amount' => $lockedOrder->total,
                'currency' => $lockedOrder->currency,
                'status' => Payment::STATUS_INITIATED,
            ]);

            $lockedOrder->update(['payment_status' => Order::PAYMENT_UNPAID]);

            return $payment;
        });
    }

    public function attachGatewayOrder(Payment $payment, string $gatewayOrderId): Payment
    {
        if ($gatewayOrderId === '') {
            throw new RuntimeException('The payment provider returned no order identifier.');
        }

        return DB::transaction(function () use ($payment, $gatewayOrderId): Payment {
            $locked = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if (! str_starts_with((string) $locked->gateway_order_id, 'pending_retry_')) {
                if ($locked->gateway_order_id !== $gatewayOrderId) {
                    throw new RuntimeException('The payment attempt was already assigned another provider order.');
                }

                return $locked;
            }

            $locked->update(['gateway_order_id' => $gatewayOrderId]);

            return $locked;
        });
    }
}
