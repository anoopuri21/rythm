<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\CatalogueAcquisitionService;
use App\Services\CatalogueExpansionManifestService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

final class AcquireCatalogueExpansion extends Command
{
    protected $signature = 'catalogue:acquire-expansion
        {manifest=tasks/PHASE_6A_ACQUISITION_MANIFEST.json : Manifest path, absolute or relative to the project}
        {--group=* : One or two public collection handles from the manifest}
        {--batch= : Safe batch ID used for resumable output}
        {--delay=1500 : Delay between product requests in milliseconds}
        {--images=3 : Maximum gallery images per product}
        {--output= : Disposable output root outside the repository}
        {--no-images : Validate product data without downloading media}';

    protected $description = 'Acquire one bounded multi-category manifest batch into disposable review-controlled staging';

    public function handle(CatalogueExpansionManifestService $manifests, CatalogueAcquisitionService $acquisition): int
    {
        try {
            $manifest = $manifests->load((string) $this->argument('manifest'));
            $selected = array_values(array_unique(array_filter(
                array_map('strval', (array) $this->option('group')),
                static fn (string $group): bool => $group !== '',
            )));
            if ($selected === [] || count($selected) > 2) {
                throw new RuntimeException('Select one or two manifest groups with --group.');
            }

            $groups = collect($manifest['groups'])->keyBy('source_collection');
            $unknown = array_values(array_diff($selected, $groups->keys()->all()));
            if ($unknown !== []) {
                throw new RuntimeException('Unknown manifest groups: '.implode(', ', $unknown));
            }

            $outputRoot = (string) ($this->option('output') ?: config('catalogue.pilot.output_root'));
            if ($this->isInsideRepository($outputRoot)) {
                throw new RuntimeException('Expansion output must be outside the repository.');
            }

            $batch = (string) ($this->option('batch') ?: now()->format('Ymd-His').'-'.Str::lower(Str::random(6)));
            if (! preg_match('/^[A-Za-z0-9-]+$/', $batch)) {
                throw new RuntimeException('Batch ID is invalid.');
            }
            $batchDirectory = rtrim($outputRoot, '/\\').DIRECTORY_SEPARATOR.$batch;
            if (! is_dir($batchDirectory) && ! mkdir($batchDirectory, 0700, true) && ! is_dir($batchDirectory)) {
                throw new RuntimeException('Could not create expansion batch directory.');
            }

            $combined = [
                'schema_version' => 1,
                'batch_id' => $batch,
                'manifest_sha256' => $manifest['_sha256'],
                'manifest_path' => $manifest['_path'],
                'started_at' => now()->toIso8601String(),
                'groups_requested' => $selected,
                'products_requested' => 0,
                'products_completed' => 0,
                'products_failed' => 0,
                'publication_review_required' => 0,
                'media_bytes' => 0,
                'groups' => [],
            ];

            foreach ($selected as $collection) {
                /** @var array<string, mixed> $group */
                $group = $groups->get($collection);
                $handles = array_values(array_map(
                    static fn (array $product): string => (string) $product['handle'],
                    $group['products'],
                ));
                $sourceIds = [];
                foreach ($group['products'] as $product) {
                    $sourceIds[(string) $product['handle']] = (string) $product['source_product_id'];
                }
                $this->components->info("Acquiring manifest group '{$collection}' (".count($handles).' products).');

                $report = $acquisition->acquire(
                    collection: $collection,
                    limit: count($handles),
                    delayMs: (int) $this->option('delay'),
                    imageLimit: (int) $this->option('images'),
                    maxImageBytes: (int) config('catalogue.pilot.max_image_bytes'),
                    maxRunBytes: (int) config('catalogue.pilot.max_run_bytes'),
                    outputRoot: $batchDirectory,
                    downloadImages: ! $this->option('no-images'),
                    resumeRunId: $collection,
                    selectedHandles: $handles,
                    selectedSourceIds: $sourceIds,
                );

                $reviewCount = collect($report['products'])->where('publication_review_required', true)->count();
                $combined['products_requested'] += count($handles);
                $combined['products_completed'] += (int) $report['products_completed'];
                $combined['products_failed'] += (int) $report['products_failed'];
                $combined['publication_review_required'] += $reviewCount;
                $combined['media_bytes'] += (int) $report['media_bytes'];
                $combined['groups'][] = [
                    'collection' => $collection,
                    'category_slug' => $group['category_slug'],
                    'run_id' => $report['run_id'],
                    'products_completed' => $report['products_completed'],
                    'products_failed' => $report['products_failed'],
                    'publication_review_required' => $reviewCount,
                    'media_bytes' => $report['media_bytes'],
                    'output_directory' => $report['output_directory'],
                ];
            }

            $combined['completed_at'] = now()->toIso8601String();
            $combined['complete'] = $combined['products_completed'] === $combined['products_requested']
                && $combined['products_failed'] === 0;
            $combinedPath = $batchDirectory.DIRECTORY_SEPARATOR.'batch-report.json';
            file_put_contents($combinedPath, json_encode($combined, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL, LOCK_EX);

            $this->table(['Metric', 'Result'], [
                ['Batch', $batch],
                ['Groups', implode(', ', $selected)],
                ['Products', "{$combined['products_completed']}/{$combined['products_requested']}"],
                ['Failures', $combined['products_failed']],
                ['Publication review required', $combined['publication_review_required']],
                ['Media', $this->bytes((int) $combined['media_bytes'])],
                ['Report', $combinedPath],
            ]);

            if (! $combined['complete']) {
                $this->components->error('Expansion batch is incomplete. Inspect the batch and group reports before retrying.');

                return self::FAILURE;
            }

            $this->components->success('Expansion batch staged successfully. Nothing was imported or published.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }
    }

    private function isInsideRepository(string $path): bool
    {
        $normalized = str_replace('\\', '/', $path);
        $repository = str_replace('\\', '/', base_path());

        return $normalized === $repository || str_starts_with($normalized.'/', rtrim($repository, '/').'/');
    }

    private function bytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        if ($bytes < 1024 * 1024) {
            return number_format($bytes / 1024, 1).' KiB';
        }

        return number_format($bytes / 1024 / 1024, 2).' MiB';
    }
}
