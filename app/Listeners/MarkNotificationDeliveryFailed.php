<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\NotificationDelivery;
use App\Notifications\BackInStockNotification;
use App\Notifications\CommerceOrderNotification;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Support\Facades\DB;

final class MarkNotificationDeliveryFailed
{
    public function handle(NotificationFailed $failed): void
    {
        if (! $failed->notification instanceof CommerceOrderNotification
            && ! $failed->notification instanceof BackInStockNotification) {
            return;
        }

        $exception = $failed->data['exception'] ?? null;
        $failure = is_object($exception)
            ? 'Delivery failed: '.class_basename($exception)
            : 'Notification channel reported failure.';

        DB::transaction(function () use ($failed, $failure): void {
            $delivery = NotificationDelivery::query()
                ->whereKey($failed->notification->delivery->id)
                ->lockForUpdate()
                ->first();
            if ($delivery === null || $delivery->status === NotificationDelivery::STATUS_SENT) {
                return;
            }

            $delivery->update([
                'status' => NotificationDelivery::STATUS_FAILED,
                'attempts' => min(255, $delivery->attempts + 1),
                'last_error' => $failure,
                'failed_at' => now(),
            ]);
        });
    }
}
