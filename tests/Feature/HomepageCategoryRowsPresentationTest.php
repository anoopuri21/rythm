<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\HomepageCategoryRow;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageCategoryRowsPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_configured_category_row_renders_real_products_and_accessible_shop_link(): void
    {
        $category = Category::factory()->create([
            'name' => 'Microphones',
            'slug' => 'microphones',
            'is_active' => true,
        ]);
        HomepageCategoryRow::query()->create([
            'category_id' => $category->id,
            'title' => 'Microphones for every stage',
            'product_limit' => 4,
            'is_active' => true,
        ]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Rythme Stage Microphone',
            'slug' => 'rythme-stage-microphone',
            'is_active' => true,
            'stock' => 0,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('id="category-product-rows"', false)
            ->assertSee('aria-labelledby="category-row-microphones"', false)
            ->assertSee('Microphones for every stage')
            ->assertSee($product->name)
            ->assertSee('/shop?category=microphones', false)
            ->assertSee('aria-label="Browse all Microphones products"', false);
    }

    public function test_empty_or_inactive_category_rows_add_no_public_section(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        HomepageCategoryRow::query()->create([
            'category_id' => $category->id,
            'product_limit' => 4,
            'is_active' => true,
        ]);
        Product::factory()->inactive()->create(['category_id' => $category->id]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('id="category-product-rows"', false);
    }
}
