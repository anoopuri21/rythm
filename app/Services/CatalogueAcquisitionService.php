<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

final class CatalogueAcquisitionService
{
    public function __construct(private readonly CataloguePublicationReviewService $publicationReview) {}

    /** @param list<string> $selectedHandles
     * @param  array<string, string>  $selectedSourceIds
     * @return array<string, mixed>
     */
    public function acquire(
        string $collection,
        int $limit,
        int $delayMs,
        int $imageLimit,
        int $maxImageBytes,
        int $maxRunBytes,
        string $outputRoot,
        bool $downloadImages = true,
        ?string $resumeRunId = null,
        array $selectedHandles = [],
        array $selectedSourceIds = [],
    ): array {
        $this->guardInputs($collection, $limit, $delayMs, $imageLimit, $maxImageBytes, $maxRunBytes, $selectedHandles, $selectedSourceIds);
        if ($resumeRunId !== null && ! preg_match('/^[A-Za-z0-9-]+$/', $resumeRunId)) {
            throw new RuntimeException('Resume run ID is invalid.');
        }

        $startedAt = microtime(true);
        $runId = $resumeRunId ?? now()->format('Ymd-His').'-'.Str::lower(Str::random(6));
        $runDirectory = rtrim($outputRoot, '/\\').DIRECTORY_SEPARATOR.$runId;
        $mediaDirectory = $runDirectory.DIRECTORY_SEPARATOR.'media';
        $this->makeDirectory($mediaDirectory);

        $report = [
            'run_id' => $runId,
            'source' => 'bajaao-public-shopify-json',
            'collection' => $collection,
            'requested_limit' => $limit,
            'selected_handles' => $selectedHandles,
            'started_at' => now()->toIso8601String(),
            'request_count' => 0,
            'products_discovered' => 0,
            'products_completed' => 0,
            'products_failed' => 0,
            'resumed_products' => 0,
            'images_downloaded' => 0,
            'image_failures' => 0,
            'raw_bytes' => 0,
            'normalized_bytes' => 0,
            'media_bytes' => 0,
            'errors' => [],
            'products' => [],
        ];

        try {
            $collectionLimit = $selectedHandles === [] ? $limit : 50;
            $collectionUrl = $this->sourceUrl("/collections/{$collection}/products.json?limit={$collectionLimit}");
            $collectionResponse = $this->request()->get($collectionUrl)->throw();
            $report['request_count']++;
            $report['raw_bytes'] += strlen($collectionResponse->body());
            $collectionProducts = array_values(array_filter($collectionResponse->json('products', []), 'is_array'));

            if ($selectedHandles === []) {
                $products = array_slice($collectionProducts, 0, $limit);
            } else {
                $byHandle = [];
                foreach ($collectionProducts as $product) {
                    $byHandle[(string) ($product['handle'] ?? '')] = $product;
                }
                $missing = array_values(array_diff($selectedHandles, array_keys($byHandle)));
                if ($missing !== []) {
                    throw new RuntimeException('Selected handles are absent from the public collection: '.implode(', ', $missing));
                }
                $products = array_map(static fn (string $handle): array => $byHandle[$handle], $selectedHandles);
            }

            $report['products_discovered'] = count($products);

            foreach ($products as $position => $summary) {
                if ($position > 0 && $delayMs > 0) {
                    usleep($delayMs * 1000);
                }

                $handle = is_array($summary) ? (string) ($summary['handle'] ?? '') : '';
                if (! preg_match('/^[a-z0-9][a-z0-9-]*$/', $handle)) {
                    $report['products_failed']++;
                    $report['errors'][] = ['handle' => $handle, 'error' => 'Invalid or missing public product handle.'];

                    continue;
                }

                $productFile = $runDirectory.DIRECTORY_SEPARATOR.$handle.'.json';
                if ($resumeRunId !== null && is_file($productFile)) {
                    $existing = json_decode((string) file_get_contents($productFile), true, flags: JSON_THROW_ON_ERROR);
                    if ($selectedSourceIds !== [] && (string) ($existing['source_product_id'] ?? '') !== $selectedSourceIds[$handle]) {
                        throw new RuntimeException("Resumed source identity differs from the manifest: {$handle}");
                    }
                    $report['products_completed']++;
                    $report['resumed_products']++;
                    $report['normalized_bytes'] += filesize($productFile) ?: 0;
                    foreach ($existing['media'] ?? [] as $media) {
                        $report['images_downloaded']++;
                        $report['media_bytes'] += (int) ($media['bytes'] ?? 0);
                    }
                    $report['products'][] = [
                        'handle' => $handle,
                        'name' => (string) ($existing['name'] ?? ''),
                        'brand' => (string) ($existing['brand'] ?? ''),
                        'variants' => count($existing['variants'] ?? []),
                        'images' => count($existing['media'] ?? []),
                        'publication_review_required' => (bool) ($existing['publication_review']['required'] ?? false),
                    ];

                    continue;
                }

                try {
                    $response = $this->request()->get($this->sourceUrl("/products/{$handle}.json"))->throw();
                    $report['request_count']++;
                    $report['raw_bytes'] += strlen($response->body());
                    $source = $response->json('product');
                    if (! is_array($source)) {
                        throw new RuntimeException('Product JSON does not contain a product object.');
                    }
                    if ($selectedSourceIds !== [] && (string) ($source['id'] ?? '') !== $selectedSourceIds[$handle]) {
                        throw new RuntimeException("Source product identity changed for selected handle: {$handle}");
                    }

                    $normalized = $this->normalize($source, $collection);
                    $normalized['media'] = [];

                    if ($downloadImages) {
                        foreach (array_slice($source['images'] ?? [], 0, $imageLimit) as $image) {
                            try {
                                $media = $this->downloadImage(
                                    is_array($image) ? (string) ($image['src'] ?? '') : '',
                                    $mediaDirectory,
                                    $handle,
                                    count($normalized['media']) + 1,
                                    $maxImageBytes,
                                    $maxRunBytes - (int) $report['media_bytes'],
                                );
                                $normalized['media'][] = $media;
                                $report['images_downloaded']++;
                                $report['media_bytes'] += $media['bytes'];
                                $report['request_count']++;
                            } catch (Throwable $exception) {
                                $report['image_failures']++;
                                $report['errors'][] = ['handle' => $handle, 'error' => $exception->getMessage()];
                            }
                        }
                    }

                    $json = json_encode($normalized, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
                    file_put_contents($runDirectory.DIRECTORY_SEPARATOR.$handle.'.json', $json.PHP_EOL, LOCK_EX);
                    $report['normalized_bytes'] += strlen($json) + 1;
                    $report['products_completed']++;
                    $report['products'][] = [
                        'handle' => $handle,
                        'name' => $normalized['name'],
                        'brand' => $normalized['brand'],
                        'variants' => count($normalized['variants']),
                        'images' => count($normalized['media']),
                        'publication_review_required' => (bool) $normalized['publication_review']['required'],
                    ];
                } catch (Throwable $exception) {
                    $report['products_failed']++;
                    $report['errors'][] = ['handle' => $handle, 'error' => $exception->getMessage()];
                }
            }
        } catch (Throwable $exception) {
            $report['errors'][] = ['handle' => null, 'error' => $exception->getMessage()];
        }

        $report['duration_seconds'] = round(microtime(true) - $startedAt, 3);
        $report['average_seconds_per_product'] = $report['products_completed'] > 0
            ? round($report['duration_seconds'] / $report['products_completed'], 3)
            : null;
        $report['estimated_60_product_seconds'] = $report['average_seconds_per_product'] !== null
            ? round($report['average_seconds_per_product'] * 60, 1)
            : null;
        $report['estimated_60_product_media_bytes'] = $report['products_completed'] > 0
            ? (int) ceil(($report['media_bytes'] / $report['products_completed']) * 60)
            : null;
        $report['completed_at'] = now()->toIso8601String();
        $report['output_directory'] = $runDirectory;

        file_put_contents(
            $runDirectory.DIRECTORY_SEPARATOR.'report.json',
            json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL,
            LOCK_EX,
        );

        return $report;
    }

    /** @param array<string, mixed> $source
     * @return array<string, mixed>
     */
    private function normalize(array $source, string $collection): array
    {
        $variants = array_map(static fn (array $variant): array => [
            'source_id' => (string) ($variant['id'] ?? ''),
            'name' => trim((string) ($variant['title'] ?? 'Default')),
            'sku' => trim((string) ($variant['sku'] ?? '')),
            'price' => (string) ($variant['price'] ?? '0.00'),
            'compare_at_price' => isset($variant['compare_at_price']) ? (string) $variant['compare_at_price'] : null,
            'available' => (bool) ($variant['available'] ?? false),
            'options' => array_values(array_filter([
                $variant['option1'] ?? null,
                $variant['option2'] ?? null,
                $variant['option3'] ?? null,
            ], static fn (mixed $value): bool => is_string($value) && $value !== '')),
        ], array_values(array_filter($source['variants'] ?? [], 'is_array')));

        $prices = array_map(static fn (array $variant): float => (float) ($variant['price'] ?? 0), array_values(array_filter($source['variants'] ?? [], 'is_array')));
        $comparePrices = array_map(static fn (array $variant): float => (float) ($variant['compare_at_price'] ?? 0), array_values(array_filter($source['variants'] ?? [], 'is_array')));
        $bodyHtml = (string) ($source['body_html'] ?? '');
        $bodyHtml = preg_replace('~<(script|style|iframe)\b[^>]*>.*?</\\1>~is', ' ', $bodyHtml) ?? '';
        $plainDescription = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($bodyHtml), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');
        $title = trim((string) ($source['title'] ?? ''));

        return [
            'schema_version' => 1,
            'source_product_id' => (string) ($source['id'] ?? ''),
            'source_url' => $this->sourceUrl('/products/'.(string) ($source['handle'] ?? '')),
            'collection' => $collection,
            'name' => $title,
            'slug' => trim((string) ($source['handle'] ?? '')),
            'brand' => trim((string) ($source['vendor'] ?? '')),
            'product_type' => trim((string) ($source['product_type'] ?? '')),
            'description_text' => $plainDescription,
            'short_description' => Str::limit($plainDescription, 500, ''),
            'publication_review' => $this->publicationReview->assess($title, $plainDescription),
            'price' => $prices === [] ? '0.00' : number_format(min($prices), 2, '.', ''),
            'compare_at_price' => max($comparePrices ?: [0]) > max($prices ?: [0]) ? number_format(max($comparePrices), 2, '.', '') : null,
            'available' => in_array(true, array_column($variants, 'available'), true),
            'published_at' => $source['published_at'] ?? null,
            'updated_at_source' => $source['updated_at'] ?? null,
            'tags' => is_array($source['tags'] ?? null) ? array_values($source['tags']) : [],
            'options' => is_array($source['options'] ?? null) ? array_values($source['options']) : [],
            'variants' => $variants,
        ];
    }

    /** @return array{file:string,bytes:int,sha256:string,mime:string,width:int,height:int} */
    private function downloadImage(string $url, string $directory, string $handle, int $position, int $maxImageBytes, int $remainingRunBytes): array
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        if (! in_array($host, config('catalogue.source.media_hosts', []), true)) {
            throw new RuntimeException("Rejected media host: {$host}");
        }

        $response = $this->request()->get($url)->throw();
        $body = $response->body();
        $bytes = strlen($body);
        if ($bytes === 0 || $bytes > $maxImageBytes || $bytes > $remainingRunBytes) {
            throw new RuntimeException("Image byte budget rejected {$bytes} bytes.");
        }

        $mime = strtolower(trim(explode(';', $response->header('Content-Type', ''))[0]));
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        if (! isset($extensions[$mime])) {
            throw new RuntimeException("Rejected media MIME type: {$mime}");
        }

        $dimensions = getimagesizefromstring($body);
        if ($dimensions === false || ($dimensions['mime'] ?? '') !== $mime) {
            throw new RuntimeException('Image content does not match its declared MIME type.');
        }

        $filename = sprintf('%s-%02d-%s.%s', $handle, $position, substr(hash('sha256', $body), 0, 12), $extensions[$mime]);
        file_put_contents($directory.DIRECTORY_SEPARATOR.$filename, $body, LOCK_EX);

        return [
            'file' => 'media/'.$filename,
            'bytes' => $bytes,
            'sha256' => hash('sha256', $body),
            'mime' => $mime,
            'width' => (int) $dimensions[0],
            'height' => (int) $dimensions[1],
        ];
    }

