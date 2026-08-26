<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImportSource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

final class CatalogueImportService
{
    /** @return array<string, mixed> */
    public function import(string $runDirectory, bool $commit = false): array
    {
        $runDirectory = $this->validatedRunDirectory($runDirectory);
        $files = array_values(array_filter(
            glob($runDirectory.DIRECTORY_SEPARATOR.'*.json') ?: [],
            static fn (string $file): bool => basename($file) !== 'report.json',
        ));

        $result = [
            'mode' => $commit ? 'commit' : 'dry-run',
            'files' => count($files),
            'validated' => 0,
            'created' => 0,
            'skipped_unchanged' => 0,
            'conflicts' => 0,
            'failed' => 0,
            'media_attached' => 0,
            'products' => [],
            'errors' => [],
        ];

        foreach ($files as $file) {
            $createdProduct = null;
            try {
                $payload = json_decode((string) file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
                $this->validatePayload($payload, $runDirectory);
                $hash = $this->payloadHash($payload);
                $existing = ProductImportSource::query()
                    ->where('source', 'bajaao')
                    ->where('source_product_id', $payload['source_product_id'])
                    ->first();

                if ($existing !== null) {
                    if (hash_equals($existing->payload_hash, $hash)) {
                        $result['skipped_unchanged']++;
                        $result['validated']++;
                        $result['products'][] = ['slug' => $payload['slug'], 'status' => 'unchanged'];

                        continue;
                    }

                    $result['conflicts']++;
                    $result['products'][] = ['slug' => $payload['slug'], 'status' => 'source-changed-owner-review-required'];

                    continue;
                }

                if (Product::withTrashed()->where('slug', $payload['slug'])->exists()) {
                    $result['conflicts']++;
                    $result['products'][] = ['slug' => $payload['slug'], 'status' => 'slug-conflict'];

                    continue;
                }

                $result['validated']++;
                if (! $commit) {
                    $result['products'][] = ['slug' => $payload['slug'], 'status' => 'ready'];

                    continue;
                }

                $createdProduct = $this->createProduct($payload, $hash);
                foreach ($payload['media'] as $media) {
                    $createdProduct->addMedia($runDirectory.DIRECTORY_SEPARATOR.$media['file'])
                        ->preservingOriginal()
                        ->withCustomProperties([
                            'source' => 'bajaao',
                            'source_sha256' => $media['sha256'],
                            'source_width' => $media['width'],
                            'source_height' => $media['height'],
                            'commercial_use_approved' => false,
                        ])
                        ->toMediaCollection('gallery');
                    $result['media_attached']++;
                }

                $result['created']++;
                $result['products'][] = ['slug' => $payload['slug'], 'status' => 'created-inactive'];
            } catch (Throwable $exception) {
                $createdProduct?->forceDelete();
                $result['failed']++;
                $result['errors'][] = ['file' => basename($file), 'error' => $exception->getMessage()];
            }
        }

        return $result;
    }

    /** @param array<string, mixed> $payload */
    private function createProduct(array $payload, string $hash): Product
    {
        return DB::transaction(function () use ($payload, $hash): Product {
            $category = Category::firstOrCreate(
                ['slug' => Str::slug((string) $payload['collection'])],
                ['name' => Str::headline((string) $payload['collection']), 'is_active' => false],
            );
            $brandName = trim((string) $payload['brand']);
            $brand = $brandName === '' ? null : Brand::firstOrCreate(
                ['slug' => Str::slug($brandName)],
                ['name' => $brandName, 'is_active' => false],
            );

            $productSku = $this->uniqueProductSku($payload);
            $product = Product::create([
                'category_id' => $category->id,
                'brand_id' => $brand?->id,
                'name' => $payload['name'],
                'slug' => $payload['slug'],
                'sku' => $productSku,
                'short_description' => $payload['short_description'],
                'description' => $payload['description_text'],
                'price' => $payload['price'],
                'compare_at_price' => $payload['compare_at_price'],
                'stock' => 0,
                'low_stock_threshold' => 5,
                'is_active' => false,
                'is_featured' => false,
            ]);

            foreach ($payload['variants'] as $index => $variant) {
                $product->variants()->create([
                    'name' => $this->variantName($variant, $index),
                    'options' => $variant['options'],
                    'sku' => $this->uniqueVariantSku($variant),
                    'price_override' => $variant['price'] === $payload['price'] ? null : $variant['price'],
                    'stock' => 0,
                    'is_active' => false,
                ]);
            }

            ProductImportSource::create([
                'product_id' => $product->id,
                'source' => 'bajaao',
                'source_product_id' => $payload['source_product_id'],
                'source_url' => $payload['source_url'],
                'payload_hash' => $hash,
                'imported_at' => now(),
            ]);

            return $product;
        });
    }

    /** @param array<string, mixed> $payload */
    private function validatePayload(array $payload, string $runDirectory): void
    {
        foreach (['schema_version', 'source_product_id', 'source_url', 'collection', 'name', 'slug', 'brand', 'description_text', 'price', 'variants', 'media'] as $field) {
            if (! array_key_exists($field, $payload)) {
                throw new RuntimeException("Missing required field: {$field}");
            }
        }
        if ($payload['schema_version'] !== 1 || ! preg_match('/^[a-z0-9][a-z0-9-]*$/', (string) $payload['slug'])) {
            throw new RuntimeException('Unsupported schema or invalid slug.');
        }
        if (parse_url((string) $payload['source_url'], PHP_URL_HOST) !== config('catalogue.source.allowed_host')) {
            throw new RuntimeException('Source URL host is not approved.');
        }
        if (! is_numeric($payload['price']) || (float) $payload['price'] < 0 || ! is_array($payload['variants']) || ! is_array($payload['media'])) {
            throw new RuntimeException('Price, variants or media are invalid.');
        }

        foreach ($payload['media'] as $media) {
            if (! is_array($media) || ! preg_match('~^media/[A-Za-z0-9._-]+$~', (string) ($media['file'] ?? ''))) {
                throw new RuntimeException('Media path is invalid.');
            }
            $path = $runDirectory.DIRECTORY_SEPARATOR.$media['file'];
            if (! is_file($path) || ! hash_equals((string) ($media['sha256'] ?? ''), hash_file('sha256', $path))) {
                throw new RuntimeException('Media file integrity check failed.');
            }
            $dimensions = getimagesize($path);
            if ($dimensions === false || $dimensions[0] !== (int) ($media['width'] ?? 0) || $dimensions[1] !== (int) ($media['height'] ?? 0)) {
                throw new RuntimeException('Media dimensions do not match the manifest.');
            }
        }
    }

    /** @param array<string, mixed> $payload */
    private function payloadHash(array $payload): string
    {
        $copy = $payload;
        unset($copy['media']);
        $copy['media_hashes'] = array_column($payload['media'], 'sha256');

        return hash('sha256', json_encode($copy, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    /** @param array<string, mixed> $payload */
    private function uniqueProductSku(array $payload): string
    {
        foreach ($payload['variants'] as $variant) {
            $sku = trim((string) ($variant['sku'] ?? ''));
            if ($sku !== '' && ! Product::withTrashed()->where('sku', $sku)->exists()) {
                return Str::limit($sku, 255, '');
            }
        }

        return 'BAJ-P-'.$payload['source_product_id'];
    }

    /** @param array<string, mixed> $variant */
    private function uniqueVariantSku(array $variant): string
    {
        $sourceId = preg_replace('/[^A-Za-z0-9-]/', '', (string) ($variant['source_id'] ?? ''));

        return 'BAJ-V-'.($sourceId !== '' ? $sourceId : Str::lower(Str::random(12)));
    }

    /** @param array<string, mixed> $variant */
    private function variantName(array $variant, int $index): string
    {
        $name = trim((string) ($variant['name'] ?? ''));

        return $name !== '' ? Str::limit($name, 255, '') : 'Variant '.($index + 1);
    }

    private function validatedRunDirectory(string $runDirectory): string
    {
        $real = realpath($runDirectory);
        if ($real === false || ! is_dir($real)) {
            throw new RuntimeException('Import run directory does not exist.');
        }
        $repository = str_replace('\\', '/', base_path());
        if (str_starts_with(str_replace('\\', '/', $real).'/', rtrim($repository, '/').'/')) {
            throw new RuntimeException('Import run directory must be outside the repository.');
        }

        return $real;
    }
}
