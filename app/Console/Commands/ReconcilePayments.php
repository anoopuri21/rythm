<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\FinancialReconciliationService;
use Illuminate\Console\Command;

final class ReconcilePayments extends Command
{
    protected $signature = 'payments:reconcile {--limit=100 : Maximum orders to inspect (1-500)} {--json : Emit machine-readable JSON}';

    protected $description = 'Read-only internal payment and refund reconciliation report';

    public function handle(FinancialReconciliationService $reconciliation): int
    {
        $limit = filter_var($this->option('limit'), FILTER_VALIDATE_INT);
        if ($limit === false || $limit < 1 || $limit > 500) {
            $this->error('The limit must be an integer between 1 and 500.');

            return self::INVALID;
        }

        $report = $reconciliation->scan($limit);

        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        } else {
            $this->info("Scanned {$report['scanned']} order(s); findings: ".count($report['findings']).'.');
            if ($report['truncated']) {
                $this->warn("Report reached the {$report['limit']}-order bound.");
            }
            if ($report['findings'] !== []) {
                $this->table(['Code', 'Order', 'Payment', 'Detail'], array_map(
                    fn (array $finding): array => [
                        $finding['code'],
                        $finding['order'],
                        $finding['payment_id'] ?? '—',
                        $finding['detail'],
                    ],
                    $report['findings'],
                ));
            }
        }

        return $report['findings'] === [] ? self::SUCCESS : self::FAILURE;
    }
}
