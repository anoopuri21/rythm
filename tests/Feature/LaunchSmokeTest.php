<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Small release-gate suite for the highest-value storefront routes.
 * Detailed behavior remains covered by the domain-specific feature tests.
 */
final class LaunchSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_homepage_search_and_guest_cart_smoke_paths_render(): void
    {
        $product = Product::query()->where('is_active', true)->where('stock', '>', 0)->firstOrFail();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Rhythm Exports');

        $this->get(route('shop.index', ['q' => $product->name]))
            ->assertOk()
            ->assertSee($product->name);

        app(CartService::class)->addItem($product, null, 1);
        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee($product->name);
    }

    public function test_checkout_authentication_and_signed_order_success_smoke_paths(): void
    {
        $this->get(route('checkout.index'))->assertRedirect(route('login'));

        $user = User::query()->where('email', 'test@example.com')->firstOrFail();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => Order::STATUS_CONFIRMED,
            'payment_status' => Order::PAYMENT_PAID,
        ]);
        $this->actingAs($user);

        $this->get(route('checkout.index'))->assertOk();
        $this->get(route('checkout.success', $order))->assertForbidden();
        $this->get(URL::signedRoute('checkout.success', ['order' => $order]))
            ->assertOk()
            ->assertSee($order->order_number);
    }
}
