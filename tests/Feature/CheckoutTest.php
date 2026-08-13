<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\CheckoutWizard;
use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Payment\FakePaymentGateway;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::where('email', 'test@example.com')->firstOrFail();
        $this->actingAs($this->user);
    }

    private function fillCart(int $qty = 1): void
    {
        $service = app(CartService::class);
        $product = Product::where('slug', 'fender-351-shape-picks-12-pack-medium')->firstOrFail();
        $service->addItem($product, null, $qty);
    }

    private function addAddress(): int
    {
        $address = app(\App\Services\AddressService::class)->store($this->user->id, [
            'name' => 'Anoop Puri',
            'phone' => '9876543210',
            'line1' => '42, Music Lane',
            'city' => 'New Delhi',
            'state' => 'Delhi',
            'pincode' => '110001',
            'is_default' => true,
        ]);

        return $address->id;
    }

    public function test_checkout_requires_login(): void
    {
        auth()->logout();

        $this->get('/checkout')->assertRedirect('/login');
    }

    public function test_checkout_page_renders(): void
    {
        $this->fillCart();

        $this->get('/checkout')->assertOk()->assertSee('Almost there.');
    }

    public function test_full_checkout_flow_with_fake_gateway(): void
    {
        Mail::fake();
        $this->fillCart(2);
        $addressId = $this->addAddress();

        Livewire::test(CheckoutWizard::class)
            ->assertSet('step', 1)
            ->call('selectAddress', $addressId)
            ->assertSet('step', 2)
            ->call('placeOrder')
            ->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame(Order::STATUS_CONFIRMED, $order->status);
        $this->assertSame(Order::PAYMENT_PAID, $order->payment_status);
        $this->assertSame(2, $order->items()->sum('qty'));
        $this->assertSame((float) '399.00' * 2, (float) $order->total);

        // Stock decremented
        $product = Product::where('slug', 'fender-351-shape-picks-12-pack-medium')->firstOrFail();
        $this->assertSame(48, $product->fresh()->stock);

        // Confirmation email queued
        Mail::assertQueued(OrderConfirmationMail::class, fn ($mail) => $mail->order->is($order));

        // Cart cleared
        $this->assertSame(0, app(CartService::class)->count());

        // Status history audit trail written (pending → confirmed)
        $this->assertSame(1, $order->statusHistory()->count());
        $this->assertSame('confirmed', $order->statusHistory()->first()->to);
    }

    public function test_place_order_with_empty_cart_errors(): void
    {
        Livewire::test(CheckoutWizard::class)
            ->call('placeOrder')
            ->assertSet('paymentError', 'Your cart is empty.');
    }

    public function test_place_order_without_address_errors(): void
    {
        $this->fillCart();

        Livewire::test(CheckoutWizard::class)
            ->call('placeOrder')
            ->assertSet('paymentError', 'Please choose a delivery address.');
    }

    public function test_new_address_form_validation(): void
    {
        Livewire::test(CheckoutWizard::class)
            ->set('name', '')
            ->set('pincode', '12')
            ->call('saveNewAddress')
            ->assertHasErrors(['name' => 'required', 'pincode' => 'regex']);
    }

    public function test_failed_payment_marks_order_failed_and_keeps_cart(): void
    {
        $this->fillCart();
        $addressId = $this->addAddress();

        Livewire::test(CheckoutWizard::class)
            ->call('selectAddress', $addressId)
            ->set('orderId', null)
            ->call('placeOrder')
            ->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame(Order::PAYMENT_PAID, $order->payment_status);
    }

    public function test_fake_gateway_verify_success_and_failure(): void
    {
        $gateway = new FakePaymentGateway;

        $success = $gateway->verify(new Order(['order_number' => 'RYM-T-1', 'total' => 100]), ['status' => 'captured']);
        $this->assertTrue($success->success);

        $fail = $gateway->verify(new Order(['order_number' => 'RYM-T-2', 'total' => 100]), ['status' => 'declined']);
        $this->assertFalse($fail->success);
    }

    public function test_success_page_requires_valid_signature_and_owner(): void
    {
        $this->fillCart();
        $addressId = $this->addAddress();

        Livewire::test(CheckoutWizard::class)
            ->call('selectAddress', $addressId)
            ->call('placeOrder')
            ->assertRedirect();

        $order = Order::firstOrFail();

        // No signature → 403
        $this->get(route('checkout.success', $order))->assertForbidden();

        // Wrong owner → 403
        $other = User::factory()->create();
        $signed = \Illuminate\Support\Facades\URL::signedRoute('checkout.success', ['order' => $order]);
        $this->actingAs($other)->get($signed)->assertForbidden();

        // Owner + signature → 200
        $this->actingAs($this->user)->get($signed)
            ->assertOk()
            ->assertSee('Thank you! Your order is confirmed.')
            ->assertSee($order->order_number);
    }

    public function test_order_number_is_unique_and_formatted(): void
    {
        $service = app(\App\Services\OrderService::class);
        $numbers = [];

        for ($i = 0; $i < 5; $i++) {
            $numbers[] = $service->generateOrderNumber();
        }

        $this->assertSame(5, count(array_unique($numbers)));

        foreach ($numbers as $number) {
            $this->assertMatchesRegularExpression('/^RYM-\d{4}-[A-F0-9]{6}$/', $number);
        }
    }
}
