<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Listeners\MarkNotificationDeliveryFailed;
use App\Listeners\MarkNotificationDeliverySent;
use App\Models\NotificationDelivery;
use App\Models\Order;
use App\Models\User;
use App\Notifications\CommerceOrderNotification;
use App\Services\CommerceNotificationService;
use App\Services\NotificationReconciliationService;
use App\Services\NotificationRetryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Tests\TestCase;

class NotificationOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivery_outcome_events_record_sent_and_redacted_failure_evidence(): void
    {
        [$user, , $delivery] = $this->delivery();
        $notification = $this->notification($delivery);

        app(MarkNotificationDeliveryFailed::class)->handle(new NotificationFailed(
            $user,
            $notification,
            'mail',
            ['exception' => new RuntimeException('private recipient detail')],
        ));
        $delivery->refresh();
        $this->assertSame(NotificationDelivery::STATUS_FAILED, $delivery->status);
        $this->assertSame(1, $delivery->attempts);
        $this->assertSame('Delivery failed: RuntimeException', $delivery->last_error);
        $this->assertStringNotContainsString('private recipient detail', $delivery->last_error);

        app(MarkNotificationDeliverySent::class)->handle(new NotificationSent($user, $notification, 'mail'));
        $delivery->refresh();
        $this->assertSame(NotificationDelivery::STATUS_SENT, $delivery->status);
        $this->assertSame(2, $delivery->attempts);
        $this->assertNotNull($delivery->sent_at);
        $this->assertNull($delivery->last_error);

        app(MarkNotificationDeliverySent::class)->handle(new NotificationSent($user, $notification, 'mail'));
        $this->assertSame(2, $delivery->fresh()->attempts);
    }

    public function test_only_known_failed_owned_delivery_retries_with_a_hard_cap(): void
    {
        Notification::fake();
        [$user, , $delivery] = $this->delivery();
        $delivery->update([
            'status' => NotificationDelivery::STATUS_FAILED,
            'attempts' => 1,
            'last_error' => 'Known failure',
            'failed_at' => now(),
        ]);

        $retried = app(NotificationRetryService::class)->retry($delivery);

        $this->assertSame(NotificationDelivery::STATUS_QUEUED, $retried->status);
        $this->assertNull($retried->last_error);
        Notification::assertSentTo($user, CommerceOrderNotification::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Only a delivery with a known failed outcome can be retried.');
        app(NotificationRetryService::class)->retry($retried);
    }

    public function test_exhausted_or_anonymous_deliveries_are_not_automatically_retried(): void
    {
        [, , $delivery] = $this->delivery();
        $delivery->update([
            'status' => NotificationDelivery::STATUS_FAILED,
            'attempts' => NotificationRetryService::MAX_ATTEMPTS,
        ]);

        try {
            app(NotificationRetryService::class)->retry($delivery);
            $this->fail('Exhausted delivery unexpectedly retried.');
        } catch (RuntimeException $exception) {
            $this->assertSame('The notification retry limit has been reached.', $exception->getMessage());
        }

        $delivery->update(['attempts' => 1, 'user_id' => null]);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Anonymous delivery retry requires manual recipient reconciliation.');
        app(NotificationRetryService::class)->retry($delivery->fresh());
    }

    public function test_reconciliation_is_read_only_bounded_and_reports_actionable_states(): void
    {
        [, , $failed] = $this->delivery('event-failed');
        $failed->update([
            'status' => NotificationDelivery::STATUS_FAILED,
            'attempts' => 1,
            'failed_at' => now(),
        ]);
        [, , $stale] = $this->delivery('event-stale');
        $stale->update(['queued_at' => now()->subMinutes(20)]);
        $before = NotificationDelivery::query()->get()->toArray();

        $report = app(NotificationReconciliationService::class)->scan(1);

        $this->assertSame(1, $report['scanned']);
        $this->assertTrue($report['truncated']);
        $this->assertSame('DELIVERY_FAILED', $report['findings'][0]['code']);
        $this->assertSame($before, NotificationDelivery::query()->get()->toArray());
        $this->artisan('notifications:reconcile', ['--json' => true])->assertFailed();
        $this->artisan('notifications:reconcile', ['--limit' => 501])->assertExitCode(2);
    }

    public function test_retry_command_queues_only_eligible_known_failures(): void
    {
        Notification::fake();
        [, , $eligible] = $this->delivery('eligible');
        $eligible->update(['status' => NotificationDelivery::STATUS_FAILED, 'attempts' => 1, 'failed_at' => now()]);
        [, , $exhausted] = $this->delivery('exhausted');
        $exhausted->update([
            'status' => NotificationDelivery::STATUS_FAILED,
            'attempts' => NotificationRetryService::MAX_ATTEMPTS,
            'failed_at' => now(),
        ]);

        $this->artisan('notifications:retry-failed', ['--limit' => 10])
            ->expectsOutputToContain('Queued 1')
            ->assertSuccessful();

        $this->assertSame(NotificationDelivery::STATUS_QUEUED, $eligible->fresh()->status);
        $this->assertSame(NotificationDelivery::STATUS_FAILED, $exhausted->fresh()->status);
    }

    public function test_delivery_admin_is_visible_only_to_support_authority(): void
    {
        $support = User::factory()->create(['role' => User::ROLE_SUPPORT]);
        $orderManager = User::factory()->create(['role' => User::ROLE_ORDER_MANAGER]);
        $this->delivery();

        $this->actingAs($support)->get('/admin/notification-deliveries')->assertOk();
        $this->actingAs($orderManager)->get('/admin/notification-deliveries')->assertForbidden();
    }

    /** @return array{User, Order, NotificationDelivery} */
    private function delivery(string $eventKey = 'order-delivery-test'): array
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'email' => $user->email]);
        $service = app(CommerceNotificationService::class);
        $event = $service->recordEvent($eventKey, 'order.confirmed', 'order', $order->id, [
            'order_number' => $order->order_number,
        ]);
        $delivery = $service->reserveDelivery(
            $event,
            $user,
            'mail',
            CommerceOrderNotification::class,
            $user->email,
        );

        return [$user, $order, $delivery];
    }

    private function notification(NotificationDelivery $delivery): CommerceOrderNotification
    {
        return new CommerceOrderNotification(
            $delivery,
            ['mail'],
            'Order confirmed',
            'Your order was confirmed.',
            route('home'),
        );
    }
}
