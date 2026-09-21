<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\PaymentGatewaySetting;
use App\Models\User;
use App\Services\PaymentSettingsService;
use App\Support\AdminAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RazorpaySettingsAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_super_admin_can_open_razorpay_settings(): void
    {
        $super = $this->staff(User::ROLE_SUPER_ADMIN);
        $this->actingAsAdmin($super)->get('/admin/razorpay-settings')->assertOk();

        foreach ([
            User::ROLE_ADMIN,
            User::ROLE_FINANCE,
            User::ROLE_SUPPORT,
            User::ROLE_MARKETING,
            User::ROLE_ORDER_MANAGER,
        ] as $role) {
            $this->actingAsAdmin($this->staff($role))
                ->get('/admin/razorpay-settings')
                ->assertForbidden();
        }
    }

    public function test_secrets_are_encrypted_and_not_returned_to_the_browser(): void
    {
        $super = $this->staff(User::ROLE_SUPER_ADMIN);
        $this->actingAsAdmin($super);

        app(PaymentSettingsService::class)->saveRazorpay([
            'mode' => 'test',
            'test_key_id' => 'rzp_test_publicid',
            'test_key_secret' => 'super-secret-test-key',
            'test_webhook_secret' => 'super-secret-webhook',
            'live_key_id' => '',
        ]);

        $row = PaymentGatewaySetting::query()->firstOrFail();
        $raw = $row->getRawOriginal('test_key_secret');
        $this->assertNotSame('super-secret-test-key', $raw);
        $this->assertSame('super-secret-test-key', $row->test_key_secret);

        $this->get('/admin/razorpay-settings')
            ->assertOk()
            ->assertSee('rzp_test_publicid', false)
            ->assertDontSee('super-secret-test-key', false)
            ->assertDontSee('super-secret-webhook', false);

        $this->assertTrue($super->hasAdminPermission(AdminAccess::PAYMENTS_MANAGE));
        $this->assertFalse($this->staff(User::ROLE_ADMIN)->hasAdminPermission(AdminAccess::PAYMENTS_MANAGE));
    }

    public function test_active_mode_selects_the_matching_key_pair(): void
    {
        app(PaymentSettingsService::class)->saveRazorpay([
            'mode' => 'live',
            'test_key_id' => 'rzp_test_aaa',
            'test_key_secret' => 'test-secret',
            'live_key_id' => 'rzp_live_bbb',
            'live_key_secret' => 'live-secret',
        ]);

        $this->assertSame('rzp_live_bbb', app(PaymentSettingsService::class)->publicKeyId());
        $this->assertSame('live-secret', config('services.razorpay.key_secret'));
    }

    private function staff(string $role): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role])->save();

        return $user;
    }
}
