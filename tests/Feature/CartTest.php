<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guest_cart_uses_session(): void
    {
        $service = app(CartService::class);
        $cart = $service->getOrCreateCart();

        $this->assertNull($cart->user_id);
        $this->assertNotNull($cart->session_id);
        // Same session → same cart
        $this->assertSame($cart->id, $service->getOrCreateCart()->id);
    }

    public function test_add_item_creates_cart_item_with_snapshot(): void
    {
        $service = app(CartService::class);
        $product = Product::where('slug', 'fender-351-shape-picks-12-pack-medium')->firstOrFail();

        $service->addItem($product, null, 3);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'qty' => 3,
            'unit_price' => $product->price,
        ]);
        $this->assertSame(3, $service->count());
    }

    public function test_add_same_item_increments_qty(): void
    {
        $service = app(CartService::class);
        $product = Product::where('slug', 'fender-351-shape-picks-12-pack-medium')->firstOrFail();

        $service->addItem($product, null, 2);
        $service->addItem($product, null, 3);

        $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'qty' => 5]);
    }

    public function test_add_rejects_quantity_beyond_stock(): void
    {
        $service = app(CartService::class);
        $product = Product::where('slug', 'fender-351-shape-picks-12-pack-medium')->firstOrFail();

        $this->expectException(\RuntimeException::class);
        $service->addItem($product, null, $product->stock + 1);
    }

    public function test_update_qty_and_remove(): void
    {
        $service = app(CartService::class);
        $product = Product::where('slug', 'fender-351-shape-picks-12-pack-medium')->firstOrFail();

        $item = $service->addItem($product, null, 2);
        $service->updateQty($item, 4);
        $this->assertSame(4, $item->fresh()->qty);

        $service->removeItem($item);
        $this->assertSame(0, $service->count());
    }

    public function test_totals_calculate_subtotal(): void
    {
        $service = app(CartService::class);
        $product = Product::where('slug', 'fender-351-shape-picks-12-pack-medium')->firstOrFail();

        $service->addItem($product, null, 2);

        $totals = $service->totals();
        $this->assertSame((float) $product->price * 2, $totals['subtotal']);
        $this->assertSame(2, $totals['count']);
    }

    public function test_guest_cart_merges_into_user_cart_on_login(): void
    {
        $service = app(CartService::class);
        $product = Product::where('slug', 'fender-351-shape-picks-12-pack-medium')->firstOrFail();
        $service->addItem($product, null, 2);

        $sessionId = session()->get('rythme.cart.session');
        $this->assertNotNull($sessionId);

        $user = User::where('email', 'test@example.com')->firstOrFail();
        Auth::login($user);

        $service->mergeGuestCart($sessionId);

        $userCart = Cart::where('user_id', $user->id)->firstOrFail();
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $userCart->id,
            'product_id' => $product->id,
            'qty' => 2,
        ]);
        $this->assertSame(2, $service->count());
    }

    public function test_cart_page_renders_for_guest(): void
    {
        $this->get('/cart')->assertOk()->assertSee('Your cart');
    }

    public function test_cart_badge_reflects_count(): void
    {
        $service = app(CartService::class);
        $product = Product::where('slug', 'fender-351-shape-picks-12-pack-medium')->firstOrFail();
        $service->addItem($product, null, 2);

        $this->get('/')
            ->assertOk()
            ->assertSee('cart-updated', escape: false)
            ->assertSee('wire:poll.5s="refresh"', escape: false);
    }
}
