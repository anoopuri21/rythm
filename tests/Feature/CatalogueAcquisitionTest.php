<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\CatalogueAcquisitionService;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class CatalogueAcquisitionTest extends TestCase
{
    private string $outputRoot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->outputRoot = sys_get_temp_dir().'/rythme-catalogue-test-'.bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        if (is_dir($this->outputRoot)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->outputRoot, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST,
            );
            foreach ($files as $file) {
                $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
            }
            rmdir($this->outputRoot);
        }
        parent::tearDown();
    }

    public function test_it_acquires_normalizes_and_locally_stages_a_bounded_product(): void
    {
        Http::fake([
            'www.bajaao.com/collections/acoustic-guitars/products.json*' => Http::response([
                'products' => [['handle' => 'test-guitar']],
            ]),
            'www.bajaao.com/products/test-guitar.json' => Http::response([
                'product' => $this->sourceProduct(),
            ]),
            'cdn.shopify.com/*' => Http::response(
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true),
                200,
                ['Content-Type' => 'image/png'],
            ),
        ]);

        $report = app(CatalogueAcquisitionService::class)->acquire(
            'acoustic-guitars', 1, 1000, 1, 1024, 4096, $this->outputRoot,
        );

        $this->assertSame(1, $report['products_completed']);
        $this->assertSame(1, $report['images_downloaded']);
        $this->assertSame(3, $report['request_count']);
        $this->assertFileExists($report['output_directory'].'/test-guitar.json');
        $this->assertFileExists($report['output_directory'].'/report.json');

        $product = json_decode(file_get_contents($report['output_directory'].'/test-guitar.json'), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('Test Guitar', $product['name']);
        $this->assertSame('4999.00', $product['price']);
        $this->assertSame('5999.00', $product['compare_at_price']);
        $this->assertStringNotContainsString('<script', $product['description_text']);
        $this->assertStringNotContainsString('alert(1)', $product['description_text']);
        $this->assertSame(1, $product['media'][0]['width']);
        $this->assertSame(1, $product['media'][0]['height']);
        $this->assertStringStartsWith('media/test-guitar-01-', $product['media'][0]['file']);

        Http::assertSentCount(3);

        Http::fake([
            'www.bajaao.com/collections/acoustic-guitars/products.json*' => Http::response([
                'products' => [['handle' => 'test-guitar']],
            ]),
        ]);
        Http::preventStrayRequests();

        $resumed = app(CatalogueAcquisitionService::class)->acquire(
            'acoustic-guitars', 1, 1000, 1, 1024, 4096, $this->outputRoot, resumeRunId: $report['run_id'],
        );

        $this->assertSame(1, $resumed['resumed_products']);
        $this->assertSame(1, $resumed['request_count']);
        $this->assertSame(1, $resumed['images_downloaded']);
    }

    public function test_it_rejects_unbounded_pilot_limits_and_unapproved_source_hosts(): void
    {
        $service = app(CatalogueAcquisitionService::class);

        $this->expectException(RuntimeException::class);
        $service->acquire('acoustic-guitars', 11, 1000, 1, 1024, 4096, $this->outputRoot);
    }

    public function test_command_rejects_repository_output(): void
    {
        $this->artisan('catalogue:acquire-pilot', ['--output' => base_path('storage/catalogue')])
            ->expectsOutputToContain('outside the repository')
            ->assertFailed();
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
            'body_html' => '<p>Safe description.</p><script>alert(1)</script>',
            'published_at' => '2026-01-01T00:00:00+05:30',
            'updated_at' => '2026-01-02T00:00:00+05:30',
            'tags' => ['Acoustic'],
            'options' => [['name' => 'Colour', 'values' => ['Natural']]],
            'variants' => [[
                'id' => 456,
                'title' => 'Natural',
                'sku' => 'TEST-1',
                'price' => '4999.00',
                'compare_at_price' => '5999.00',
                'available' => true,
                'option1' => 'Natural',
                'option2' => null,
                'option3' => null,
            ]],
            'images' => [['src' => 'https://cdn.shopify.com/test.jpg']],
        ];
    }
}
