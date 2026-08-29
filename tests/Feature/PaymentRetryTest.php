<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentRetryTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_retry_failed_payment_without_duplicate_order(): void
    {
        $user = User::factory()->create();
        $order = $this->pendingOrder($user);
        $this->payment($order, 'failed_order', Payment::STATUS_FAILED);

        $response = $this->actingAs($user)->post(route('orders.retry-payment', $order));

        $response->assertRedirect();
        $this->assertStringContainsString('/checkout/success/', $response->headers->get('Location'));
        $this->assertSame(Order::PAYMENT_PAID, $order->fresh()->payment_status);
        $this->assertSame(Order::STATUS_CONFIRMED, $order->fresh()->status);
        $this->assertDatabaseCount('orders', 1);
        $this->assertSame(2, $order->payments()->count());
        $this->assertSame(1, $order->payments()->where('status', Payment::STATUS_PAID)->count());
    }

    public function test_existing_abandoned_initiation_is_reused(): void
    {
        $user = User::factory()->create();
        $order = $this->pendingOrder($user);
        $payment = $this->payment($order, 'existing_gateway_order', Payment::STATUS_INITIATED);

        $this->actingAs($user)->post(route('orders.retry-payment', $order))->assertRedirect();

        $this->assertSame(1, $order->payments()->count());
        $this->assertSame('existing_gateway_order', $payment->fresh()->gateway_order_id);
        $this->assertSame(Payment::STATUS_PAID, $payment->fresh()->status);
    }

    public function test_retry_is_owner_only_and_rejects_non_payable_orders(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $order = $this->pendingOrder($owner);

        $this->actingAs($other)->post(route('orders.retry-payment', $order))->assertForbidden();
        $this->assertDatabaseCount('payments', 0);

        $order->update([
            'status' => Order::STATUS_CONFIRMED,
            'payment_status' => Order::PAYMENT_PAID,
        ]);

        $this->actingAs($owner)
            ->from(route('orders.show', $order))
            ->post(route('orders.retry-payment', $order))
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('order_error');
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_retry_attempts_are_bounded_and_unknown_preparation_is_not_retried(): void
    {
        $user = User::factory()->create();
        $order = $this->pendingOrder($user);

        foreach (range(1, 3) as $attempt) {
            $this->payment($order, 'failed_'.$attempt, Payment::STATUS_FAILED);
        }

        $this->actingAs($user)
            ->from(route('orders.show', $order))
            ->post(route('orders.retry-payment', $order))
            ->assertSessionHas('order_error', 'The payment-attempt limit has been reached. Contact support with your order number.');
        $this->assertSame(3, $order->payments()->count());

        $otherOrder = $this->pendingOrder($user);
        $this->payment($otherOrder, 'pending_retry_existing', Payment::STATUS_INITIATED);

        $this->actingAs($user)
            ->from(route('orders.show', $otherOrder))
            ->post(route('orders.retry-payment', $otherOrder))
            ->assertSessionHas('order_error', 'A payment attempt is already being prepared. Check the order again before retrying.');
        $this->assertSame(1, $otherOrder->payments()->count());
    }

    public function test_order_page_only_offers_retry_for_eligible_owner(): void
    {
        $user = User::factory()->create();
        $order = $this->pendingOrder($user);

        $this->actingAs($user)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Retry payment');

        $order->update(['payment_status' => Order::PAYMENT_PAID, 'status' => Order::STATUS_CONFIRMED]);

        $this->actingAs($user)->get(route('orders.show', $order))
            ->assertOk()
            ->assertDontSee('Retry payment');
    }

    private function pendingOrder(User $user): Order
    {
        return Order::factory()->create([
            'user_id' => $user->id,
            'email' => null,
            'status' => Order::STATUS_PENDING,
            'payment_status' => Order::PAYMENT_FAILED,
            'subtotal' => 100,
            'total' => 100,
            'currency' => 'INR',
        ]);
    }

    private function payment(Order $order, string $gatewayOrderId, string $status): Payment
    {
        return Payment::query()->create([
            'order_id' => $order->id,
            'gateway' => 'razorpay',
            'gateway_order_id' => $gatewayOrderId,
            'amount' => $order->total,
            'currency' => $order->currency,
            'status' => $status,
        ]);
    }
}
