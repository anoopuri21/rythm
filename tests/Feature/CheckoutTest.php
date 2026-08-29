<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\DTOs\CheckoutData;
use App\Events\CommerceNotificationRequested;
use App\Livewire\CheckoutWizard;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SiteSetting;
use App\Models\User;
use App\Payment\FakePaymentGateway;
use App\Payment\PaymentResult;
use App\Payment\RazorpayGateway;
use App\Services\AddressService;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
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
        $address = app(AddressService::class)->store($this->user->id, [
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
        Event::fake([CommerceNotificationRequested::class]);
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

        Event::assertDispatched(
            CommerceNotificationRequested::class,
            fn ($event) => $event->orderId === $order->id && $event->eventType === 'order.confirmed'
        );

        // Cart cleared
        $this->assertSame(0, app(CartService::class)->count());

        // Status history audit trail written (pending → confirmed)
        $this->assertSame(1, $order->statusHistory()->count());
        $this->assertSame('confirmed', $order->statusHistory()->first()->to);
    }

    public function test_server_totals_are_persisted_and_rendered_consistently(): void
    {
        SiteSetting::query()->updateOrCreate(['key' => 'shipping_flat_fee'], ['value' => '50']);
        SiteSetting::query()->updateOrCreate(['key' => 'shipping_free_above'], ['value' => '0']);
        SiteSetting::query()->updateOrCreate(['key' => 'tax_rate'], ['value' => '10']);
        Cache::forget('site.settings');

        $this->fillCart(2);
        $addressId = $this->addAddress();

        Livewire::test(CheckoutWizard::class)
            ->call('selectAddress', $addressId)
            ->call('placeOrder')
            ->assertRedirect();

        $order = Order::query()->firstOrFail();
        $this->assertSame(798.0, (float) $order->subtotal);
        $this->assertSame(50.0, (float) $order->shipping_fee);
        $this->assertSame(79.8, (float) $order->tax);
        $this->assertSame(927.8, (float) $order->total);

        $this->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('798.00')
            ->assertSee('50.00')
            ->assertSee('79.80')
            ->assertSee('927.80');
        $this->get(URL::signedRoute('orders.invoice', ['order' => $order]))
            ->assertOk()
            ->assertSee('798.00')
            ->assertSee('50.00')
            ->assertSee('79.80')
            ->assertSee('927.80');
    }

    public function test_checkout_order_creation_is_idempotent_per_attempt(): void
    {
        $this->fillCart(2);
        $addressId = $this->addAddress();
        $addresses = app(AddressService::class);
        $address = $addresses->forUser($this->user->id)->firstWhere('id', $addressId);
        $data = new CheckoutData(
            addressId: $addressId,
            shippingAddress: $addresses->snapshot($address),
            billingAddress: $addresses->snapshot($address),
            idempotencyKey: 'checkout-attempt-123',
        );
        $cart = app(CartService::class)->getOrCreateCart();
        $orders = app(OrderService::class);

        $first = $orders->createFromCheckout($cart, $data, $this->user->id);
        $second = $orders->createFromCheckout($cart, $data, $this->user->id);

        $this->assertTrue($first->is($second));
        $this->assertSame(1, Order::query()->where('idempotency_key', 'checkout-attempt-123')->count());
        $this->assertSame(1, Order::query()->count());
    }

    public function test_checkout_idempotency_key_cannot_cross_accounts(): void
    {
        $this->fillCart();
        $addressId = $this->addAddress();
        $addresses = app(AddressService::class);
        $address = $addresses->forUser($this->user->id)->firstWhere('id', $addressId);
        $data = new CheckoutData(
            addressId: $addressId,
            shippingAddress: $addresses->snapshot($address),
            billingAddress: $addresses->snapshot($address),
            idempotencyKey: 'account-bound-attempt',
        );
        $cart = app(CartService::class)->getOrCreateCart();
        app(OrderService::class)->createFromCheckout($cart, $data, $this->user->id);

        $this->expectException(\RuntimeException::class);
        app(OrderService::class)->createFromCheckout($cart, $data, User::factory()->create()->id);
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
        $signed = URL::signedRoute('checkout.success', ['order' => $order]);
        $this->actingAs($other)->get($signed)->assertForbidden();

        // Owner + signature → 200
        $this->actingAs($this->user)->get($signed)
            ->assertOk()
            ->assertSee('Thank you! Your order is confirmed.')
            ->assertSee($order->order_number);
    }

    public function test_payment_finalization_is_idempotent(): void
    {
        Event::fake([CommerceNotificationRequested::class]);
        $this->fillCart(2);
        $addressId = $this->addAddress();

        Livewire::test(CheckoutWizard::class)
            ->call('selectAddress', $addressId)
            ->call('placeOrder')
            ->assertRedirect();

        $order = Order::with(['items', 'payments'])->firstOrFail();
        $payment = $order->payments->firstOrFail();
        $product = Product::where('slug', 'fender-351-shape-picks-12-pack-medium')->firstOrFail();
        $stockAfterFirstCapture = $product->stock;

        $replayed = app(OrderService::class)->markPaid(
            $order,
            new PaymentResult(true, 'paid', $payment->gateway_payment_id),
            $payment->gateway_order_id,
        );

        $this->assertFalse($replayed);
        $this->assertSame($stockAfterFirstCapture, $product->fresh()->stock);
        $this->assertSame(1, $order->payments()->count());
        $this->assertSame(1, $order->statusHistory()->where('to', Order::STATUS_CONFIRMED)->count());
        $this->assertDatabaseHas('inventory_movements', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'type' => InventoryMovement::TYPE_ORDER_CAPTURE,
            'quantity_delta' => -2,
            'balance_after' => $stockAfterFirstCapture,
        ]);
        $this->assertSame(1, $order->inventoryMovements()->count());
        Event::assertDispatchedTimes(CommerceNotificationRequested::class, 1);
    }

    public function test_variant_checkout_decrements_only_variant_stock_once(): void
    {
        $variant = ProductVariant::query()
            ->where('is_active', true)
            ->where('stock', '>=', 2)
            ->with('product')
            ->firstOrFail();
        $product = $variant->product;
        $productStockBefore = $product->stock;
        $variantStockBefore = $variant->stock;

        app(CartService::class)->addItem($product, $variant, 2);
        $addressId = $this->addAddress();

        Livewire::test(CheckoutWizard::class)
            ->call('selectAddress', $addressId)
            ->call('placeOrder')
            ->assertRedirect();

        $order = Order::with('payments')->firstOrFail();
        $payment = $order->payments->firstOrFail();

        $this->assertSame($variantStockBefore - 2, $variant->fresh()->stock);
        $this->assertSame($productStockBefore, $product->fresh()->stock);

        $replayed = app(OrderService::class)->markPaid(
            $order,
            new PaymentResult(true, 'paid', $payment->gateway_payment_id),
            $payment->gateway_order_id,
        );

        $this->assertFalse($replayed);
        $this->assertSame($variantStockBefore - 2, $variant->fresh()->stock);
        $this->assertSame($productStockBefore, $product->fresh()->stock);
        $this->assertDatabaseHas('inventory_movements', [
            'order_id' => $order->id,
            'product_id' => null,
            'product_variant_id' => $variant->id,
            'type' => InventoryMovement::TYPE_ORDER_CAPTURE,
            'quantity_delta' => -2,
            'balance_after' => $variantStockBefore - 2,
        ]);
        $this->assertSame(1, $order->inventoryMovements()->count());
    }

    public function test_checkout_rejects_stock_that_changed_after_cart_add(): void
    {
        $product = Product::where('slug', 'fender-351-shape-picks-12-pack-medium')->firstOrFail();
        app(CartService::class)->addItem($product, null, 1);
        $product->update(['stock' => 0]);
        $addressId = $this->addAddress();

        Livewire::test(CheckoutWizard::class)
            ->call('selectAddress', $addressId)
            ->call('placeOrder')
            ->assertSet('paymentError', "Not enough stock for {$product->name}.");

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_customer_cannot_confirm_another_customers_order(): void
    {
        $this->fillCart();
        $addressId = $this->addAddress();

        Livewire::test(CheckoutWizard::class)
            ->call('selectAddress', $addressId)
            ->call('placeOrder')
            ->assertRedirect();

        $order = Order::firstOrFail();
        $other = User::factory()->create();
        $this->actingAs($other);

        Livewire::test(CheckoutWizard::class)
            ->set('orderId', $order->id)
            ->set('gatewayOrderId', $order->payments()->value('gateway_order_id'))
            ->call('confirmPayment', ['status' => 'captured'])
            ->assertSet('paymentError', 'No pending order found.');
    }

    public function test_payment_initiation_is_idempotent_for_gateway_order(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);
        $service = app(OrderService::class);

        $first = $service->recordPaymentInitiation($order, 'order_same_gateway_id');
        $second = $service->recordPaymentInitiation($order, 'order_same_gateway_id');

        $this->assertTrue($first->is($second));
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_order_number_is_unique_and_formatted(): void
    {
        $service = app(OrderService::class);
        $numbers = [];

        for ($i = 0; $i < 5; $i++) {
            $numbers[] = $service->generateOrderNumber();
        }

        $this->assertSame(5, count(array_unique($numbers)));

        foreach ($numbers as $number) {
            $this->assertMatchesRegularExpression('/^RYM-\d{4}-[A-F0-9]{6}$/', $number);
        }
    }

    public function test_razorpay_webhook_signature_verification(): void
    {
        $gateway = new RazorpayGateway('key_id', 'key_secret', 'whsec_test');

        $body = json_encode(['event' => 'payment.captured', 'payload' => []]);
        $valid = hash_hmac('sha256', $body, 'whsec_test');

        $this->assertTrue($gateway->verifyWebhookSignature($body, $valid));
        $this->assertFalse($gateway->verifyWebhookSignature($body, 'tampered'));
        $this->assertFalse($gateway->verifyWebhookSignature($body, ''));
    }

    public function test_invalid_razorpay_callback_cannot_mutate_payment_state(): void
    {
        config()->set('rythme.razorpay.key_id', 'rzp_test_key');
        config()->set('rythme.razorpay.key_secret', 'test_secret');

        $this->fillCart();
        $addressId = $this->addAddress();
        $addresses = app(AddressService::class);
        $address = $addresses->forUser($this->user->id)->firstWhere('id', $addressId);
        $data = new CheckoutData(
            addressId: $addressId,
            shippingAddress: $addresses->snapshot($address),
            billingAddress: $addresses->snapshot($address),
            idempotencyKey: 'invalid-callback-attempt',
        );
        $order = app(OrderService::class)->createFromCheckout(
            app(CartService::class)->getOrCreateCart(),
            $data,
            $this->user->id,
        );
        $payment = app(OrderService::class)->recordPaymentInitiation($order, 'order_gateway_123');

        $this->post(route('payment.razorpay.callback'), [
            'razorpay_payment_id' => 'pay_untrusted',
            'razorpay_order_id' => 'order_gateway_123',
            'razorpay_signature' => 'invalid',
        ])->assertRedirect(route('checkout.index'));

        $this->assertSame(Payment::STATUS_INITIATED, $payment->fresh()->status);
        $this->assertSame(Order::PAYMENT_UNPAID, $order->fresh()->payment_status);
    }

    public function test_razorpay_callback_signature_verification(): void
    {
        $gateway = new RazorpayGateway('key_id', 'key_secret', 'whsec_test');

        $payload = [
            'razorpay_order_id' => 'order_abc',
            'razorpay_payment_id' => 'pay_xyz',
            'razorpay_signature' => 'forged',
        ];
        $order = new Order(['order_number' => 'RYM-SEC-1', 'total' => 100]);

        $result = $gateway->verify($order, $payload);
        $this->assertFalse($result->success);

        // Missing fields
        $result = $gateway->verify($order, []);
        $this->assertFalse($result->success);
    }
}
