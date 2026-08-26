<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\CatalogueAcquisitionService;
use Illuminate\Console\Command;

final class AcquireCataloguePilot extends Command
{
    protected $signature = 'catalogue:acquire-pilot
        {--collection= : Public Shopify collection handle}
        {--limit= : Product limit, maximum 10}
        {--delay= : Delay between product requests in milliseconds}
        {--images= : Maximum gallery images per product, maximum 5}
        {--output= : Disposable output root outside the repository}
        {--resume= : Existing run ID to resume without re-downloading completed products}
        {--no-images : Validate product data without downloading media}';

    protected $description = 'Acquire a bounded, public catalogue pilot into disposable staging storage';

    public function handle(CatalogueAcquisitionService $service): int
    {
        $collection = (string) ($this->option('collection') ?: config('catalogue.pilot.collection'));
        $limit = (int) ($this->option('limit') ?: config('catalogue.pilot.limit'));
        $delay = (int) ($this->option('delay') ?: config('catalogue.pilot.delay_ms'));
        $images = (int) ($this->option('images') ?? config('catalogue.pilot.images_per_product'));
        $output = (string) ($this->option('output') ?: config('catalogue.pilot.output_root'));

        if ($this->isInsideRepository($output)) {
            $this->components->error('Pilot output must be outside the repository to protect workspace capacity.');

            return self::FAILURE;
        }

        $this->components->info("Acquiring {$limit} products from public collection '{$collection}'.");
        $report = $service->acquire(
            collection: $collection,
            limit: $limit,
            delayMs: $delay,
            imageLimit: $images,
            maxImageBytes: (int) config('catalogue.pilot.max_image_bytes'),
            maxRunBytes: (int) config('catalogue.pilot.max_run_bytes'),
            outputRoot: $output,
            downloadImages: ! $this->option('no-images'),
            resumeRunId: $this->option('resume') !== null ? (string) $this->option('resume') : null,
        );

        $this->table(
            ['Metric', 'Result'],
            [
                ['Run', $report['run_id']],
                ['Products', "{$report['products_completed']}/{$report['products_discovered']}"],
                ['Resumed without download', $report['resumed_products']],
                ['Images', $report['images_downloaded']],
                ['Requests', $report['request_count']],
                ['Duration', $report['duration_seconds'].' seconds'],
                ['Raw data', $this->bytes((int) $report['raw_bytes'])],
                ['Normalized data', $this->bytes((int) $report['normalized_bytes'])],
                ['Media', $this->bytes((int) $report['media_bytes'])],
                ['Estimated 60-product media', $this->bytes((int) ($report['estimated_60_product_media_bytes'] ?? 0))],
                ['Output', $report['output_directory']],
            ],
        );

        if ($report['products_completed'] !== $report['products_discovered'] || $report['products_completed'] !== $limit) {
            $this->components->error('Pilot did not complete every requested product. Inspect report.json.');

            return self::FAILURE;
        }

        $this->components->success('Bounded acquisition pilot completed. Data is staged only and has not been imported or published.');

        return self::SUCCESS;
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
