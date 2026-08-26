<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\CatalogueExpansionManifestService;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

final class CatalogueExpansionTest extends TestCase
{
    private string $root;

    private string $manifest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = sys_get_temp_dir().'/rythme-expansion-test-'.bin2hex(random_bytes(4));
        mkdir($this->root, 0700, true);
        $this->manifest = $this->root.'/manifest.json';
        file_put_contents($this->manifest, json_encode($this->validManifest(), JSON_THROW_ON_ERROR));
    }

    protected function tearDown(): void
    {
        if (is_dir($this->root)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->root, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST,
            );
            foreach ($files as $file) {
                $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
            }
            rmdir($this->root);
        }

        parent::tearDown();
    }

    public function test_manifest_batch_acquires_only_selected_handles_and_flags_publication_review(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'www.bajaao.com/collections/group-one/products.json*' => Http::response([
                'products' => [['handle' => 'decoy'], ['handle' => 'selected-one']],
            ]),
            'www.bajaao.com/products/selected-one.json' => Http::response([
                'product' => $this->sourceProduct('1', 'selected-one', 'Includes a free ebook and warranty.'),
            ]),
            'www.bajaao.com/collections/group-two/products.json*' => Http::response([
                'products' => [['handle' => 'selected-two']],
            ]),
            'www.bajaao.com/products/selected-two.json' => Http::response([
                'product' => $this->sourceProduct('2', 'selected-two', 'Plain product specification.'),
            ]),
        ]);

        $this->artisan('catalogue:acquire-expansion', [
            'manifest' => $this->manifest,
            '--group' => ['group-one', 'group-two'],
            '--batch' => 'test-batch',
            '--output' => $this->root.'/output',
            '--no-images' => true,
        ])->assertSuccessful();

        $batch = json_decode(file_get_contents($this->root.'/output/test-batch/batch-report.json'), true, flags: JSON_THROW_ON_ERROR);
        $this->assertTrue($batch['complete']);
        $this->assertSame(2, $batch['products_completed']);
        $this->assertSame(1, $batch['publication_review_required']);
        $this->assertFileExists($this->root.'/output/test-batch/group-one/selected-one.json');
        $this->assertFileDoesNotExist($this->root.'/output/test-batch/group-one/decoy.json');

        $product = json_decode(file_get_contents($this->root.'/output/test-batch/group-one/selected-one.json'), true, flags: JSON_THROW_ON_ERROR);
        $this->assertTrue($product['publication_review']['required']);
        $this->assertContains('warranty or guarantee claim', $product['publication_review']['reasons']);
        $this->assertContains('free item, lesson or trial claim', $product['publication_review']['reasons']);
        Http::assertSentCount(4);
    }

    public function test_manifest_source_identity_mismatch_stops_the_batch(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'www.bajaao.com/collections/group-one/products.json*' => Http::response([
                'products' => [['handle' => 'selected-one']],
            ]),
            'www.bajaao.com/products/selected-one.json' => Http::response([
                'product' => $this->sourceProduct('999', 'selected-one', 'Changed identity.'),
            ]),
        ]);

        $this->artisan('catalogue:acquire-expansion', [
            'manifest' => $this->manifest,
            '--group' => ['group-one'],
            '--batch' => 'identity-mismatch',
            '--output' => $this->root.'/output',
            '--no-images' => true,
        ])->expectsOutputToContain('incomplete')->assertFailed();

        $report = json_decode(file_get_contents($this->root.'/output/identity-mismatch/group-one/report.json'), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame(0, $report['products_completed']);
        $this->assertSame(1, $report['products_failed']);
        $this->assertStringContainsString('identity changed', $report['errors'][0]['error']);
    }

    public function test_manifest_rejects_duplicate_handles_and_command_rejects_repository_output(): void
    {
        $manifest = $this->validManifest();
        $manifest['groups'][1]['products'][0]['handle'] = 'selected-one';
        file_put_contents($this->manifest, json_encode($manifest, JSON_THROW_ON_ERROR));

        $this->expectException(RuntimeException::class);
        app(CatalogueExpansionManifestService::class)->load($this->manifest);
    }

    public function test_command_requires_a_bounded_group_selection(): void
    {
        $this->artisan('catalogue:acquire-expansion', [
            'manifest' => $this->manifest,
            '--output' => $this->root.'/output',
        ])->expectsOutputToContain('Select one or two manifest groups')->assertFailed();

        $this->artisan('catalogue:acquire-expansion', [
            'manifest' => $this->manifest,
            '--group' => ['group-one'],
            '--output' => base_path('storage/catalogue-expansion'),
        ])->expectsOutputToContain('outside the repository')->assertFailed();
    }

    /** @return array<string, mixed> */
    private function validManifest(): array
    {
        return [
            'schema_version' => 1,
            'source_host' => 'www.bajaao.com',
            'target_total' => 2,
            'groups' => [
                [
                    'name' => 'One',
                    'category_slug' => 'one',
                    'source_collection' => 'group-one',
                    'target' => 1,
                    'products' => [['source_product_id' => '1', 'handle' => 'selected-one', 'title' => 'Selected One']],
                ],
                [
                    'name' => 'Two',
                    'category_slug' => 'two',
                    'source_collection' => 'group-two',
                    'target' => 1,
                    'products' => [['source_product_id' => '2', 'handle' => 'selected-two', 'title' => 'Selected Two']],
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function sourceProduct(string $id, string $handle, string $description): array
    {
        return [
            'id' => $id,
            'title' => str($handle)->replace('-', ' ')->title()->toString(),
            'handle' => $handle,
            'vendor' => 'Test Brand',
            'product_type' => 'Test Type',
            'body_html' => '<p>'.$description.'</p>',
            'tags' => [],
            'options' => [],
            'variants' => [[
                'id' => $id.'01',
                'title' => 'Default',
                'sku' => 'SKU-'.$id,
                'price' => '1000.00',
                'compare_at_price' => null,
                'available' => true,
            ]],
            'images' => [],
        ];
    }
}
