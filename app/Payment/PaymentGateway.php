<?php

declare(strict_types=1);

namespace App\Payment;

use App\Models\Order;

/**
 * Gateway contract — Razorpay (production/test) or Fake (dev/tests).
 */
interface PaymentGateway
{
    /**
     * Create a gateway order and return its reference id.
     */
    public function createOrder(Order $order): string;

    /**
     * Verify a payment callback (signature check) and return a result.
     *
     * @param  array<string, mixed>  $payload
     */
    public function verify(Order $order, array $payload): PaymentResult;

    /**
     * Handle an async webhook payload.
     *
     * @param  array<string, mixed>  $payload
     */
    public function handleWebhook(array $payload): PaymentResult;
}
