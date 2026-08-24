<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use App\Services\CouponService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_percent_coupon_discount(): void
    {
        $coupon = Coupon::factory()->create(['code' => 'SAVE10', 'type' => 'percent', 'value' => 10]);

        $result = app(CouponService::class)->validateAndApply('SAVE10', 1000);

        $this->assertSame(100.0, $result['discount']);
    }

    public function test_fixed_coupon_discount(): void
    {
        $coupon = Coupon::factory()->fixed(200)->create(['code' => 'FLAT200']);

        $result = app(CouponService::class)->validateAndApply('flat200', 1000);

        $this->assertSame(200.0, $result['discount']);
    }

    public function test_invalid_code_rejected(): void
    {
        $this->expectException(\RuntimeException::class);
        app(CouponService::class)->validateAndApply('NOPE', 1000);
    }

    public function test_expired_coupon_rejected(): void
    {
        Coupon::factory()->create(['code' => 'OLD', 'expires_at' => now()->subDay()]);

        $this->expectException(\RuntimeException::class);
        app(CouponService::class)->validateAndApply('OLD', 1000);
    }

    public function test_min_order_enforced(): void
    {
        Coupon::factory()->create(['code' => 'BIG', 'min_order' => 5000]);

        $this->expectException(\RuntimeException::class);
        app(CouponService::class)->validateAndApply('BIG', 1000);
    }

    public function test_usage_limit_enforced(): void
    {
        Coupon::factory()->create(['code' => 'LIMITED', 'max_uses' => 2, 'used_count' => 2]);

        $this->expectException(\RuntimeException::class);
        app(CouponService::class)->validateAndApply('LIMITED', 1000);
    }

    public function test_max_discount_cap(): void
    {
        Coupon::factory()->create(['code' => 'CAP', 'type' => 'percent', 'value' => 50, 'max_discount' => 300]);

        $result = app(CouponService::class)->validateAndApply('CAP', 1000);
        $this->assertSame(300.0, $result['discount']);
    }

    public function test_usage_increments(): void
    {
        $coupon = Coupon::factory()->create(['code' => 'INC']);

        app(CouponService::class)->incrementUsage($coupon);
        $this->assertSame(1, $coupon->fresh()->used_count);
    }

    public function test_checkout_with_coupon_discounts_order(): void
    {
        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->actingAs($user);

        Coupon::factory()->create(['code' => 'WELCOME10', 'type' => 'percent', 'value' => 10]);

        // Cart: Fender picks ₹399
        $product = Product::where('slug', 'fender-351-shape-picks-12-pack-medium')->firstOrFail();
        app(CartService::class)->addItem($product, null, 2); // ₹798

        $addressId = app(\App\Services\AddressService::class)->store($user->id, [
            'name' => 'Anoop Puri', 'phone' => '9876543210',
            'line1' => '42, Music Lane', 'city' => 'New Delhi', 'state' => 'Delhi', 'pincode' => '110001',
        ])->id;

        \Livewire\Livewire::test(\App\Livewire\CheckoutWizard::class)
            ->call('selectAddress', $addressId)
            ->set('couponCode', 'WELCOME10')
            ->call('applyCoupon')
            ->assertSet('appliedCoupon', 'WELCOME10')
            ->assertSet('couponDiscount', 79.8)
            ->call('placeOrder')
            ->assertRedirect();

        $order = \App\Models\Order::firstOrFail();
        $this->assertSame(79.8, (float) $order->discount);
        $this->assertSame(718.2, (float) $order->total);
        $this->assertStringContainsString('WELCOME10', (string) $order->notes);
    }

    public function test_admin_can_access_coupons(): void
    {
        $admin = User::where('email', 'admin@rythme.test')->firstOrFail();
        Coupon::factory()->create(['code' => 'ADMIN10']);

        $this->actingAs($admin)->get('/admin/coupons')->assertOk()->assertSee('ADMIN10');
    }
}
