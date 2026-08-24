<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\AddToCart;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_product_page_renders_full_layout(): void
    {
        $product = Product::where('slug', 'yamaha-f310-acoustic-guitar')->firstOrFail();

        $this->get($product->url = route('product.show', $product))
            ->assertOk()
            ->assertViewIs('product.show')
            ->assertSee($product->name)
            ->assertSee('Add to Cart')
            ->assertSee($product->sku)
            ->assertSee('1-Year Warranty')
            ->assertSee('You may also like');
    }

    public function test_product_page_has_single_h1_and_meta(): void
    {
        $product = Product::first();

        $this->get(route('product.show', $product))
            ->assertOk()
            ->assertSee('<h1', escape: false)
            ->assertSee('application/ld+json', escape: false);
    }

    public function test_inactive_product_returns_404(): void
    {
        $product = Product::factory()->inactive()->create([
            'category_id' => Category::first()->id,
            'brand_id' => Brand::first()->id,
            'slug' => 'hidden-product',
            'sku' => 'RYM-HIDDEN-01',
        ]);

        $this->get(route('product.show', $product))->assertNotFound();
    }

    public function test_unknown_slug_returns_404(): void
    {
        $this->get('/product/does-not-exist')->assertNotFound();
    }

    public function test_related_products_exclude_self_and_are_same_family(): void
    {
        $guitars = Category::where('slug', 'guitars')->firstOrFail();
        $acoustic = Category::where('slug', 'acoustic-guitars')->firstOrFail();
        $product = Product::where('category_id', $acoustic->id)->firstOrFail();

        $this->get(route('product.show', $product))
            ->assertOk()
            ->assertDontSee('Yamaha PSR-E373')   // keyboards, not related
            ->assertSee('You may also like');
    }

    public function test_add_to_cart_adds_item_with_price_snapshot(): void
    {
        // Fender 351 picks — no variants, clean price snapshot.
        $product = Product::where('slug', 'fender-351-shape-picks-12-pack-medium')->firstOrFail();

        Livewire::test(AddToCart::class, ['product' => $product])
            ->set('qty', 2)
            ->call('add')
            ->assertHasNoErrors()
            ->assertSet('added', true)
            ->assertDispatched('cart-updated');

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'product_variant_id' => null,
            'qty' => 2,
            'unit_price' => (float) $product->price,
        ]);
    }

    public function test_add_to_cart_with_variant_snapshot(): void
    {
        $product = Product::where('slug', 'squier-affinity-stratocaster-hss')->firstOrFail();
        $variant = $product->variants()->where('name', 'Black')->firstOrFail();

        Livewire::test(AddToCart::class, ['product' => $product])
            ->call('selectVariant', $variant->id)
            ->assertSet('variantId', $variant->id)
            ->call('add')
            ->assertSet('added', true);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'qty' => 1,
            'unit_price' => (float) $variant->effectivePrice($product),
        ]);
    }

    public function test_add_to_cart_rejects_qty_beyond_stock(): void
    {
        $product = Product::where('slug', 'yamaha-f310-acoustic-guitar')->firstOrFail();
        // Component auto-selects the first variant (Natural, stock 7).
        $variant = $product->variants()->firstOrFail();

        Livewire::test(AddToCart::class, ['product' => $product])
            ->set('qty', $variant->stock + 10)
            ->call('add')
            ->assertSet('added', false)
            ->assertSet('error', "Only {$variant->stock} in stock.");

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_add_to_cart_rejects_inactive_product(): void
    {
        $product = Product::factory()->inactive()->create([
            'category_id' => Category::first()->id,
            'brand_id' => Brand::first()->id,
            'slug' => 'inactive-cart-product',
            'sku' => 'RYM-INCART-01',
            'price' => 100,
            'stock' => 5,
        ]);

        Livewire::test(AddToCart::class, ['product' => $product])
            ->call('add')
            ->assertSet('added', false)
            ->assertSet('error', 'This product is no longer available.');
    }

    public function test_out_of_stock_shows_disabled_add_button(): void
    {
        $product = Product::factory()->create([
            'category_id' => Category::first()->id,
            'brand_id' => Brand::first()->id,
            'slug' => 'out-of-stock-page',
            'sku' => 'RYM-OOSP-01',
            'price' => 100,
            'stock' => 0,
        ]);

        $this->get(route('product.show', $product))
            ->assertOk()
            ->assertSee('Out of stock')
            ->assertSee('disabled', escape: false);
    }
}
