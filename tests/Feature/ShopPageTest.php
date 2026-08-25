<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\ShopIndex;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariant;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShopPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_shop_page_renders_products_and_header(): void
    {
        $this->get('/shop')
            ->assertOk()
            ->assertViewIs('shop.index')
            ->assertSee('Shop instruments')
            ->assertSee('Yamaha F310 Acoustic Guitar')
            ->assertSee('₹8,499');
    }

    public function test_shop_renders_marketplace_shortcuts_searchable_facets_and_truthful_sort(): void
    {
        $this->get('/shop')
            ->assertOk()
            ->assertSee('Shop popular categories')
            ->assertSee('shop-shortcuts', escape: false)
            ->assertSee('Search categories')
            ->assertSee('Search brands')
            ->assertSee('value="featured"', escape: false)
            ->assertDontSee('Popularity');
    }

    public function test_shop_seo_policy_distinguishes_base_pagination_and_filtered_queries(): void
    {
        $this->get('/shop')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('shop.index').'">', escape: false)
            ->assertSee('<meta name="robots" content="index, follow">', escape: false);

        $this->get('/shop?page=2')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('shop.index', ['page' => 2]).'">', escape: false)
            ->assertSee('<meta name="robots" content="index, follow">', escape: false);

        $this->get('/shop?q=guitar')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('shop.index').'">', escape: false)
            ->assertSee('<meta name="robots" content="noindex, follow">', escape: false);
    }

    public function test_child_category_filter_shows_only_that_category(): void
    {
        $this->get('/shop?category=acoustic-guitars')
            ->assertOk()
            ->assertSee('Yamaha F310 Acoustic Guitar')
            ->assertSee('Fender CD-60S Dreadnought')
            ->assertDontSee('Squier Affinity Stratocaster');
    }

    public function test_parent_category_includes_children(): void
    {
        $this->get('/shop?category=guitars')
            ->assertOk()
            ->assertSee('Yamaha F310 Acoustic Guitar')   // acoustic child
            ->assertSee('Squier Affinity Stratocaster')  // electric child
            ->assertSee('Ibanez GRX70QA');               // electric child
    }

    public function test_brand_filter_restricts_results(): void
    {
        $this->get('/shop?brand[]=fender')
            ->assertOk()
            ->assertSee('Fender CD-60S Dreadnought')
            ->assertSee('Fender Mustang LT25')
            ->assertSee('Fender 351 Shape Picks')
            ->assertDontSee('Yamaha F310');
    }

    public function test_price_filter_restricts_results(): void
    {
        $this->get('/shop?min=20000&max=40000')
            ->assertOk()
            ->assertSee('Squier Affinity Stratocaster')  // ₹34,999
            ->assertDontSee('Fender 351 Shape Picks');   // ₹399
    }

    public function test_in_stock_filter_excludes_out_of_stock(): void
    {
        $outOfStock = Product::factory()->create([
            'category_id' => Category::where('slug', 'picks-capos')->firstOrFail()->id,
            'brand_id' => Brand::where('slug', 'fender')->firstOrFail()->id,
            'name' => 'Out Of Stock Special',
            'slug' => 'out-of-stock-special',
            'sku' => 'RYM-OOS-001',
            'price' => 999,
            'stock' => 0,
        ]);

        $this->get('/shop?instock=1')
            ->assertOk()
            ->assertDontSee('Out Of Stock Special');

        $this->assertDatabaseHas('products', ['id' => $outOfStock->id, 'stock' => 0]);
    }

    public function test_search_query_filters_by_name(): void
    {
        $this->get('/shop?q=ukulele')
            ->assertOk()
            ->assertSee('Kala KA-15S')
            ->assertDontSee('Yamaha F310');
    }

    public function test_approved_average_rating_filter_and_summary_are_truthful(): void
    {
        $highRated = Product::where('slug', 'yamaha-f310-acoustic-guitar')->firstOrFail();
        $lowRated = Product::where('slug', 'squier-affinity-stratocaster-hss')->firstOrFail();

        Review::create(['product_id' => $highRated->id, 'rating' => 5, 'is_approved' => true]);
        Review::create(['product_id' => $highRated->id, 'rating' => 4, 'is_approved' => true]);
        Review::create(['product_id' => $lowRated->id, 'rating' => 2, 'is_approved' => true]);
        Review::create(['product_id' => $lowRated->id, 'rating' => 5, 'is_approved' => false]);

        Livewire::test(ShopIndex::class)
            ->call('setMinRating', 4)
            ->assertSet('minRating', 4)
            ->assertSee('Yamaha F310 Acoustic Guitar')
            ->assertSee('4.5')
            ->assertDontSee('Squier Affinity Stratocaster');
    }

    public function test_category_aware_attribute_facet_filters_normalized_assignments(): void
    {
        $category = Category::where('slug', 'acoustic-guitars')->firstOrFail();
        $yamaha = Product::where('slug', 'yamaha-f310-acoustic-guitar')->firstOrFail();
        $fender = Product::where('slug', 'fender-cd-60s-dreadnought-acoustic-guitar')->firstOrFail();
        $attribute = ProductAttribute::create([
            'name' => 'Top wood',
            'slug' => 'top-wood',
            'type' => 'select',
            'is_filterable' => true,
            'is_active' => true,
        ]);
        $spruce = ProductAttributeValue::create([
            'product_attribute_id' => $attribute->id,
            'value' => 'Spruce',
            'slug' => 'spruce',
        ]);
        $mahogany = ProductAttributeValue::create([
            'product_attribute_id' => $attribute->id,
            'value' => 'Mahogany',
            'slug' => 'mahogany',
        ]);
        $cedar = ProductAttributeValue::create([
            'product_attribute_id' => $attribute->id,
            'value' => 'Cedar',
            'slug' => 'cedar',
        ]);
        $fenderVariant = ProductVariant::create([
            'product_id' => $fender->id,
            'name' => 'Cedar QA',
            'sku' => 'TEST-FENDER-CEDAR-QA',
            'stock' => 2,
            'is_active' => true,
        ]);

        $attribute->categories()->attach($category->id, ['is_filterable' => true]);
        $spruce->products()->attach($yamaha->id);
        $mahogany->products()->attach($fender->id);
        $cedar->variants()->attach($fenderVariant->id);

        Livewire::test(ShopIndex::class)
            ->call('setCategory', 'acoustic-guitars')
            ->assertSee('Top wood')
            ->assertSee('Spruce')
            ->call('toggleAttribute', 'top-wood', 'spruce')
            ->assertSet('selectedAttributes', ['top-wood' => ['spruce']])
            ->assertSee('Yamaha F310 Acoustic Guitar')
            ->assertDontSee('Fender CD-60S Dreadnought');

        Livewire::test(ShopIndex::class)
            ->call('setCategory', 'acoustic-guitars')
            ->assertSee('Cedar')
            ->call('toggleAttribute', 'top-wood', 'cedar')
            ->assertSee('Fender CD-60S Dreadnought')
            ->assertDontSee('Yamaha F310 Acoustic Guitar');
    }

    public function test_livewire_sort_price_ascending(): void
    {
        Livewire::test(ShopIndex::class, ['sort' => 'price-asc'])
            ->assertOk()
            ->assertSeeInOrder([
                'Fender 351 Shape Picks',   // ₹399
                'Ernie Ball Super Slinky',  // ₹649
                'Planet Waves Classic',     // ₹999
            ]);
    }

    public function test_livewire_category_and_clear(): void
    {
        Livewire::test(ShopIndex::class)
            ->call('setCategory', 'digital-pianos')
            ->assertSet('category', 'digital-pianos')
            ->assertSee('Roland FP-30X')
            ->assertDontSee('Kala KA-15S')
            ->call('clearFilters')
            ->assertSet('category', null)
            ->assertSee('Yamaha F310 Acoustic Guitar');
    }

    public function test_livewire_toggle_brand(): void
    {
        Livewire::test(ShopIndex::class)
            ->call('toggleBrand', 'fender')
            ->assertSet('selectedBrands', ['fender'])
            ->assertSee('Fender CD-60S')
            ->assertDontSee('Yamaha F310')
            ->call('toggleBrand', 'fender')
            ->assertSet('selectedBrands', []);
    }

    public function test_livewire_price_and_stock_toggle(): void
    {
        Livewire::test(ShopIndex::class)
            ->set('minPrice', 50000)
            ->assertSee('Roland FP-30X')     // ₹61,999
            ->assertDontSee('Kala KA-15S');  // ₹4,499
    }

    public function test_navbar_has_category_drawer_with_db_categories(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('All Categories', escape: false)
            ->assertSee('/shop?category=guitars', escape: false)
            ->assertSee('/shop?category=acoustic-guitars', escape: false)
            ->assertSee('Shop by Category', escape: false);
    }

    public function test_shop_empty_state_shows_clear_button(): void
    {
        Product::query()->delete();

        Livewire::test(ShopIndex::class)
            ->assertSee('Nothing matches those filters')
            ->assertSee('Clear all filters');
    }
}
