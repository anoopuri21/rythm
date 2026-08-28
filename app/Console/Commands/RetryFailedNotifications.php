<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\NotificationDelivery;
use App\Services\NotificationRetryService;
use Illuminate\Console\Command;

final class RetryFailedNotifications extends Command
{
    protected $signature = 'notifications:retry-failed {--limit=10 : Maximum known failures to retry (1-50)}';

    protected $description = 'Retry a bounded set of known failed customer notification deliveries';

    public function handle(NotificationRetryService $service): int
    {
        $limit = filter_var($this->option('limit'), FILTER_VALIDATE_INT);
        if ($limit === false || $limit < 1 || $limit > 50) {
            $this->error('The limit must be an integer between 1 and 50.');

            return self::INVALID;
        }

        $deliveries = NotificationDelivery::query()
            ->where('status', NotificationDelivery::STATUS_FAILED)
            ->where('attempts', '<', NotificationRetryService::MAX_ATTEMPTS)
            ->whereNotNull('user_id')
            ->orderBy('failed_at')
            ->limit($limit)
            ->get();
        $retried = 0;
        $failed = 0;

        foreach ($deliveries as $delivery) {
            try {
                $service->retry($delivery);
                $retried++;
            } catch (\RuntimeException) {
                $failed++;
            }
        }

        $this->info("Queued {$retried} known failed delivery retry/retries; local failures: {$failed}.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
