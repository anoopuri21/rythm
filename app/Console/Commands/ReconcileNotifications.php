<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\NotificationReconciliationService;
use Illuminate\Console\Command;

final class ReconcileNotifications extends Command
{
    protected $signature = 'notifications:reconcile {--limit=100 : Maximum deliveries to inspect (1-500)} {--json : Emit JSON}';

    protected $description = 'Read-only notification delivery reconciliation report';

    public function handle(NotificationReconciliationService $service): int
    {
        $limit = filter_var($this->option('limit'), FILTER_VALIDATE_INT);
        if ($limit === false || $limit < 1 || $limit > 500) {
            $this->error('The limit must be an integer between 1 and 500.');

            return self::INVALID;
        }

        $report = $service->scan($limit);
        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
        } else {
            $this->info("Scanned {$report['scanned']} delivery record(s); findings: ".count($report['findings']).'.');
            if ($report['truncated']) {
                $this->warn("Report reached the {$report['limit']}-delivery bound.");
            }
            if ($report['findings'] !== []) {
                $this->table(['Code', 'Delivery', 'Event', 'Channel', 'Detail'], array_map(
                    fn (array $finding): array => array_values($finding),
                    $report['findings'],
                ));
            }
        }

        return $report['findings'] === [] ? self::SUCCESS : self::FAILURE;
    }
}
