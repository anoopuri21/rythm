<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\SkuGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * SKU is optional in the admin product form; blank values are replaced by
 * a generated, collision-free code (products.sku / product_variants.sku
 * are NOT NULL + UNIQUE).
 */
class SkuGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_generated_sku_matches_the_house_format(): void
    {
        $this->assertMatchesRegularExpression('/^RYM-[A-Z0-9]{8}$/', SkuGenerator::generate());
    }

    public function test_generated_skus_are_unique_across_products_and_variants(): void
    {
        $product = Product::factory()->create();
        ProductVariant::factory()->create(['product_id' => $product->id]);

        foreach (range(1, 5) as $i) {
            $sku = SkuGenerator::generate();
            $this->assertFalse(SkuGenerator::exists($sku));

            // Consume the SKU; the next generated one must differ.
            Product::factory()->create(['sku' => $sku]);
        }
    }

    public function test_generated_sku_passes_database_unique_constraints(): void
    {
        $product = Product::factory()->create(['sku' => SkuGenerator::generate()]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => SkuGenerator::generate(),
        ]);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'sku' => $product->sku]);
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'sku' => $variant->sku]);
    }

    public function test_soft_deleted_product_skus_stay_reserved(): void
    {
        $product = Product::factory()->create(['sku' => 'RYM-SOFTDELT']);
        $product->delete();

        $this->assertTrue(SkuGenerator::exists('RYM-SOFTDELT'));
    }

    public function test_fill_if_blank_generates_and_preserves_explicit_values(): void
    {
        $generated = SkuGenerator::fillIfBlank(['sku' => '   ']);
        $this->assertMatchesRegularExpression('/^RYM-[A-Z0-9]{8}$/', $generated['sku']);

        $missing = SkuGenerator::fillIfBlank([]);
        $this->assertMatchesRegularExpression('/^RYM-[A-Z0-9]{8}$/', $missing['sku']);

        $kept = SkuGenerator::fillIfBlank(['sku' => '  MY-CODE ']);
        $this->assertSame('MY-CODE', $kept['sku']);
    }
}
