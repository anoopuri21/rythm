<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\NotificationDelivery;
use App\Notifications\BackInStockNotification;
use App\Notifications\CommerceOrderNotification;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\DB;

final class MarkNotificationDeliverySent
{
    public function handle(NotificationSent $sent): void
    {
        if (! $sent->notification instanceof CommerceOrderNotification
            && ! $sent->notification instanceof BackInStockNotification) {
            return;
        }

        DB::transaction(function () use ($sent): void {
            $delivery = NotificationDelivery::query()
                ->whereKey($sent->notification->delivery->id)
                ->lockForUpdate()
                ->first();
            if ($delivery === null || $delivery->status === NotificationDelivery::STATUS_SENT) {
                return;
            }

            $delivery->update([
                'status' => NotificationDelivery::STATUS_SENT,
                'attempts' => min(255, $delivery->attempts + 1),
                'last_error' => null,
                'sent_at' => now(),
                'failed_at' => null,
            ]);
        });
    }
}
