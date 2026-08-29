<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Observers\HomepageDataObserver;
use App\Services\CategoryService;
use App\Services\HomepageDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HomepageDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget(HomepageDataObserver::CACHE_KEY);
    }

    public function test_new_arrivals_are_latest_active_products_and_bounded(): void
    {
        [$category, $brand] = $this->catalogueParents();
        Product::factory()->count(12)->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'is_active' => true,
        ]);
        $inactive = Product::factory()->inactive()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
        ]);

        $products = app(HomepageDataService::class)->all()['newArrivals'];

        $this->assertCount(10, $products);
        $this->assertTrue($products->every(fn (Product $product): bool => $product->is_active));
        $this->assertFalse($products->contains('id', $inactive->id));
        $this->assertSame($products->pluck('id')->sortDesc()->values()->all(), $products->pluck('id')->all());
    }

    public function test_trending_flag_persists_flushes_cache_and_renders_section(): void
    {
        [$category, $brand] = $this->catalogueParents();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'is_active' => true,
            'is_trending' => false,
        ]);
        app(HomepageDataService::class)->all();

        $product->update(['is_trending' => true, 'featured_rank' => 3]);

        $this->assertTrue($product->fresh()->is_trending);
        $this->assertSame(3, $product->fresh()->featured_rank);
        $this->assertTrue(app(HomepageDataService::class)->all()['trending']->contains('id', $product->id));
        $this->get('/')->assertOk()->assertSee('Trending Products')->assertSee($product->name);
    }

    public function test_best_deals_include_only_active_truthful_discounts(): void
    {
        [$category, $brand] = $this->catalogueParents();
        $deal = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'is_active' => true,
            'price' => 800,
            'compare_at_price' => 1000,
        ]);
        $notDiscounted = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'is_active' => true,
            'price' => 1000,
            'compare_at_price' => 1000,
        ]);
        $inactive = Product::factory()->inactive()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'price' => 500,
            'compare_at_price' => 1000,
        ]);

        $deals = app(HomepageDataService::class)->all()['bestDeals'];

        $this->assertTrue($deals->contains('id', $deal->id));
        $this->assertFalse($deals->contains('id', $notDiscounted->id));
        $this->assertFalse($deals->contains('id', $inactive->id));
        $this->get('/')->assertOk()->assertSee('Best Deals')->assertSee($deal->name);
    }

    public function test_popular_categories_discover_new_active_catalogue_groups(): void
    {
        [$category, $brand] = $this->catalogueParents('Microphones', 'microphones');
        Product::factory()->count(2)->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'is_active' => true,
        ]);

        $categories = app(HomepageDataService::class)->all()['popularCategories'];
        $microphones = $categories->firstWhere('slug', 'microphones');

        $this->assertNotNull($microphones);
        $this->assertSame(2, $microphones['count']);
        $this->assertArrayHasKey('image', $microphones);
    }

    public function test_category_navigation_returns_empty_when_configured_database_is_unmigrated(): void
    {
        Schema::dropIfExists('categories');
        Cache::forget('categories.tree');

        $this->assertSame([], app(CategoryService::class)->tree());
    }

    /** @return array{Category, Brand} */
    private function catalogueParents(string $name = 'Guitars', string $slug = 'guitars'): array
    {
        return [
            Category::factory()->create(['name' => $name, 'slug' => $slug, 'is_active' => true]),
            Brand::factory()->create(['is_active' => true]),
        ];
    }
}
