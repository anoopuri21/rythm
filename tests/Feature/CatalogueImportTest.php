<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductImportSource;
use App\Models\User;
use App\Services\CatalogueAcquisitionService;
use App\Services\CatalogueImportService;
use App\Services\ImportedProductActivationService;
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
        $this->assertDatabaseHas('product_import_sources', [
            'product_id' => $product->id,
            'source' => 'bajaao',
            'publication_review_required' => false,
            'publication_reviewed_at' => null,
        ]);
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

    public function test_expansion_batch_import_is_dry_run_first_and_commit_stays_inactive(): void
    {
        $run = $this->acquiredRun();
        $batch = $this->outputRoot.'/expansion-batch';
        mkdir($batch, 0700);
        $group = $batch.'/acoustic-guitars';
        rename($run, $group);
        file_put_contents($batch.'/batch-report.json', json_encode([
            'complete' => true,
            'products_failed' => 0,
            'image_failures' => 0,
            'products_without_media' => 0,
            'groups' => [['collection' => 'acoustic-guitars', 'output_directory' => $group]],
        ], JSON_THROW_ON_ERROR));

        config(['catalogue.pilot.output_root' => $this->outputRoot]);
        $this->artisan('catalogue:import-expansion', ['batch' => 'expansion-batch'])->assertSuccessful();
        $this->assertDatabaseCount('products', 0);

        $this->artisan('catalogue:import-expansion', ['batch' => 'expansion-batch', '--commit' => true])->assertSuccessful();
        $this->assertDatabaseCount('products', 1);
        $this->assertFalse(Product::sole()->is_active);
        $this->assertSame(0, Product::sole()->stock);
    }

    public function test_explicit_allow_conflicts_imports_nothing_over_existing_changed_products(): void
    {
        $run = $this->acquiredRun();
        app(CatalogueImportService::class)->import($run, true);
        $originalPrice = Product::sole()->price;

        $file = collect(glob($run.'/*.json'))->first(fn (string $path): bool => basename($path) !== 'report.json');
        $payload = json_decode((string) file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
        $payload['price'] = '1.00';
        file_put_contents($file, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

        $batch = $this->outputRoot.'/conflict-batch';
        mkdir($batch, 0700);
        $group = $batch.'/acoustic-guitars';
        rename($run, $group);
        file_put_contents($batch.'/batch-report.json', json_encode([
            'complete' => true,
            'products_failed' => 0,
            'image_failures' => 0,
            'products_without_media' => 0,
            'groups' => [['collection' => 'acoustic-guitars', 'output_directory' => $group]],
        ], JSON_THROW_ON_ERROR));

        config(['catalogue.pilot.output_root' => $this->outputRoot]);
        $this->artisan('catalogue:import-expansion', [
            'batch' => 'conflict-batch',
            '--commit' => true,
            '--allow-conflicts' => true,
        ])->expectsOutputToContain('safely held without overwrite')->assertSuccessful();

        $this->assertDatabaseCount('products', 1);
        $this->assertSame($originalPrice, Product::sole()->price);
        $report = json_decode(file_get_contents($batch.'/import-report.json'), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame(1, $report['conflicts']);
        $this->assertSame(0, $report['created']);
    }

    public function test_imported_product_requires_review_real_stock_and_approved_local_media_before_activation(): void
    {
        $directory = $this->acquiredRun();
        app(CatalogueImportService::class)->import($directory, true);
        $product = Product::sole();
        $actor = User::factory()->create();
        $actor->forceFill(['role' => User::ROLE_CATALOGUE_MANAGER])->save();
        $this->actingAs($actor);

        try {
            $product->update(['is_active' => true]);
            $this->fail('Direct activation should be blocked.');
        } catch (\DomainException $exception) {
            $this->assertStringContainsString('reviewed content', $exception->getMessage());
        }

        $product->refresh()->update(['stock' => 5]);
        $activated = app(ImportedProductActivationService::class)
            ->approveAndActivate($product->refresh(), $actor, 'Content, local media, price and physical stock verified.');

        $this->assertTrue($activated->is_active);
        $this->assertTrue($activated->category()->sole()->is_active);
        $this->assertTrue($activated->brand()->sole()->is_active);
        $source = $activated->importSource()->sole();
        $this->assertNotNull($source->publication_reviewed_at);
        $this->assertSame($actor->id, $source->publication_reviewed_by);
        $this->assertNotNull($source->commercial_use_approved_at);
        $this->assertTrue((bool) $activated->getFirstMedia('gallery')->getCustomProperty('commercial_use_approved'));
        $this->assertDatabaseHas('admin_audit_logs', [
            'actor_id' => $actor->id,
            'action' => 'catalogue.imported_product_activated',
            'reason' => 'Content, local media, price and physical stock verified.',
        ]);
        $this->get('/product/test-guitar')->assertOk();
    }

    public function test_review_and_category_metadata_do_not_create_false_source_change_conflicts(): void
    {
        $directory = $this->acquiredRun();
        $service = app(CatalogueImportService::class);
        $service->import($directory, true);

        $file = collect(glob($directory.'/*.json'))->first(fn (string $path): bool => basename($path) !== 'report.json');
        $payload = json_decode((string) file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
        $payload['publication_review'] = ['required' => true, 'reasons' => ['new review rule']];
        $payload['target_category_name'] = 'Mapped Acoustic Guitars';
        $payload['target_category_slug'] = 'mapped-acoustic-guitars';
        file_put_contents($file, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

        $result = $service->import($directory, true);
        $this->assertSame(1, $result['skipped_unchanged']);
        $this->assertSame(0, $result['conflicts']);
        $this->assertDatabaseCount('products', 1);
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
