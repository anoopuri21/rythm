<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\CatalogueImportService;
use Illuminate\Console\Command;
use Throwable;

final class ImportCatalogueRun extends Command
{
    protected $signature = 'catalogue:import {run : Absolute disposable acquisition-run directory} {--commit : Create inactive review-required catalogue records}';

    protected $description = 'Validate or import a bounded catalogue acquisition run';

    public function handle(CatalogueImportService $service): int
    {
        try {
            $result = $service->import((string) $this->argument('run'), (bool) $this->option('commit'));
        } catch (Throwable $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->table(['Metric', 'Result'], [
            ['Mode', $result['mode']],
            ['Files', $result['files']],
            ['Validated', $result['validated']],
            ['Created inactive', $result['created']],
            ['Skipped unchanged', $result['skipped_unchanged']],
            ['Conflicts', $result['conflicts']],
            ['Failed', $result['failed']],
            ['Media attached', $result['media_attached']],
        ]);

        foreach ($result['errors'] as $error) {
            $this->components->warn($error['file'].': '.$error['error']);
        }

        if ($result['failed'] > 0 || $result['conflicts'] > 0) {
            $this->components->error('Import requires review; no conflict was overwritten.');

            return self::FAILURE;
        }

        $this->components->success($result['mode'] === 'commit'
            ? 'Import completed as inactive review-required records.'
            : 'Dry run passed; no catalogue data was written.');

        return self::SUCCESS;
    }
}
