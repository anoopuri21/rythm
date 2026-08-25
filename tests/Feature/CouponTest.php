<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\CheckoutWizard;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\AddressService;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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

    public function test_coupon_usage_is_reserved_atomically_before_payment(): void
    {
        $coupon = Coupon::factory()->create([
            'code' => 'LASTONE',
            'max_uses' => 1,
            'used_count' => 0,
            'min_order' => 0,
        ]);
        $first = Order::factory()->create([
            'coupon_code' => $coupon->code,
            'subtotal' => 1000,
            'discount' => 100,
            'total' => 900,
        ]);
        $second = Order::factory()->create([
            'coupon_code' => $coupon->code,
            'subtotal' => 1000,
            'discount' => 100,
            'total' => 900,
        ]);
        $orders = app(OrderService::class);

        $orders->recordPaymentInitiation($first, 'gateway_first');

        $this->assertSame(1, $coupon->fresh()->used_count);
        $this->assertNotNull($first->fresh()->coupon_usage_recorded_at);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('usage limit');
        $orders->recordPaymentInitiation($second, 'gateway_second');
    }

    public function test_unpaid_cancellation_releases_coupon_reservation_once(): void
    {
        $coupon = Coupon::factory()->create([
            'code' => 'RELEASEME',
            'max_uses' => 1,
            'used_count' => 0,
            'min_order' => 0,
        ]);
        $order = Order::factory()->create([
            'status' => Order::STATUS_PENDING,
            'payment_status' => Order::PAYMENT_UNPAID,
            'coupon_code' => $coupon->code,
            'subtotal' => 1000,
            'discount' => 100,
            'total' => 900,
        ]);
        $orders = app(OrderService::class);
        $orders->recordPaymentInitiation($order, 'gateway_release');

        $orders->cancelByUser($order);

        $this->assertSame(0, $coupon->fresh()->used_count);
        $this->assertNotNull($order->fresh()->coupon_usage_released_at);
    }

    public function test_invalid_coupon_type_and_value_are_rejected(): void
    {
        Coupon::factory()->create(['code' => 'BADTYPE', 'type' => 'mystery', 'value' => 10]);
        Coupon::factory()->create(['code' => 'BADPERCENT', 'type' => Coupon::TYPE_PERCENT, 'value' => 101]);

        try {
            app(CouponService::class)->validateAndApply('BADTYPE', 1000);
            $this->fail('Invalid coupon type was accepted.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('invalid discount type', $exception->getMessage());
        }

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('invalid discount value');
        app(CouponService::class)->validateAndApply('BADPERCENT', 1000);
    }

    public function test_invalid_coupon_active_period_is_rejected(): void
    {
        Coupon::factory()->create([
            'code' => 'BADWINDOW',
            'starts_at' => now()->subHour(),
            'expires_at' => now()->subHours(2),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('invalid active period');
        app(CouponService::class)->validateAndApply('BADWINDOW', 1000);
    }

    public function test_coupon_codes_are_normalized_on_write(): void
    {
        $coupon = Coupon::factory()->create(['code' => '  mixedCase  ']);

        $this->assertSame('MIXEDCASE', $coupon->fresh()->code);
        $result = app(CouponService::class)->validateAndApply('mixedcase', 1000);
        $this->assertTrue($result['coupon']->is($coupon));
        $this->assertGreaterThan(0, $result['discount']);
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

    public function test_usage_increment_cannot_exceed_limit(): void
    {
        $coupon = Coupon::factory()->create(['max_uses' => 1, 'used_count' => 1]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('usage limit');
        app(CouponService::class)->incrementUsage($coupon);
    }

    public function test_checkout_with_coupon_discounts_order(): void
    {
        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->actingAs($user);

        Coupon::factory()->create(['code' => 'WELCOME10', 'type' => 'percent', 'value' => 10]);

        // Cart: Fender picks ₹399
        $product = Product::where('slug', 'fender-351-shape-picks-12-pack-medium')->firstOrFail();
        app(CartService::class)->addItem($product, null, 2); // ₹798

        $addressId = app(AddressService::class)->store($user->id, [
            'name' => 'Anoop Puri', 'phone' => '9876543210',
            'line1' => '42, Music Lane', 'city' => 'New Delhi', 'state' => 'Delhi', 'pincode' => '110001',
        ])->id;

        Livewire::test(CheckoutWizard::class)
            ->call('selectAddress', $addressId)
            ->set('couponCode', 'WELCOME10')
            ->call('applyCoupon')
            ->assertSet('appliedCoupon', 'WELCOME10')
            ->assertSet('couponDiscount', 79.8)
            ->call('placeOrder')
            ->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame(79.8, (float) $order->discount);
        $this->assertSame(718.2, (float) $order->total);
        $this->assertStringContainsString('WELCOME10', (string) $order->notes);
    }

    public function test_checkout_ignores_tampered_livewire_discount_amount(): void
    {
        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->actingAs($user);

        $product = Product::where('slug', 'fender-351-shape-picks-12-pack-medium')->firstOrFail();
        app(CartService::class)->addItem($product, null, 1);

        $addressId = app(AddressService::class)->store($user->id, [
            'name' => 'Anoop Puri', 'phone' => '9876543210',
            'line1' => '42, Music Lane', 'city' => 'New Delhi', 'state' => 'Delhi', 'pincode' => '110001',
        ])->id;

        Livewire::test(CheckoutWizard::class)
            ->call('selectAddress', $addressId)
            ->set('couponDiscount', 398.99)
            ->call('placeOrder')
            ->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame(0.0, (float) $order->discount);
        $this->assertSame(399.0, (float) $order->total);
    }

    public function test_coupon_is_revalidated_when_order_is_placed(): void
    {
        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->actingAs($user);

        $coupon = Coupon::factory()->create(['code' => 'STALE10', 'type' => 'percent', 'value' => 10]);
        $product = Product::where('slug', 'fender-351-shape-picks-12-pack-medium')->firstOrFail();
        app(CartService::class)->addItem($product, null, 1);

        $addressId = app(AddressService::class)->store($user->id, [
            'name' => 'Anoop Puri', 'phone' => '9876543210',
            'line1' => '42, Music Lane', 'city' => 'New Delhi', 'state' => 'Delhi', 'pincode' => '110001',
        ])->id;

        $component = Livewire::test(CheckoutWizard::class)
            ->call('selectAddress', $addressId)
            ->set('couponCode', 'STALE10')
            ->call('applyCoupon')
            ->assertSet('appliedCoupon', 'STALE10');

        $coupon->update(['expires_at' => now()->subMinute()]);

        $component->call('placeOrder')
            ->assertSet('paymentError', 'This coupon has expired.');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_reprices_cart_from_current_catalog_price(): void
    {
        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->actingAs($user);

        $product = Product::where('slug', 'fender-351-shape-picks-12-pack-medium')->firstOrFail();
        app(CartService::class)->addItem($product, null, 1);
        $product->update(['price' => 449]);

        $addressId = app(AddressService::class)->store($user->id, [
            'name' => 'Anoop Puri', 'phone' => '9876543210',
            'line1' => '42, Music Lane', 'city' => 'New Delhi', 'state' => 'Delhi', 'pincode' => '110001',
        ])->id;

        Livewire::test(CheckoutWizard::class)
            ->call('selectAddress', $addressId)
            ->call('placeOrder')
            ->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame(449.0, (float) $order->subtotal);
        $this->assertSame(449.0, (float) $order->items()->firstOrFail()->unit_price);
    }

    public function test_admin_can_access_coupons(): void
    {
        $admin = User::where('email', 'admin@rythme.test')->firstOrFail();
        Coupon::factory()->create(['code' => 'ADMIN10']);

        $this->actingAs($admin)->get('/admin/coupons')->assertOk()->assertSee('ADMIN10');
    }
}
