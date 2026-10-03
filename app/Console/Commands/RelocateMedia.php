<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\MediaRelocationService;
use Illuminate\Console\Command;

/**
 * Ops tool: put every stored image on the configured media disk and report
 * whether the browser-facing storage link exists. Safe to re-run (idempotent).
 */
final class RelocateMedia extends Command
{
    protected $signature = 'media:relocate {--dry-run : Show what would move without changing anything}';

    protected $description = 'Move media stored on other disks to the media disk (MEDIA_DISK) and check the public storage link';

    public function handle(MediaRelocationService $media): int
    {
        $info = $media->diagnostics();

        $this->info('Media storage');
        $this->line('  Media disk:    '.$info['disk'].' ('.$info['driver'].')');
        $this->line('  Public URL:    '.($info['url'] ?? '— (driver builds its own URLs)'));

        if (! $info['public']) {
            $this->warn('  WARNING: disk "'.$info['disk'].'" is not publicly readable — the storefront cannot serve its files. Set MEDIA_DISK=public.');
        }

        foreach ($info['links'] as $link) {
            $this->line('  Storage link:  '.($link['ok']
                ? 'ok ('.$link['link'].')'
                : 'MISSING — run: php artisan storage:link'));
        }

        $dryRun = (bool) $this->option('dry-run');
        $report = $media->relocateAll($dryRun);

        $this->newLine();

        if ($report['examined'] === 0) {
            $this->info('All media is already on the "'.$report['target'].'" disk. Nothing to move.');

            return self::SUCCESS;
        }

        $verb = $dryRun ? 'Would move' : 'Moved';
        foreach ($report['moved'] as $row) {
            $this->line("  {$verb} media #{$row['id']} from \"{$row['from']}\" → \"{$report['target']}\" ({$row['files']} file(s))");
        }

        foreach ($report['failed'] as $row) {
            $this->error("  FAILED media #{$row['id']}: {$row['reason']}");
        }

        $this->newLine();
        $summary = sprintf('%s %d of %d media item(s); %d failed.', $dryRun ? 'Would move' : 'Moved', count($report['moved']), $report['examined'], count($report['failed']));
        $report['failed'] === [] ? $this->info($summary) : $this->warn($summary);

        return $report['failed'] === [] ? self::SUCCESS : self::FAILURE;
    }
}
