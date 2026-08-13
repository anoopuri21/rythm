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
}
