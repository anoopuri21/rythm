<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use App\Notifications\AccountClosureLinkNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class AccountClosureTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_request_a_closure_link(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('account.closure.send'))
            ->assertRedirect()
            ->assertSessionHas('closure_success');

        Notification::assertSentTo($user, AccountClosureLinkNotification::class);
        $this->assertNotNull($user->fresh()->account_closure_token);
        $this->assertNull($user->fresh()->deactivated_at);
    }

    public function test_mail_failure_keeps_the_account_and_clears_the_token(): void
    {
        $user = User::factory()->create();
        $this->app->bind(\Illuminate\Notifications\Channels\MailChannel::class, function ($app) {
            return new class($app->make('mail.manager'), $app->make(\Illuminate\Mail\Markdown::class)) extends \Illuminate\Notifications\Channels\MailChannel
            {
                public function send($notifiable, $notification): void
                {
                    throw new TransportException('smtp down');
                }
            };
        });

        $this->actingAs($user)
            ->post(route('account.closure.send'))
            ->assertRedirect()
            ->assertSessionHas('closure_error');

        $this->assertNull($user->fresh()->account_closure_token);
        $this->assertNull($user->fresh()->deactivated_at);
    }

    public function test_staff_cannot_request_a_closure_link(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('account.closure.send'))
            ->assertForbidden();
    }

    public function test_opening_the_link_does_not_change_the_account(): void
    {
        $user = User::factory()->create();
        $url = $this->closureUrl($user);

        $this->actingAs($user)
            ->get($url)
            ->assertOk()
            ->assertSee('Deactivate or remove');

        $fresh = $user->fresh();
        $this->assertSame($user->email, $fresh->email);
        $this->assertNull($fresh->deactivated_at);
        $this->assertNull($fresh->removed_at);
    }

    public function test_link_rejects_a_different_signed_in_account_and_an_expired_token(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $url = $this->closureUrl($user);

        $this->actingAs($other)
            ->get($url)
            ->assertForbidden()
            ->assertSee('different account');

        $this->assertAuthenticatedAs($other);

        $user->forceFill(['account_closure_expires_at' => now()->subMinute()])->save();

        $this->actingAs($user)
            ->get($url)
            ->assertForbidden()
            ->assertSee('no longer valid');

        $this->assertNull($user->fresh()->deactivated_at);
    }

    public function test_link_rejects_a_changed_email_and_an_expired_signature(): void
    {
        $user = User::factory()->create();
        $url = $this->closureUrl($user);
        $user->forceFill(['email' => 'changed-'.$user->id.'@example.com'])->save();

        $this->actingAs($user)
            ->get($url)
            ->assertForbidden()
            ->assertSee('no longer valid');

        $again = User::factory()->create();
        $freshUrl = $this->closureUrl($again);
        $this->travel(61)->minutes();

        $this->actingAs($again)->get($freshUrl)->assertForbidden();
        $this->assertNull($again->fresh()->deactivated_at);
    }

    public function test_closure_requires_the_current_password(): void
    {
        $user = User::factory()->create();
        $url = $this->closureUrl($user);
        $deactivate = $this->actionUrl($user, $url, 'account.closure.deactivate');

        $this->actingAs($user)
            ->from($url)
            ->post($deactivate, ['current_password' => 'wrong-password'])
            ->assertSessionHasErrors('current_password');

        $this->assertAuthenticatedAs($user);
        $this->assertNull($user->fresh()->deactivated_at);
    }

    public function test_deactivate_blocks_the_next_login(): void
    {
        $user = User::factory()->create();
        $email = $user->email;
        $url = $this->closureUrl($user);

        $this->actingAs($user)
            ->post($this->actionUrl($user, $url, 'account.closure.deactivate'), [
                'current_password' => 'password',
            ])
            ->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertNotNull($user->fresh()->deactivated_at);
        $this->assertSame($email, $user->fresh()->email);

        $this->post('/login', [
            'email' => $email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_remove_anonymizes_the_customer_and_keeps_the_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
        $address = Address::factory()->create(['user_id' => $user->id]);
        $url = $this->closureUrl($user);

        $this->actingAs($user)
            ->post($this->actionUrl($user, $url, 'account.closure.remove'), [
                'current_password' => 'password',
            ])
            ->assertRedirect(route('login'));

        $this->assertGuest();
        $fresh = $user->fresh();
        $this->assertSame('Deleted customer', $fresh->name);
        $this->assertSame('deleted+'.$user->id.'@users.invalid', $fresh->email);
        $this->assertNotNull($fresh->removed_at);
        $this->assertNull($fresh->account_closure_token);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }

    private function closureUrl(User $user): string
    {
        Notification::fake();

        $this->actingAs($user)->post(route('account.closure.send'))->assertRedirect();

        $url = '';
        Notification::assertSentTo($user, AccountClosureLinkNotification::class, function (AccountClosureLinkNotification $notification) use (&$url): bool {
            $url = $notification->url;

            return true;
        });

        return $url;
    }

    private function actionUrl(User $user, string $showUrl, string $route): string
    {
        parse_str((string) parse_url($showUrl, PHP_URL_QUERY), $query);

        return URL::temporarySignedRoute($route, now()->addMinutes(60), [
            'user' => $user->id,
            'hash' => sha1($user->fresh()->email),
            'token' => $query['token'],
        ]);
    }
}
