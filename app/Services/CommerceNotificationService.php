<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CommerceEvent;
use App\Models\NotificationDelivery;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RuntimeException;

final class CommerceNotificationService
{
    private const ALLOWED_METADATA = [
        'order_number',
        'status',
        'payment_status',
        'amount',
        'currency',
        'refund_id',
        'shipment_id',
        'shipment_status',
    ];

    /** @param array<string, mixed> $metadata */
    public function recordEvent(
        string $eventKey,
        string $eventType,
        string $aggregateType,
        ?int $aggregateId,
        array $metadata = [],
    ): CommerceEvent {
        $eventKey = trim($eventKey);
        if ($eventKey === '' || mb_strlen($eventKey) > 150) {
            throw new RuntimeException('A bounded commerce event key is required.');
        }

        $safeMetadata = collect(Arr::only($metadata, self::ALLOWED_METADATA))
            ->map(fn ($value) => is_string($value) ? Str::limit($value, 200, '') : $value)
            ->filter(fn ($value): bool => is_scalar($value) || $value === null)
            ->sortKeys()
            ->all();
        $hash = hash('sha256', json_encode([
            'type' => $eventType,
            'aggregate_type' => $aggregateType,
            'aggregate_id' => $aggregateId,
            'metadata' => $safeMetadata,
        ], JSON_THROW_ON_ERROR));

        try {
            $event = CommerceEvent::query()->firstOrCreate(
                ['event_key' => $eventKey],
                [
                    'event_type' => Str::limit($eventType, 100, ''),
                    'aggregate_type' => Str::limit($aggregateType, 60, ''),
                    'aggregate_id' => $aggregateId,
                    'payload_hash' => $hash,
                    'metadata' => $safeMetadata,
                    'occurred_at' => now(),
                ],
            );
        } catch (UniqueConstraintViolationException) {
            $event = CommerceEvent::query()->where('event_key', $eventKey)->firstOrFail();
        }

        if (! hash_equals($event->payload_hash, $hash)) {
            throw new RuntimeException('Commerce event identity was reused with different data.');
        }

        return $event;
    }

    public function reserveDelivery(
        CommerceEvent $event,
        ?User $user,
        string $channel,
        string $notificationType,
        string $recipient,
    ): NotificationDelivery {
        if (! in_array($channel, ['mail', 'database'], true)) {
            throw new RuntimeException('Unsupported notification channel.');
        }

        $recipientHash = hash('sha256', Str::lower(trim($recipient)));
        $deliveryKey = hash('sha256', implode('|', [
            $event->event_key,
            $user?->id ?? $recipientHash,
            $channel,
            $notificationType,
        ]));

        try {
            return NotificationDelivery::query()->firstOrCreate(
                ['delivery_key' => $deliveryKey],
                [
                    'commerce_event_id' => $event->id,
                    'user_id' => $user?->id,
                    'channel' => $channel,
                    'notification_type' => Str::limit($notificationType, 120, ''),
                    'recipient_hash' => $recipientHash,
                    'status' => NotificationDelivery::STATUS_QUEUED,
                    'queued_at' => now(),
                ],
            );
        } catch (UniqueConstraintViolationException) {
            return NotificationDelivery::query()->where('delivery_key', $deliveryKey)->firstOrFail();
        }
    }
}
