<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\NotificationDelivery;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Services\CommerceNotificationService;
use App\Services\NotificationPreferenceService;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class NotificationDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_schema_has_durable_event_delivery_preference_and_inbox_tables(): void
    {
        $this->assertTrue(Schema::hasColumns('commerce_events', [
            'event_key', 'event_type', 'aggregate_type', 'aggregate_id', 'payload_hash', 'metadata', 'occurred_at',
        ]));
        $this->assertTrue(Schema::hasColumns('notification_deliveries', [
            'commerce_event_id', 'user_id', 'delivery_key', 'channel', 'recipient_hash', 'status', 'attempts',
        ]));
        $this->assertTrue(Schema::hasColumns('notification_preferences', [
            'user_id', 'category', 'email_enabled', 'database_enabled',
        ]));
        $this->assertTrue(Schema::hasColumns('notifications', ['id', 'type', 'notifiable_type', 'notifiable_id', 'data', 'read_at']));
    }

    public function test_commerce_events_are_idempotent_immutable_and_reject_identity_collision(): void
    {
        $service = app(CommerceNotificationService::class);
        $event = $service->recordEvent('order:42:confirmed', 'order.confirmed', 'order', 42, [
            'order_number' => 'RYM-42',
            'status' => 'confirmed',
            'email' => 'must-not-be-stored@example.test',
            'secret' => 'must-not-be-stored',
        ]);
        $same = $service->recordEvent('order:42:confirmed', 'order.confirmed', 'order', 42, [
            'order_number' => 'RYM-42',
            'status' => 'confirmed',
        ]);

        $this->assertTrue($event->is($same));
        $this->assertSame(['order_number' => 'RYM-42', 'status' => 'confirmed'], $event->metadata);
        $this->assertDatabaseCount('commerce_events', 1);

        try {
            $event->update(['event_type' => 'changed']);
            $this->fail('Immutable event unexpectedly changed.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Commerce events are immutable.', $exception->getMessage());
        }

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Commerce event identity was reused with different data.');
        $service->recordEvent('order:42:confirmed', 'order.confirmed', 'order', 42, ['status' => 'cancelled']);
    }

    public function test_delivery_identity_suppresses_duplicates_and_hashes_recipient(): void
    {
        $user = User::factory()->create(['email' => 'customer@example.test']);
        $service = app(CommerceNotificationService::class);
        $event = $service->recordEvent('order:50:shipped', 'order.shipped', 'order', 50);

        $first = $service->reserveDelivery($event, $user, 'mail', 'order-status', $user->email);
        $same = $service->reserveDelivery($event, $user, 'mail', 'order-status', $user->email);
        $database = $service->reserveDelivery($event, $user, 'database', 'order-status', (string) $user->id);

        $this->assertTrue($first->is($same));
        $this->assertFalse($first->is($database));
        $this->assertSame(hash('sha256', 'customer@example.test'), $first->recipient_hash);
        $this->assertSame(NotificationDelivery::STATUS_QUEUED, $first->status);
        $this->assertDatabaseCount('notification_deliveries', 2);
        $this->assertStringNotContainsString('customer@example.test', json_encode($first->getAttributes(), JSON_THROW_ON_ERROR));
    }

    public function test_only_optional_preferences_can_change_and_transactional_channels_stay_enabled(): void
    {
        $user = User::factory()->create();
        $service = app(NotificationPreferenceService::class);
        $preference = $service->set(
            $user,
            NotificationPreference::CATEGORY_PRODUCT_UPDATES,
            false,
            true,
        );

        $this->assertFalse($preference->email_enabled);
        $this->assertTrue($preference->database_enabled);
        $this->assertFalse($service->channelEnabled($user, NotificationPreference::CATEGORY_PRODUCT_UPDATES, 'mail'));
        $this->assertTrue($service->channelEnabled($user, 'payment_security', 'mail'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Mandatory transactional notifications cannot be disabled or configured.');
        $service->set($user, 'payment_security', false, false);
    }

    public function test_standard_database_notification_storage_is_operational(): void
    {
        $user = User::factory()->create();
        $user->notify(new DomainDatabaseNotification);

        $this->assertSame(1, $user->notifications()->count());
        $this->assertSame('Domain notification', $user->notifications()->firstOrFail()->data['title']);
    }
}

final class DomainDatabaseNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return ['title' => 'Domain notification'];
    }
}
