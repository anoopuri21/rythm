<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\DTOs\ShopFilters;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ShopLargeCatalogueQualificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_shop_filters_sorting_pagination_and_query_bound_with_eighty_products(): void
    {
        $parent = Category::factory()->create([
            'name' => 'Qualification Instruments',
            'slug' => 'qualification-instruments',
            'is_active' => true,
        ]);
        $categories = collect([
            Category::factory()->create([
                'parent_id' => $parent->id,
                'name' => 'Qualification Guitars',
                'slug' => 'qualification-guitars',
                'is_active' => true,
            ]),
            Category::factory()->create([
                'parent_id' => $parent->id,
                'name' => 'Qualification Keyboards',
                'slug' => 'qualification-keyboards',
                'is_active' => true,
            ]),
        ]);
        $brands = collect([
            Brand::factory()->create(['name' => 'Qualification Alpha', 'slug' => 'qualification-alpha', 'is_active' => true]),
            Brand::factory()->create(['name' => 'Qualification Beta', 'slug' => 'qualification-beta', 'is_active' => true]),
        ]);

        foreach (range(1, 80) as $number) {
            Product::factory()->create([
                'category_id' => $categories[($number - 1) % 2]->id,
                'brand_id' => $brands[($number - 1) % 2]->id,
                'name' => sprintf('Qualification Product %02d', $number),
                'slug' => sprintf('qualification-product-%02d', $number),
                'sku' => sprintf('QUAL-%03d', $number),
                'price' => 1000 + ($number * 100),
                'stock' => $number % 5 === 0 ? 0 : 5,
                'is_active' => true,
                'is_featured' => false,
            ]);
        }

        Product::factory()->create([
            'category_id' => $categories[0]->id,
            'brand_id' => $brands[0]->id,
            'name' => 'Qualification Inactive Product',
            'slug' => 'qualification-inactive-product',
            'sku' => 'QUAL-INACTIVE',
            'is_active' => false,
        ]);

        $service = app(ProductQueryService::class);
        DB::flushQueryLog();
        DB::enableQueryLog();
        $page = $service->paginate($service->shopQuery(new ShopFilters(sort: 'price-asc')));
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertSame(80, $page->total());
        $this->assertSame(12, $page->perPage());
        $this->assertSame(7, $page->lastPage());
        $this->assertSame('Qualification Product 01', $page->items()[0]->name);
        $this->assertLessThanOrEqual(10, $queryCount, 'Shop pagination should retain a bounded query count.');

        $this->assertSame(80, $service->shopQuery(new ShopFilters(category: $parent->slug))->count());
        $this->assertSame(40, $service->shopQuery(new ShopFilters(category: $categories[0]->slug))->count());
        $this->assertSame(40, $service->shopQuery(new ShopFilters(brands: [$brands[0]->slug]))->count());
        $this->assertSame(64, $service->shopQuery(new ShopFilters(inStockOnly: true))->count());
        $this->assertSame(11, $service->shopQuery(new ShopFilters(minPrice: 2000, maxPrice: 3000))->count());
        $this->assertFalse($service->shopQuery(new ShopFilters(search: 'Inactive'))->exists());

        $descending = $service->shopQuery(new ShopFilters(sort: 'price-desc'))->firstOrFail();
        $this->assertSame('Qualification Product 80', $descending->name);
    }
}
