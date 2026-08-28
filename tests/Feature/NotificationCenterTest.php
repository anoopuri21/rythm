<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_center_requires_authentication_and_is_noindex(): void
    {
        $this->get(route('account.notifications.index'))->assertRedirect(route('login'));

        $user = User::factory()->create();
        $this->actingAs($user)->get(route('account.notifications.index'))
            ->assertOk()
            ->assertSee('Notifications')
            ->assertSee('Optional preferences')
            ->assertSee('essential order notifications always remain enabled')
            ->assertSee('<meta name="robots" content="noindex, follow">', false);
    }

    public function test_center_is_owned_paginated_and_shows_truthful_unread_count(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        foreach (range(1, 13) as $number) {
            $this->notification($user, "Owned update {$number}", read: $number === 1, sequence: $number);
        }
        $this->notification($other, 'Other customer private update');

        $this->actingAs($user)->get(route('account.notifications.index'))
            ->assertOk()
            ->assertSee('12</strong> unread', false)
            ->assertSee('Owned update 13')
            ->assertDontSee('>Owned update 1<', false)
            ->assertDontSee('Other customer private update')
            ->assertSee('Notification pages');

        $this->actingAs($user)->get(route('account.notifications.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('>Owned update 1<', false)
            ->assertDontSee('Other customer private update');
    }

    public function test_owner_can_mark_one_read_unread_and_all_read(): void
    {
        $user = User::factory()->create();
        $first = $this->notification($user, 'First unread');
        $second = $this->notification($user, 'Second unread');

        $this->actingAs($user)->patch(route('account.notifications.read', $first->id))
            ->assertRedirect()
            ->assertSessionHas('notification_status');
        $this->assertNotNull($first->fresh()->read_at);
        $this->assertNull($second->fresh()->read_at);

        $this->patch(route('account.notifications.unread', $first->id))->assertRedirect();
        $this->assertNull($first->fresh()->read_at);

        $this->patch(route('account.notifications.read-all'))->assertRedirect();
        $this->assertSame(0, $user->unreadNotifications()->count());
    }

    public function test_customer_cannot_change_another_customers_notification(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $notification = $this->notification($owner, 'Private update');

        $this->actingAs($other)
            ->patch(route('account.notifications.read', $notification->id))
            ->assertNotFound();
        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_customer_can_update_only_approved_optional_preferences(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('account.notifications.preferences'), [
            'preferences' => [
                NotificationPreference::CATEGORY_ORDER_UPDATES => [
                    'email_enabled' => false,
                    'database_enabled' => true,
                ],
                NotificationPreference::CATEGORY_PRODUCT_UPDATES => [
                    'email_enabled' => false,
                    'database_enabled' => false,
                ],
                'payment_security' => [
                    'email_enabled' => false,
                    'database_enabled' => false,
                ],
            ],
        ])->assertRedirect()->assertSessionHas('preference_status');

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $user->id,
            'category' => NotificationPreference::CATEGORY_ORDER_UPDATES,
            'email_enabled' => false,
            'database_enabled' => true,
        ]);
        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $user->id,
            'category' => NotificationPreference::CATEGORY_PRODUCT_UPDATES,
            'email_enabled' => false,
            'database_enabled' => false,
        ]);
        $this->assertDatabaseMissing('notification_preferences', [
            'user_id' => $user->id,
            'category' => 'payment_security',
        ]);
    }

    private function notification(User $user, string $title, bool $read = false, int $sequence = 0): DatabaseNotification
    {
        return DatabaseNotification::query()->create([
            'id' => (string) Str::uuid(),
            'type' => 'test.notification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => [
                'title' => $title,
                'message' => 'A bounded transactional update.',
            ],
            'read_at' => $read ? now() : null,
            'created_at' => now()->addSeconds($sequence),
            'updated_at' => now()->addSeconds($sequence),
        ]);
    }
}
