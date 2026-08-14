<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\WishlistButton;
use App\Livewire\WishlistPage;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::where('email', 'test@example.com')->firstOrFail();
    }

    public function test_wishlist_page_requires_login(): void
    {
        $this->get('/wishlist')->assertRedirect('/login');
    }

    public function test_wishlist_page_renders_empty_state(): void
    {
        $this->actingAs($this->user)->get('/wishlist')
            ->assertOk()
            ->assertSee('Nothing saved yet');
    }

    public function test_toggle_adds_and_removes_wishlist_item(): void
    {
        $this->actingAs($this->user);

        $product = Product::where('slug', 'yamaha-f310-acoustic-guitar')->firstOrFail();

        Livewire::test(WishlistButton::class, ['productId' => $product->id, 'variant' => 'card'])
            ->assertSet('active', false)
            ->call('toggle')
            ->assertSet('active', true)
            ->assertDispatched('wishlist-updated');

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $this->user->id,
            'product_id' => $product->id,
        ]);

        Livewire::test(WishlistButton::class, ['productId' => $product->id, 'variant' => 'card'])
            ->call('toggle')
            ->assertSet('active', false);

        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $this->user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_guest_toggle_redirects_to_login(): void
    {
        $product = Product::first();

        Livewire::test(WishlistButton::class, ['productId' => $product->id, 'variant' => 'card'])
            ->call('toggle')
            ->assertRedirect('/login');
    }

    public function test_wishlist_page_lists_saved_products(): void
    {
        $this->actingAs($this->user);

        $product = Product::where('slug', 'yamaha-f310-acoustic-guitar')->firstOrFail();
        \App\Models\Wishlist::create(['user_id' => $this->user->id, 'product_id' => $product->id]);

        $this->get('/wishlist')
            ->assertOk()
            ->assertSee('Yamaha F310 Acoustic Guitar')
            ->assertSee('Move to cart');
    }

    public function test_move_to_cart_moves_and_removes_from_wishlist(): void
    {
        $this->actingAs($this->user);

        $product = Product::where('slug', 'fender-351-shape-picks-12-pack-medium')->firstOrFail();
        \App\Models\Wishlist::create(['user_id' => $this->user->id, 'product_id' => $product->id]);

        Livewire::test(WishlistPage::class)
            ->call('moveToCart', $product->id)
            ->assertDispatched('wishlist-updated')
            ->assertDispatched('cart-updated');

        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $this->user->id,
            'product_id' => $product->id,
        ]);
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'qty' => 1,
        ]);
    }

    public function test_remove_from_wishlist_page(): void
    {
        $this->actingAs($this->user);

        $product = Product::first();
        \App\Models\Wishlist::create(['user_id' => $this->user->id, 'product_id' => $product->id]);

        Livewire::test(WishlistPage::class)
            ->call('remove', $product->id);

        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $this->user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_wishlist_button_shows_on_shop_card(): void
    {
        $this->get('/shop')
            ->assertOk()
            ->assertSee('wishlist-updated', escape: false);
    }
}
