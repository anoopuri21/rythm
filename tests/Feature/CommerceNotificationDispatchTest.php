<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\CommerceNotificationRequested;
use App\Listeners\HandleCommerceNotification;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\CommerceOrderNotification;
use App\Payment\FakePaymentGateway;
use App\Payment\PaymentResult;
use App\Services\OrderService;
use App\Services\RefundService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CommerceNotificationDispatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_listener_reserves_mail_and_database_deliveries_once(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $order = $this->order($user);
        $requested = new CommerceNotificationRequested(
            "order:{$order->id}:confirmed",
            'order.confirmed',
            $order->id,
        );
        $listener = app(HandleCommerceNotification::class);

        $listener->handle($requested);
        $listener->handle($requested);

        $this->assertDatabaseCount('commerce_events', 1);
        $this->assertDatabaseCount('notification_deliveries', 2);
        Notification::assertSentToTimes($user, CommerceOrderNotification::class, 2);
        Notification::assertSentTo(
            $user,
            CommerceOrderNotification::class,
            fn ($notification, $channels): bool => $channels === ['mail']
                && $notification->viaQueues()['mail'] === 'emails'
        );
        Notification::assertSentTo(
            $user,
            CommerceOrderNotification::class,
            fn ($notification, $channels): bool => $channels === ['database']
        );
    }

    public function test_payment_and_order_services_emit_deterministic_events_once(): void
    {
        Event::fake([CommerceNotificationRequested::class]);
        $user = User::factory()->create();
        $order = $this->order($user);
        $payment = $this->payment($order, Payment::STATUS_INITIATED);

        app(OrderService::class)->markFailed($order, new PaymentResult(false, 'failed', message: 'Declined'));
        Event::assertDispatched(
            CommerceNotificationRequested::class,
            fn ($event): bool => $event->eventKey === "payment:{$payment->id}:failed"
                && $event->eventType === 'payment.failed'
        );

        $order->update(['payment_status' => Order::PAYMENT_UNPAID]);
        $payment->refresh()->update(['status' => Payment::STATUS_INITIATED]);
        app(OrderService::class)->markPaid(
            $order,
            new PaymentResult(true, 'paid', 'gateway-payment'),
            $payment->gateway_order_id,
        );
        app(OrderService::class)->markPaid(
            $order->fresh(),
            new PaymentResult(true, 'paid', 'gateway-payment'),
            $payment->gateway_order_id,
        );

        Event::assertDispatchedTimes(CommerceNotificationRequested::class, 2);
        Event::assertDispatched(
            CommerceNotificationRequested::class,
            fn ($event): bool => $event->eventKey === "order:{$order->id}:confirmed"
                && $event->eventType === 'order.confirmed'
        );
    }

    public function test_refund_service_emits_requested_and_completed_events(): void
    {
        Event::fake([CommerceNotificationRequested::class]);
        $finance = User::factory()->create(['role' => User::ROLE_FINANCE]);
        $order = $this->order($finance, paid: true);
        $payment = $this->payment($order, Payment::STATUS_PAID);
        $service = app(RefundService::class);

        $refund = $service->request($payment, 25, 'Approved notification refund', $finance, 'notification-refund');
        $service->process($refund, app(FakePaymentGateway::class), $finance);

        Event::assertDispatched(
            CommerceNotificationRequested::class,
            fn ($event): bool => $event->eventKey === "refund:{$refund->id}:requested"
                && $event->eventType === 'refund.requested'
        );
        Event::assertDispatched(
            CommerceNotificationRequested::class,
            fn ($event): bool => $event->eventKey === "refund:{$refund->id}:completed"
                && $event->eventType === 'refund.completed'
        );
    }

    private function order(User $user, bool $paid = false): Order
    {
        return Order::factory()->create([
            'user_id' => $user->id,
            'email' => $user->email,
            'status' => $paid ? Order::STATUS_CONFIRMED : Order::STATUS_PENDING,
            'payment_status' => $paid ? Order::PAYMENT_PAID : Order::PAYMENT_UNPAID,
            'subtotal' => 100,
            'total' => 100,
            'currency' => 'INR',
        ]);
    }

    private function payment(Order $order, string $status): Payment
    {
        return Payment::query()->create([
            'order_id' => $order->id,
            'gateway' => 'razorpay',
            'gateway_order_id' => 'gateway-order-'.$order->id,
            'gateway_payment_id' => $status === Payment::STATUS_PAID ? 'gateway-payment-'.$order->id : null,
            'amount' => 100,
            'currency' => 'INR',
            'status' => $status,
        ]);
    }
}
