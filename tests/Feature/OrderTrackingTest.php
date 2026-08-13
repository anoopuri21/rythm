<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::where('email', 'test@example.com')->firstOrFail();
        $this->actingAs($this->user);
    }

    private function makeOrder(string $status = Order::STATUS_CONFIRMED, string $payment = Order::PAYMENT_PAID): Order
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'status' => $status,
            'payment_status' => $payment,
        ]);

        // Audit history like a real flow
        $order->statusHistory()->create(['from' => null, 'to' => 'placed', 'actor' => 'system']);
        $order->statusHistory()->create(['from' => 'placed', 'to' => 'confirmed', 'actor' => 'system']);

        return $order;
    }

    public function test_order_tracking_page_renders_timeline(): void
    {
        $order = $this->makeOrder();

        $this->get(route('orders.show', $order))
            ->assertOk()
            ->assertViewIs('orders.show')
            ->assertSee('Order placed')
            ->assertSee('Payment confirmed')
            ->assertSee('Processing')
            ->assertSee('Shipped')
            ->assertSee('Delivered')
            ->assertSee($order->order_number);
    }

    public function test_shipped_order_marks_previous_steps_done(): void
    {
        $order = $this->makeOrder(Order::STATUS_SHIPPED);
        $order->statusHistory()->create(['from' => 'confirmed', 'to' => 'shipped', 'actor' => 'system']);

        $timeline = $order->trackingTimeline();
        $keys = array_column($timeline, 'key');
        $done = array_column($timeline, 'done');

        $this->assertSame(['placed', 'confirmed', 'processing', 'shipped', 'delivered'], $keys);
        // placed, confirmed, processing, shipped = done; delivered = pending
        $this->assertSame([true, true, true, true, false], $done);
    }

    public function test_cancelled_order_shows_cancelled_step(): void
    {
        $order = $this->makeOrder(Order::STATUS_CANCELLED);
        $order->statusHistory()->create(['from' => 'confirmed', 'to' => 'cancelled', 'actor' => 'customer']);

        $this->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Cancelled');
    }

    public function test_cannot_view_others_order(): void
    {
        $other = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $other->id, 'email' => $other->email]);

        $this->get(route('orders.show', $order))->assertForbidden();
    }

    public function test_order_tracking_requires_login_without_signed_link(): void
    {
        auth()->logout();

        $order = $this->makeOrder();
        // Guest without signed link → redirected to login
        $this->get(route('orders.show', $order))->assertRedirect('/login');
    }

    public function test_signed_link_allows_guest_access(): void
    {
        auth()->logout();

        $order = $this->makeOrder();
        $signed = \Illuminate\Support\Facades\URL::signedRoute('orders.show', ['order' => $order]);

        $this->get($signed)
            ->assertOk()
            ->assertSee($order->order_number);
    }

    public function test_guest_lookup_page_renders(): void
    {
        auth()->logout();

        $this->get('/track-order')
            ->assertOk()
            ->assertSee('Track your order');
    }

    public function test_guest_lookup_finds_order_by_number_and_email(): void
    {
        auth()->logout();

        $order = $this->makeOrder();

        $this->post('/track-order', [
            'order_number' => $order->order_number,
            'email' => $this->user->email,
        ])->assertRedirect(route('orders.show', $order));
    }

    public function test_guest_lookup_rejects_mismatch(): void
    {
        auth()->logout();

        $order = $this->makeOrder();

        $this->post('/track-order', [
            'order_number' => $order->order_number,
            'email' => 'wrong@example.com',
        ])->assertSessionHasErrors('order_number');
    }

    public function test_invoice_renders_for_owner(): void
    {
        $order = $this->makeOrder();

        $this->get(route('orders.invoice', $order))
            ->assertOk()
            ->assertViewIs('orders.invoice')
            ->assertSee('Tax invoice')
            ->assertSee($order->order_number)
            ->assertSee('Print / Save PDF');
    }

    public function test_invoice_forbidden_for_others(): void
    {
        $other = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $other->id, 'email' => $other->email]);

        $this->get(route('orders.invoice', $order))->assertForbidden();
    }

    public function test_account_orders_link_to_tracking(): void
    {
        $order = $this->makeOrder();

        $this->get('/account')
            ->assertOk()
            ->assertSee(route('orders.show', $order), escape: false);
    }
    public function test_shipped_status_queues_email(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $order = $this->makeOrder(Order::STATUS_CONFIRMED);
        app(\App\Services\OrderService::class)->changeStatus($order, Order::STATUS_SHIPPED);

        $this->assertSame(Order::STATUS_SHIPPED, $order->fresh()->status);
        \Illuminate\Support\Facades\Mail::assertQueued(
            \App\Mail\OrderStatusMail::class,
            fn ($mail) => $mail->order->is($order) && $mail->newStatus === Order::STATUS_SHIPPED
        );

        // Audit history has the transition
        $this->assertDatabaseHas('order_status_history', [
            'order_id' => $order->id,
            'to' => 'shipped',
        ]);
    }

    public function test_invalid_transition_rejected(): void
    {
        $order = $this->makeOrder(Order::STATUS_SHIPPED);

        $this->expectException(\RuntimeException::class);
        app(\App\Services\OrderService::class)->changeStatus($order, Order::STATUS_PROCESSING);
    }

    public function test_cancelled_queues_email(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $order = $this->makeOrder(Order::STATUS_CONFIRMED);
        app(\App\Services\OrderService::class)->changeStatus($order, Order::STATUS_CANCELLED);

        \Illuminate\Support\Facades\Mail::assertQueued(
            \App\Mail\OrderStatusMail::class,
            fn ($mail) => $mail->newStatus === Order::STATUS_CANCELLED
        );
    }
    public function test_admin_order_resource_lists_and_views(): void
    {
        $admin = \App\Models\User::where('email', 'admin@rythme.test')->firstOrFail();
        $order = $this->makeOrder();

        $this->actingAs($admin)
            ->get('/admin/orders')
            ->assertOk()
            ->assertSee($order->order_number);

        $this->actingAs($admin)
            ->get('/admin/orders/'.$order->id)
            ->assertOk()
            ->assertSee('Status history');
    }

    public function test_admin_can_change_status_from_resource(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $admin = \App\Models\User::where('email', 'admin@rythme.test')->firstOrFail();
        $order = $this->makeOrder(Order::STATUS_CONFIRMED);

        $this->actingAs($admin)->get('/admin/orders/'.$order->id)->assertOk();

        // Service level change (resource action wraps this)
        app(\App\Services\OrderService::class)->changeStatus($order, Order::STATUS_PROCESSING);
        $this->assertSame(Order::STATUS_PROCESSING, $order->fresh()->status);
    }

    public function test_admin_cannot_create_orders(): void
    {
        $admin = \App\Models\User::where('email', 'admin@rythme.test')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/orders/create')
            ->assertNotFound();
    }
    public function test_user_can_cancel_confirmed_order_with_stock_restore(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $product = \App\Models\Product::where('slug', 'fender-351-shape-picks-12-pack-medium')->firstOrFail();
        $stockBefore = $product->stock;

        $order = $this->makeOrder(Order::STATUS_CONFIRMED, Order::PAYMENT_PAID);
        \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'unit_price' => $product->price,
            'qty' => 2,
            'total' => (float) $product->price * 2,
        ]);

        $this->post(route('orders.cancel', $order))
            ->assertRedirect()
            ->assertSessionHas('order_success');

        $order->refresh();
        $this->assertSame(Order::STATUS_CANCELLED, $order->status);
        $this->assertSame(Order::PAYMENT_REFUNDED, $order->payment_status);
        // stock restored
        $this->assertSame($stockBefore + 2, $product->fresh()->stock);

        \Illuminate\Support\Facades\Mail::assertQueued(
            \App\Mail\OrderStatusMail::class,
            fn ($mail) => $mail->newStatus === Order::STATUS_CANCELLED
        );
    }

    public function test_cannot_cancel_shipped_order(): void
    {
        $order = $this->makeOrder(Order::STATUS_SHIPPED);

        $this->post(route('orders.cancel', $order))
            ->assertRedirect()
            ->assertSessionHas('order_error');

        $this->assertSame(Order::STATUS_SHIPPED, $order->fresh()->status);
    }

    public function test_cannot_cancel_others_order(): void
    {
        $other = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $other->id, 'email' => $other->email, 'status' => Order::STATUS_CONFIRMED]);

        $this->post(route('orders.cancel', $order))->assertForbidden();
    }

    public function test_cancel_button_shown_on_confirmable_order_page(): void
    {
        $order = $this->makeOrder(Order::STATUS_CONFIRMED);

        $this->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Cancel order');
    }

    public function test_cancel_button_hidden_on_shipped_order(): void
    {
        $order = $this->makeOrder(Order::STATUS_SHIPPED);

        $this->get(route('orders.show', $order))
            ->assertOk()
            ->assertDontSee('Cancel order');
    }
}