    private function request(): PendingRequest
    {
        return Http::withHeaders(['User-Agent' => (string) config('catalogue.source.user_agent')])
            ->acceptJson()
            ->connectTimeout(10)
            ->timeout(30)
            ->retry(2, 1000, throw: false);
    }

    private function sourceUrl(string $path): string
    {
        $baseUrl = rtrim((string) config('catalogue.source.base_url'), '/');
        $host = strtolower((string) parse_url($baseUrl, PHP_URL_HOST));
        if ($host !== config('catalogue.source.allowed_host')) {
            throw new RuntimeException("Rejected source host: {$host}");
        }

        return $baseUrl.'/'.ltrim($path, '/');
    }

    /** @param list<string> $selectedHandles
     * @param  array<string, string>  $selectedSourceIds
     */
    private function guardInputs(string $collection, int $limit, int $delayMs, int $imageLimit, int $maxImageBytes, int $maxRunBytes, array $selectedHandles, array $selectedSourceIds): void
    {
        if (! preg_match('/^[a-z0-9][a-z0-9-]*$/', $collection)) {
            throw new RuntimeException('Collection must be a public Shopify collection handle.');
        }
        if ($limit < 1 || $limit > 10) {
            throw new RuntimeException('Pilot limit must be between 1 and 10 products.');
        }
        if ($selectedHandles !== []) {
            if (count($selectedHandles) !== $limit || count(array_unique($selectedHandles)) !== count($selectedHandles)) {
                throw new RuntimeException('Selected handles must be unique and match the requested limit.');
            }
            foreach ($selectedHandles as $handle) {
                if (! is_string($handle) || ! preg_match('/^[a-z0-9][a-z0-9-]*$/', $handle)) {
                    throw new RuntimeException('Selected product handle is invalid.');
                }
            }
            if ($selectedSourceIds !== [] && array_keys($selectedSourceIds) !== $selectedHandles) {
                throw new RuntimeException('Selected source identities must match the ordered handles.');
            }
            foreach ($selectedSourceIds as $sourceId) {
                if (! is_string($sourceId) || ! ctype_digit($sourceId)) {
                    throw new RuntimeException('Selected source product identity is invalid.');
                }
            }
        } elseif ($selectedSourceIds !== []) {
            throw new RuntimeException('Selected source identities require selected handles.');
        }
        if ($delayMs < 1000 || $delayMs > 10000) {
            throw new RuntimeException('Request delay must be between 1000 and 10000 milliseconds.');
        }
        if ($imageLimit < 0 || $imageLimit > 5 || $maxImageBytes < 1 || $maxRunBytes < $maxImageBytes) {
            throw new RuntimeException('Media limits are invalid.');
        }
    }

    private function makeDirectory(string $directory): void
    {
        if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
            throw new RuntimeException("Could not create run directory: {$directory}");
        }
    }
}
