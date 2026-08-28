<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\HomepageCategoryRow;
use App\Models\Product;
use App\Observers\HomepageDataObserver;
use App\Services\HomepageDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class HomepageCategoryRowsQueryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget(HomepageDataObserver::CACHE_KEY);
    }

    public function test_only_four_configured_rows_are_returned_in_admin_order(): void
    {
        foreach ([50, 10, 40, 20, 30] as $order) {
            $category = Category::factory()->create(['name' => "Category {$order}", 'is_active' => true]);
            Product::factory()->create(['category_id' => $category->id, 'is_active' => true]);
            HomepageCategoryRow::query()->create([
                'category_id' => $category->id,
                'sort_order' => $order,
                'product_limit' => 4,
                'is_active' => true,
            ]);
        }

        $rows = app(HomepageDataService::class)->all()['categoryRows'];

        $this->assertCount(4, $rows);
        $this->assertSame([10, 20, 30, 40], $rows->pluck('row.sort_order')->all());
    }

    public function test_inactive_rows_categories_and_categories_without_active_products_are_hidden(): void
    {
        $inactiveRowCategory = Category::factory()->create(['is_active' => true]);
        Product::factory()->create(['category_id' => $inactiveRowCategory->id]);
        HomepageCategoryRow::query()->create(['category_id' => $inactiveRowCategory->id, 'product_limit' => 4, 'is_active' => false]);

        $inactiveCategory = Category::factory()->create(['is_active' => false]);
        Product::factory()->create(['category_id' => $inactiveCategory->id]);
        HomepageCategoryRow::query()->create(['category_id' => $inactiveCategory->id, 'product_limit' => 4, 'is_active' => true]);

        $emptyCategory = Category::factory()->create(['is_active' => true]);
        Product::factory()->inactive()->create(['category_id' => $emptyCategory->id]);
        HomepageCategoryRow::query()->create(['category_id' => $emptyCategory->id, 'product_limit' => 4, 'is_active' => true]);

        $this->assertEmpty(app(HomepageDataService::class)->all()['categoryRows']);
    }

    public function test_products_are_active_eager_loaded_and_bounded_to_eight(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        HomepageCategoryRow::query()->create([
            'category_id' => $category->id,
            'product_limit' => 255,
            'is_active' => true,
        ]);
        $brand = Brand::factory()->create();
        Product::factory()->count(10)->create(['category_id' => $category->id, 'brand_id' => $brand->id, 'is_active' => true]);
        Product::factory()->inactive()->create(['category_id' => $category->id, 'brand_id' => $brand->id]);

        $products = app(HomepageDataService::class)->all()['categoryRows']->first()['products'];

        $this->assertCount(8, $products);
        $this->assertTrue($products->every(fn (Product $product): bool => $product->is_active));
        $this->assertTrue($products->every(fn (Product $product): bool => $product->relationLoaded('brand')
            && $product->relationLoaded('category')
            && $product->relationLoaded('media')));
    }

    public function test_configured_categories_lead_discovery_with_truthful_active_counts(): void
    {
        $fallback = Category::factory()->create(['name' => 'Acoustic Guitars', 'slug' => 'acoustic-guitars', 'is_active' => true]);
        Product::factory()->count(2)->create(['category_id' => $fallback->id, 'is_active' => true]);

        $configured = Category::factory()->create(['name' => 'Microphones', 'slug' => 'microphones', 'is_active' => true]);
        Product::factory()->count(3)->create(['category_id' => $configured->id, 'is_active' => true]);
        Product::factory()->inactive()->create(['category_id' => $configured->id]);
        HomepageCategoryRow::query()->create(['category_id' => $configured->id, 'product_limit' => 4, 'is_active' => true]);

        $categories = app(HomepageDataService::class)->all()['popularCategories'];

        $this->assertSame('microphones', $categories->first()['slug']);
        $this->assertSame(3, $categories->first()['count']);
        $this->assertSame('acoustic-guitars', $categories->get(1)['slug']);
    }
}
