<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\CatalogueImportService;
use Illuminate\Console\Command;
use RuntimeException;
use Throwable;

final class ImportCatalogueExpansion extends Command
{
    protected $signature = 'catalogue:import-expansion
        {batch : Safe batch ID under the configured disposable root, or an absolute batch directory}
        {--commit : Create inactive, zero-stock, review-required catalogue records}
        {--allow-conflicts : Import ready records while safely holding existing changed/slug conflicts without overwrite}';

    protected $description = 'Dry-run or explicitly import a completed expansion batch without activating products';

    public function handle(CatalogueImportService $imports): int
    {
        try {
            $batch = $this->validatedBatch((string) $this->argument('batch'));
            $report = json_decode((string) file_get_contents($batch.DIRECTORY_SEPARATOR.'batch-report.json'), true, flags: JSON_THROW_ON_ERROR);
            if (! is_array($report) || ! ($report['complete'] ?? false)) {
                throw new RuntimeException('Expansion batch report is missing or incomplete.');
            }
            if ((int) ($report['products_failed'] ?? 0) !== 0
                || (int) ($report['image_failures'] ?? 0) !== 0
                || (int) ($report['products_without_media'] ?? 0) !== 0) {
                throw new RuntimeException('Expansion batch contains product or media failures.');
            }

            $combined = [
                'mode' => $this->option('commit') ? 'commit' : 'dry-run',
                'groups' => 0,
                'files' => 0,
                'validated' => 0,
                'created' => 0,
                'skipped_unchanged' => 0,
                'conflicts' => 0,
                'failed' => 0,
                'media_attached' => 0,
                'reports' => [],
            ];

            foreach ($report['groups'] ?? [] as $group) {
                $directory = realpath((string) ($group['output_directory'] ?? ''));
                if ($directory === false || ! is_dir($directory) || ! str_starts_with($directory.DIRECTORY_SEPARATOR, $batch.DIRECTORY_SEPARATOR)) {
                    throw new RuntimeException('Expansion group directory is missing or outside its batch.');
                }
                $result = $imports->import($directory, (bool) $this->option('commit'));
                $combined['groups']++;
                foreach (['files', 'validated', 'created', 'skipped_unchanged', 'conflicts', 'failed', 'media_attached'] as $metric) {
                    $combined[$metric] += (int) $result[$metric];
                }
                $combined['reports'][] = [
                    'collection' => $group['collection'] ?? basename($directory),
                    'result' => $result,
                ];
            }

            $resultPath = $batch.DIRECTORY_SEPARATOR.'import-report.json';
            file_put_contents($resultPath, json_encode($combined, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL, LOCK_EX);

            $this->table(['Metric', 'Result'], [
                ['Mode', $combined['mode']],
                ['Groups', $combined['groups']],
                ['Files', $combined['files']],
                ['Validated', $combined['validated']],
                ['Created', $combined['created']],
                ['Unchanged', $combined['skipped_unchanged']],
                ['Conflicts', $combined['conflicts']],
                ['Failed', $combined['failed']],
                ['Media attached', $combined['media_attached']],
                ['Report', $resultPath],
            ]);

            $allowConflicts = (bool) $this->option('allow-conflicts');
            if ($combined['failed'] > 0 || ($combined['conflicts'] > 0 && ! $allowConflicts) || $combined['files'] === 0) {
                $this->components->error('Expansion import requires review; no activation occurred.');

                return self::FAILURE;
            }

            $message = match (true) {
                $this->option('commit') && $combined['conflicts'] > 0 => "{$combined['created']} ready records imported inactive; {$combined['conflicts']} existing conflicts remained safely held without overwrite.",
                (bool) $this->option('commit') => 'Expansion records imported inactive with zero stock and mandatory review controls.',
                $combined['conflicts'] > 0 => "Dry-run accepted with {$combined['conflicts']} safely held conflicts. Use --commit --allow-conflicts to import only ready records.",
                default => 'Expansion dry-run passed. Re-run with --commit only after reviewing this report.',
            };
            $this->components->success($message);

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }
    }

    private function validatedBatch(string $directory): string
    {
        $isAbsolute = str_starts_with($directory, DIRECTORY_SEPARATOR) || preg_match('/^[A-Za-z]:[\\\\\/]/', $directory) === 1;
        if (! $isAbsolute) {
            if (! preg_match('/^[A-Za-z0-9-]+$/', $directory)) {
                throw new RuntimeException('Expansion batch ID is invalid.');
            }
            $directory = rtrim((string) config('catalogue.pilot.output_root'), '/\\').DIRECTORY_SEPARATOR.$directory;
        }

        $real = realpath($directory);
        if ($real === false || ! is_dir($real)) {
            throw new RuntimeException('Expansion batch directory does not exist.');
        }
        $repository = str_replace('\\', '/', base_path());
        if (str_starts_with(str_replace('\\', '/', $real).DIRECTORY_SEPARATOR, rtrim($repository, '/').DIRECTORY_SEPARATOR)) {
            throw new RuntimeException('Expansion batch directory must be outside the repository.');
        }

        return $real;
    }
}
