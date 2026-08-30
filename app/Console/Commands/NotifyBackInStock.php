<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Events\BackInStockNotificationRequested;
use App\Models\BackInStockSubscription;
use Illuminate\Console\Command;

final class NotifyBackInStock extends Command
{
    protected $signature = 'back-in-stock:notify {--limit=100 : Maximum pending requests to inspect (1-500)}';

    protected $description = 'Queue a bounded batch of consented back-in-stock emails';

    public function handle(): int
    {
        $limit = filter_var($this->option('limit'), FILTER_VALIDATE_INT);
        if ($limit === false || $limit < 1 || $limit > 500) {
            $this->error('The limit must be an integer between 1 and 500.');

            return self::INVALID;
        }

        $subscriptions = BackInStockSubscription::query()
            ->pending()
            ->with(['product', 'variant'])
            ->orderBy('id')
            ->limit($limit)
            ->get();
        $queued = 0;

        foreach ($subscriptions as $subscription) {
            $variant = $subscription->variant;
            $stock = $variant?->stock ?? $subscription->product?->stock ?? 0;
            if (! $subscription->product?->is_active || $stock < 1) {
                continue;
            }

            BackInStockNotificationRequested::dispatch($subscription->id);
            $queued++;
        }

        $this->info("Queued {$queued} back-in-stock notification request(s) from a {$limit}-record bound.");

        return self::SUCCESS;
    }
}
