<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\ShopIndex;
use App\Models\Product;
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
            'category_id' => \App\Models\Category::where('slug', 'picks-capos')->firstOrFail()->id,
            'brand_id' => \App\Models\Brand::where('slug', 'fender')->firstOrFail()->id,
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
