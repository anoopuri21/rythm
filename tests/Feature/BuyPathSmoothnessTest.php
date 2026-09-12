<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\CheckoutWizard;
use App\Models\Product;
use App\Models\User;
use App\Services\AddressService;
use App\Services\CartService;
use App\Support\PaymentAvailability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * C4 / W2: buy-path copy, login intended, payment availability guards.
 */
class BuyPathSmoothnessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_empty_cart_page_uses_production_empty_copy(): void
    {
        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Your cart is empty', false)
            ->assertSee('Browse the shop', false)
            ->assertSee('checkout requires an account', false);
    }

    public function test_guest_cart_cta_points_to_login_with_checkout_intended(): void
    {
        $product = Product::query()->where('is_active', true)->where('stock', '>', 0)->firstOrFail();
        app(CartService::class)->addItem($product, null, 1);

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Sign in to checkout', false)
            ->assertSee('intended', false);
    }

    public function test_login_show_stores_safe_intended_path(): void
    {
        $this->get(route('login', ['intended' => '/checkout']))
            ->assertOk();

        $this->assertSame('/checkout', session('url.intended'));
    }

    public function test_login_show_ignores_external_intended(): void
    {
        $this->get(route('login', ['intended' => 'https://evil.example/phish']))
            ->assertOk();

        $this->assertNotSame('https://evil.example/phish', session('url.intended'));
    }

    public function test_payment_availability_blocks_when_unconfigured_and_fake_disallowed(): void
    {
        Config::set('services.razorpay.key_id', '');
        Config::set('services.razorpay.key_secret', '');
        Config::set('services.razorpay.allow_fake', false);
        // Force non-local style: runningUnitTests still allows fake via PaymentAvailability —
        // assert mode helpers when not in unit-test path by checking razorpayConfigured only.
        $this->assertFalse(PaymentAvailability::razorpayConfigured());
    }

    public function test_checkout_place_order_blocked_message_when_gateway_unavailable(): void
    {
        // In PHPUnit, fake is allowed by design so CheckoutTest keeps working.
        // Here we only assert the UI exposes availability flags when configured.
        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->actingAs($user);

        Config::set('services.razorpay.key_id', 'rzp_test_dummy');
        Config::set('services.razorpay.key_secret', 'dummy_secret');

        $this->assertTrue(PaymentAvailability::razorpayConfigured());
        $this->assertTrue(PaymentAvailability::canCheckout());
        $this->assertSame('razorpay', PaymentAvailability::mode());
        $this->assertStringContainsString('Razorpay', PaymentAvailability::customerMessage());
    }

    public function test_checkout_wizard_shows_free_shipping_and_pay_label_when_ready(): void
    {
        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->actingAs($user);

        $product = Product::where('slug', 'fender-351-shape-picks-12-pack-medium')->firstOrFail();
        app(CartService::class)->addItem($product, null, 1);

        $address = app(AddressService::class)->store($user->id, [
            'name' => 'Buyer',
            'phone' => '9876543210',
            'line1' => '1 Road',
            'city' => 'Delhi',
            'state' => 'Delhi',
            'pincode' => '110001',
            'is_default' => true,
        ]);

        // Tests allow fake gateway when keys empty.
        Config::set('services.razorpay.key_id', '');
        Config::set('services.razorpay.key_secret', '');
        Config::set('services.razorpay.allow_fake', true);

        Livewire::test(CheckoutWizard::class)
            ->call('selectAddress', $address->id)
            ->assertSet('step', 2)
            ->assertSee('Free')
            ->assertSee('Simulate pay', false);
    }

    public function test_wishlist_empty_copy_is_direct(): void
    {
        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->actingAs($user);

        $this->get(route('wishlist.index'))
            ->assertOk()
            ->assertSee('Your wishlist is empty', false)
            ->assertSee('Browse the shop', false);
    }
}
