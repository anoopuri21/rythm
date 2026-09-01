<?php

declare(strict_types=1);

namespace App\Services;

use App\Listeners\HandleBackInStockNotification;
use App\Listeners\HandleCommerceNotification;
use App\Models\NotificationDelivery;
use App\Notifications\BackInStockNotification;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class NotificationRetryService
{
    public const MAX_ATTEMPTS = 3;

    public function retry(NotificationDelivery $delivery): NotificationDelivery
    {
        $reserved = DB::transaction(function () use ($delivery): NotificationDelivery {
            $locked = NotificationDelivery::query()->whereKey($delivery->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== NotificationDelivery::STATUS_FAILED) {
                throw new RuntimeException('Only a delivery with a known failed outcome can be retried.');
            }

            if ($locked->attempts >= self::MAX_ATTEMPTS) {
                throw new RuntimeException('The notification retry limit has been reached.');
            }

            if ($locked->user_id === null) {
                throw new RuntimeException('Anonymous delivery retry requires manual recipient reconciliation.');
            }

            $locked->update([
                'status' => NotificationDelivery::STATUS_QUEUED,
                'last_error' => null,
                'queued_at' => now(),
                'failed_at' => null,
            ]);

            return $locked->fresh(['event', 'user']);
        });

        try {
            if ($reserved->notification_type === BackInStockNotification::class) {
                app(HandleBackInStockNotification::class)->retryDelivery($reserved);
            } else {
                app(HandleCommerceNotification::class)->retryDelivery($reserved);
            }
        } catch (\Throwable $exception) {
            $reserved->update([
                'status' => NotificationDelivery::STATUS_FAILED,
                'last_error' => 'Retry dispatch failed: '.class_basename($exception),
                'failed_at' => now(),
            ]);

            throw new RuntimeException('Notification retry dispatch failed with a known local error.', previous: $exception);
        }

        return $reserved->fresh();
    }
}
