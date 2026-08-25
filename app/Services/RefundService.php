<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use RuntimeException;

final class RefundService
{
    /**
     * Persist a durable refund request without claiming that gateway money moved.
     * The caller must already hold the order row lock inside a transaction.
     */
    public function requestForCancellation(Order $order): Refund
    {
        $payment = Payment::query()
            ->where('order_id', $order->id)
            ->where('status', Payment::STATUS_PAID)
            ->latest('id')
            ->first();

        if ($payment === null) {
            throw new RuntimeException('A captured payment is required before a refund can be requested.');
        }

        $refund = Refund::query()->firstOrCreate(
            ['order_id' => $order->id],
            [
                'payment_id' => $payment->id,
                'amount' => $order->total,
                'currency' => $order->currency,
                'status' => Refund::STATUS_PENDING,
                'reason' => 'Customer cancelled order',
            ],
        );

        $order->update(['payment_status' => Order::PAYMENT_REFUND_PENDING]);

        return $refund;
    }
}
