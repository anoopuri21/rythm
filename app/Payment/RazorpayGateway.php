<?php

declare(strict_types=1);

namespace App\Payment;

use App\Models\Order;
use Razorpay\Api\Api;
use RuntimeException;

/**
 * Razorpay integration (test mode by default — RYTHME_RAZORPAY_KEY_ID /
 * RYTHME_RAZORPAY_KEY_SECRET envs). Signature verified on every callback;
 * webhook verified via HMAC SHA-256 over the raw body.
 */
final class RazorpayGateway implements PaymentGateway
{
    public function __construct(
        private readonly string $keyId,
        private readonly string $keySecret,
        private readonly ?string $webhookSecret = null,
    ) {}

    private function api(): Api
    {
        return new Api($this->keyId, $this->keySecret);
    }

    public function createOrder(Order $order): string
    {
        $gatewayOrder = $this->api()->order->create([
            'receipt' => $order->order_number,
            'amount' => (int) round($order->total * 100),
            'currency' => $order->currency,
            'notes' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ],
        ]);

        return (string) $gatewayOrder['id'];
    }

    public function verify(Order $order, array $payload): PaymentResult
    {
        $paymentId = (string) ($payload['razorpay_payment_id'] ?? '');
        $orderId = (string) ($payload['razorpay_order_id'] ?? '');
        $signature = (string) ($payload['razorpay_signature'] ?? '');

        if ($paymentId === '' || $orderId === '' || $signature === '') {
            return new PaymentResult(false, 'failed', message: 'Missing Razorpay callback fields.');
        }

        $expected = hash_hmac('sha256', "{$orderId}|{$paymentId}", $this->keySecret);

        if (! hash_equals($expected, $signature)) {
            return new PaymentResult(false, 'failed', message: 'Invalid payment signature.');
        }

        $payment = $this->api()->payment->fetch($paymentId);

        if (($payment['status'] ?? '') !== 'captured') {
            return new PaymentResult(false, 'failed', $paymentId, 'Payment not captured.');
        }

        $amountPaid = (int) ($payment['amount'] ?? 0);
        $expectedAmount = (int) round($order->total * 100);

        if ($amountPaid !== $expectedAmount) {
            return new PaymentResult(false, 'failed', $paymentId, 'Payment amount mismatch.');
        }

        return new PaymentResult(true, 'paid', $paymentId);
    }

    public function handleWebhook(array $payload): PaymentResult
    {
        // Structural guard: webhook payload must describe an event + entity.
        if (($payload['event'] ?? '') === '' || ($payload['payload'] ?? null) === null) {
            return new PaymentResult(false, 'failed', message: 'Malformed webhook payload.');
        }

        $paymentEntity = $payload['payload']['payment']['entity'] ?? $payload['payload']['order']['entity'] ?? null;

        if ($paymentEntity === null) {
            return new PaymentResult(false, 'failed', message: 'Unknown webhook entity.');
        }

        $isCaptured = ($paymentEntity['status'] ?? '') === 'captured'
            || ($paymentEntity['status'] ?? '') === 'paid';

        return $isCaptured
            ? new PaymentResult(true, 'paid', (string) ($paymentEntity['id'] ?? ''))
            : new PaymentResult(false, 'failed', message: 'Payment not captured.');
    }

    /**
     * HMAC verification for raw webhook bodies (Razorpay signature header).
     */
    public function verifyWebhookSignature(string $rawBody, string $signature): bool
    {
        if ($this->webhookSecret === null || $this->webhookSecret === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $rawBody, $this->webhookSecret);

        return hash_equals($expected, $signature);
    }

    public static function isConfigured(): bool
    {
        return config('rythme.razorpay.key_id') !== null
            && config('rythme.razorpay.key_secret') !== null;
    }

    public static function fromConfig(): self
    {
        if (! self::isConfigured()) {
            throw new RuntimeException('Razorpay is not configured. Set RYTHME_RAZORPAY_KEY_ID and RYTHME_RAZORPAY_KEY_SECRET.');
        }

        return new self(
            (string) config('rythme.razorpay.key_id'),
            (string) config('rythme.razorpay.key_secret'),
            config('rythme.razorpay.webhook_secret'),
        );
    }
}
