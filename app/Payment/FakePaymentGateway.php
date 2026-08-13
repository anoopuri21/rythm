<?php

declare(strict_types=1);

namespace App\Payment;

use App\Models\Order;

/**
 * Deterministic fake used in development and tests when Razorpay keys
 * are absent — never used in production (keys are required there).
 */
final class FakePaymentGateway implements PaymentGateway
{
    public function createOrder(Order $order): string
    {
        return 'fake_order_' . $order->order_number;
    }

    public function verify(Order $order, array $payload): PaymentResult
    {
        $accepted = ['authorized', 'captured', 'paid', 'fake_success'];

        if (in_array((string) ($payload['status'] ?? ''), $accepted, true)) {
            return new PaymentResult(true, 'paid', 'fake_pay_' . $order->order_number);
        }

        return new PaymentResult(false, 'failed', message: 'Fake payment declined.');
    }

    public function handleWebhook(array $payload): PaymentResult
    {
        return new PaymentResult(true, 'paid', 'fake_webhook_' . now()->timestamp);
    }
}
