<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\NotificationDelivery;

final class NotificationReconciliationService
{
    /** @return array{scanned:int,limit:int,truncated:bool,findings:list<array{code:string,delivery_id:int,event:string,channel:string,detail:string}>} */
    public function scan(int $limit = 100): array
    {
        $limit = min(500, max(1, $limit));
        $deliveries = NotificationDelivery::query()
            ->with('event:id,event_key')
            ->orderBy('id')
            ->limit($limit + 1)
            ->get();
        $truncated = $deliveries->count() > $limit;
        $deliveries = $deliveries->take($limit);
        $findings = [];

        foreach ($deliveries as $delivery) {
            $finding = match (true) {
                $delivery->status === NotificationDelivery::STATUS_FAILED && $delivery->attempts >= NotificationRetryService::MAX_ATTEMPTS => ['DELIVERY_RETRY_EXHAUSTED', 'Known failure reached the retry limit.'],
                $delivery->status === NotificationDelivery::STATUS_FAILED => ['DELIVERY_FAILED', 'Known failure is eligible for bounded retry.'],
                $delivery->status === NotificationDelivery::STATUS_QUEUED && $delivery->queued_at?->lt(now()->subMinutes(15)) => ['DELIVERY_STALE_QUEUED', 'Queued delivery has no outcome after 15 minutes.'],
                $delivery->status === NotificationDelivery::STATUS_SENT && $delivery->sent_at === null => ['DELIVERY_SENT_TIME_MISSING', 'Sent delivery has no completion timestamp.'],
                default => null,
            };

            if ($finding !== null) {
                $findings[] = [
                    'code' => $finding[0],
                    'delivery_id' => $delivery->id,
                    'event' => $delivery->event->event_key,
                    'channel' => $delivery->channel,
                    'detail' => $finding[1],
                ];
            }
        }

        return [
            'scanned' => $deliveries->count(),
            'limit' => $limit,
            'truncated' => $truncated,
            'findings' => $findings,
        ];
    }
}
