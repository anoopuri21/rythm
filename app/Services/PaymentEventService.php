<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Payment\PaymentResult;
use Illuminate\Support\Str;

final class PaymentEventService
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array{event: PaymentEvent, is_new: bool, payload_matches: bool}
     */
    public function receive(string $rawBody, array $payload, ?Payment $payment, ?string $providerEventId): array
    {
        $hash = hash('sha256', $rawBody);
        $eventId = trim((string) $providerEventId);
        $eventId = $eventId !== '' ? Str::limit($eventId, 150, '') : 'sha256:'.$hash;
        $entity = $this->entity($payload);

        $event = PaymentEvent::query()->firstOrCreate(
            ['gateway' => 'razorpay', 'gateway_event_id' => $eventId],
            [
                'order_id' => $payment?->order_id,
                'payment_id' => $payment?->id,
                'event_type' => Str::limit((string) ($payload['event'] ?? 'unknown'), 100, ''),
                'status' => PaymentEvent::STATUS_RECEIVED,
                'payload_hash' => $hash,
                'redacted_metadata' => array_filter([
                    'entity_id' => isset($entity['id']) ? Str::limit((string) $entity['id'], 150, '') : null,
                    'gateway_order_id' => isset($entity['order_id']) ? Str::limit((string) $entity['order_id'], 150, '') : null,
                    'amount' => isset($entity['amount']) ? (int) $entity['amount'] : null,
                    'currency' => isset($entity['currency']) ? Str::upper(Str::limit((string) $entity['currency'], 3, '')) : null,
                    'status' => isset($entity['status']) ? Str::limit((string) $entity['status'], 40, '') : null,
                ], static fn ($value): bool => $value !== null && $value !== ''),
                'received_at' => now(),
            ],
        );

        return [
            'event' => $event,
            'is_new' => $event->wasRecentlyCreated,
            'payload_matches' => hash_equals($event->payload_hash, $hash),
        ];
    }

    /** @param array<string, mixed> $payload */
    public function verifyCapturedPayment(Payment $payment, array $payload): PaymentResult
    {
        $entity = $this->entity($payload);

        if ($entity === []) {
            return new PaymentResult(false, 'failed', message: 'Webhook payment entity is missing.');
        }

        $gatewayOrderId = (string) ($entity['order_id'] ?? '');
        if ($gatewayOrderId === '' || ! hash_equals((string) $payment->gateway_order_id, $gatewayOrderId)) {
            return new PaymentResult(false, 'failed', message: 'Gateway order mismatch.');
        }

        $expectedAmount = (int) round((float) $payment->amount * 100);
        if ((int) ($entity['amount'] ?? -1) !== $expectedAmount) {
            return new PaymentResult(false, 'failed', message: 'Payment amount mismatch.');
        }

        $currency = Str::upper((string) ($entity['currency'] ?? ''));
        if ($currency === '' || $currency !== Str::upper((string) $payment->currency)) {
            return new PaymentResult(false, 'failed', message: 'Payment currency mismatch.');
        }

        if (($entity['status'] ?? '') !== 'captured') {
            return new PaymentResult(false, 'failed', message: 'Payment not captured.');
        }

        $paymentId = (string) ($entity['id'] ?? '');
        if ($paymentId === '') {
            return new PaymentResult(false, 'failed', message: 'Gateway payment identifier is missing.');
        }

        return new PaymentResult(true, 'paid', $paymentId);
    }

    public function processed(PaymentEvent $event): void
    {
        $event->update([
            'status' => PaymentEvent::STATUS_PROCESSED,
            'processed_at' => now(),
            'failure_message' => null,
        ]);
    }

    public function failed(PaymentEvent $event, string $message): void
    {
        $event->update([
            'status' => PaymentEvent::STATUS_FAILED,
            'processed_at' => now(),
            'failure_message' => Str::limit($message, 500, ''),
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function entity(array $payload): array
    {
        $entity = $payload['payload']['payment']['entity'] ?? null;

        return is_array($entity) ? $entity : [];
    }
}
