<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\VerifyMailFromAddressMail;
use App\Services\MailSenderSettingsService;
use App\Services\SiteSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class MailSenderSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_settings_page_shows_outbound_email_section(): void
    {
        $admin = \App\Models\User::where('email', 'admin@rythme.test')->firstOrFail();

        $this->actingAsAdmin($admin)
            ->get('/admin/settings')
            ->assertOk()
            ->assertSee('Outbound email')
            ->assertSee('Sender email address', false);
    }

    public function test_route_mail_from_verify_is_registered(): void
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('mail-from.verify'));
    }

    public function test_saving_sender_email_sends_verification_and_does_not_go_live_yet(): void
    {
        Mail::fake();

        $mail = app(MailSenderSettingsService::class);
        $result = $mail->saveFromAdmin([
            'mail_from_address' => 'noreply@example.com',
            'mail_from_name' => 'Rhythm Store',
        ]);

        $this->assertSame('verification_sent', $result['status']);
        $this->assertFalse($mail->isVerified());
        $this->assertSame('noreply@example.com', $mail->pendingAddress());
        $this->assertSame('', $mail->liveAddress());

        Mail::assertSent(VerifyMailFromAddressMail::class, function (VerifyMailFromAddressMail $m): bool {
            return $m->pendingAddress === 'noreply@example.com'
                && str_contains($m->confirmUrl, '/mail/from/verify');
        });

        // Env/config still used until verified.
        $from = $mail->effectiveFrom();
        $this->assertNotSame('noreply@example.com', $from['address']);
    }

    public function test_signed_confirmation_activates_sender(): void
    {
        Mail::fake();

        $mail = app(MailSenderSettingsService::class);
        $mail->saveFromAdmin([
            'mail_from_address' => 'orders@example.com',
            'mail_from_name' => 'Orders Desk',
        ]);

        $settings = app(SiteSettingsService::class);
        $hash = (string) $settings->get('mail_from_pending_token');
        $this->assertNotSame('', $hash);

        // Recover plain token by re-sending through controlled start is hard; confirm via service with known token.
        $plain = 'test-plain-token-for-mail-from-verify-abcdef';
        $settings->put('mail_from_pending_token', hash('sha256', $plain));
        $settings->put('mail_from_pending_sent_at', now()->toIso8601String());

        $url = URL::temporarySignedRoute('mail-from.verify', now()->addHour(), ['token' => $plain]);

        $this->get($url)
            ->assertRedirect(route('home'))
            ->assertSessionHas('mail_from_status');

        $mail = app(MailSenderSettingsService::class);
        $this->assertTrue($mail->isVerified());
        $this->assertSame('orders@example.com', $mail->liveAddress());
        $this->assertSame('Orders Desk', $mail->liveName());
        $this->assertSame('', $mail->pendingAddress());
        $this->assertSame('orders@example.com', $mail->effectiveFrom()['address']);
    }

    public function test_invalid_token_does_not_activate(): void
    {
        Mail::fake();
        $mail = app(MailSenderSettingsService::class);
        $mail->saveFromAdmin(['mail_from_address' => 'x@example.com', 'mail_from_name' => 'X']);

        $url = URL::temporarySignedRoute('mail-from.verify', now()->addHour(), ['token' => 'wrong-token']);
        $this->get($url)->assertRedirect(route('home'));

        $this->assertFalse(app(MailSenderSettingsService::class)->isVerified());
    }

    public function test_clearing_sender_falls_back_to_env(): void
    {
        Mail::fake();
        $mail = app(MailSenderSettingsService::class);
        $mail->saveFromAdmin(['mail_from_address' => 'a@example.com', 'mail_from_name' => 'A']);
        $settings = app(SiteSettingsService::class);
        $plain = 'clear-test-token-xyz';
        $settings->put('mail_from_pending_token', hash('sha256', $plain));
        $settings->put('mail_from_pending_sent_at', now()->toIso8601String());
        $mail->confirmPending($plain);
        $this->assertTrue($mail->isVerified());

        $envAddress = (string) config('mail.from_bootstrap.address', config('mail.from.address'));

        $result = $mail->saveFromAdmin(['mail_from_address' => '', 'mail_from_name' => '']);
        $this->assertSame('cleared', $result['status']);
        $this->assertFalse($mail->isVerified());
        $this->assertSame('', $mail->liveAddress());
        $this->assertSame($envAddress, $mail->effectiveFrom()['address']);
    }
}
