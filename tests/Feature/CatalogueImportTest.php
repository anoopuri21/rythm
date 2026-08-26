<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductImportSource;
use App\Models\User;
use App\Services\CatalogueAcquisitionService;
use App\Services\CatalogueImportService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CatalogueImportTest extends TestCase
{
    use RefreshDatabase;

    private string $outputRoot;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->outputRoot = sys_get_temp_dir().'/rythme-import-test-'.bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        if (is_dir($this->outputRoot)) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->outputRoot, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $file) {
                $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
            }
            rmdir($this->outputRoot);
        }
        parent::tearDown();
    }

    public function test_dry_run_writes_nothing_and_commit_creates_inactive_locally_managed_product(): void
    {
        $directory = $this->acquiredRun();
        $service = app(CatalogueImportService::class);

        $dry = $service->import($directory);
        $this->assertSame(1, $dry['validated']);
        $this->assertSame(0, $dry['created']);
        $this->assertDatabaseCount('products', 0);

        $committed = $service->import($directory, true);
        $this->assertSame(1, $committed['created']);
        $this->assertSame(1, $committed['media_attached']);

        $product = Product::with(['variants', 'media'])->sole();
        $this->assertFalse($product->is_active);
        $this->assertSame(0, $product->stock);
        $this->assertCount(2, $product->variants);
        $this->assertTrue($product->variants->every(fn ($variant): bool => ! $variant->is_active && $variant->stock === 0));
        $this->assertCount(1, $product->media);
        $this->assertFalse($product->media->first()->getCustomProperty('commercial_use_approved'));
        $this->assertStringNotContainsString('bajaao.com', $product->media->first()->getUrl());
        $this->assertDatabaseHas('product_import_sources', ['product_id' => $product->id, 'source' => 'bajaao']);
        $this->get('/product/test-guitar')->assertNotFound();
        $this->actingAs(User::factory()->admin()->create())
            ->get('/admin/products')
            ->assertOk()
            ->assertSee('Test Guitar');

        $rerun = $service->import($directory, true);
        $this->assertSame(1, $rerun['skipped_unchanged']);
        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('product_variants', 2);
        $this->assertDatabaseCount('media', 1);
    }

    public function test_changed_source_is_a_conflict_and_never_overwrites_existing_product(): void
    {
        $directory = $this->acquiredRun();
        $service = app(CatalogueImportService::class);
        $service->import($directory, true);

        $file = collect(glob($directory.'/*.json'))->first(fn (string $path): bool => basename($path) !== 'report.json');
        $payload = json_decode((string) file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
        $payload['price'] = '1.00';
        file_put_contents($file, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

        $result = $service->import($directory, true);
        $this->assertSame(1, $result['conflicts']);
        $this->assertSame('4999.00', Product::sole()->price);
    }

    public function test_import_provenance_identity_is_database_unique(): void
    {
        $directory = $this->acquiredRun();
        app(CatalogueImportService::class)->import($directory, true);
        $source = ProductImportSource::sole();

        $this->expectException(QueryException::class);
        ProductImportSource::create([
            'product_id' => $source->product_id,
            'source' => $source->source,
            'source_product_id' => $source->source_product_id,
            'source_url' => $source->source_url,
            'payload_hash' => str_repeat('a', 64),
            'imported_at' => now(),
        ]);
    }

    private function acquiredRun(): string
    {
        Http::fake([
            'www.bajaao.com/collections/acoustic-guitars/products.json*' => Http::response(['products' => [['handle' => 'test-guitar']]]),
            'www.bajaao.com/products/test-guitar.json' => Http::response(['product' => $this->sourceProduct()]),
            'cdn.shopify.com/*' => Http::response(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true), 200, ['Content-Type' => 'image/png']),
        ]);

        $report = app(CatalogueAcquisitionService::class)->acquire('acoustic-guitars', 1, 1000, 1, 1024, 4096, $this->outputRoot);

        return $report['output_directory'];
    }

    /** @return array<string, mixed> */
    private function sourceProduct(): array
    {
        return [
            'id' => 123,
            'title' => 'Test Guitar',
            'handle' => 'test-guitar',
            'vendor' => 'Test Brand',
            'product_type' => 'Acoustic Guitars',
            'body_html' => '<p>Safe description.</p>',
            'published_at' => '2026-01-01T00:00:00+05:30',
            'updated_at' => '2026-01-02T00:00:00+05:30',
            'tags' => ['Acoustic'],
            'options' => [['name' => 'Colour', 'values' => ['Natural', 'Black']]],
            'variants' => [
                ['id' => 456, 'title' => 'Natural', 'sku' => 'TEST-1', 'price' => '4999.00', 'compare_at_price' => '5999.00', 'available' => true, 'option1' => 'Natural'],
                ['id' => 457, 'title' => 'Black', 'sku' => 'TEST-2', 'price' => '5499.00', 'compare_at_price' => '5999.00', 'available' => true, 'option1' => 'Black'],
            ],
            'images' => [['src' => 'https://cdn.shopify.com/test.png']],
        ];
    }
}
