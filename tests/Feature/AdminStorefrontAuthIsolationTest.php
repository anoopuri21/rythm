<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * Admin panel uses guard "admin"; storefront uses "web".
 * Logging into one must not authenticate the other.
 */
class AdminStorefrontAuthIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_panel_uses_dedicated_admin_guard(): void
    {
        $source = file_get_contents(base_path('app/Providers/Filament/AdminPanelProvider.php'));
        $this->assertNotFalse($source);
        $this->assertStringContainsString("authGuard('admin')", $source);
        $this->assertStringContainsString('UseAdminAuthGuard', $source);

        $this->assertArrayHasKey('admin', config('auth.guards'));
        $this->assertSame('session', config('auth.guards.admin.driver'));
        $this->assertSame('users', config('auth.guards.admin.provider'));
    }

    public function test_admin_guard_login_does_not_authenticate_storefront_web_guard(): void
    {
        $admin = User::where('email', 'admin@rythme.test')->firstOrFail();

        Auth::guard('admin')->login($admin);

        $this->assertTrue(Auth::guard('admin')->check());
        $this->assertTrue(Auth::guard('admin')->user()->is($admin));
        $this->assertFalse(Auth::guard('web')->check());
        $this->assertNull(Auth::guard('web')->user());
    }

    public function test_web_guard_login_does_not_authenticate_admin_guard(): void
    {
        $customer = User::where('email', 'test@example.com')->firstOrFail();

        Auth::guard('web')->login($customer);

        $this->assertTrue(Auth::guard('web')->check());
        $this->assertFalse(Auth::guard('admin')->check());
    }

    public function test_admin_session_does_not_open_storefront_account(): void
    {
        $admin = User::where('email', 'admin@rythme.test')->firstOrFail();

        // Staff on admin guard only; default guard remains web (empty).
        $this->actingAsAdmin($admin);

        $this->assertTrue(Auth::guard('admin')->check());
        $this->assertFalse(Auth::guard('web')->check());
        $this->assertSame('web', Auth::getDefaultDriver());

        $this->get(route('account.index'))
            ->assertRedirect(route('login'));
    }

    public function test_customer_session_cannot_open_admin_panel(): void
    {
        $customer = User::where('email', 'test@example.com')->firstOrFail();

        $this->actingAs($customer, 'web')
            ->get('/admin')
            ->assertRedirect(); // Filament login
    }

    public function test_storefront_logout_only_clears_web_guard(): void
    {
        $admin = User::where('email', 'admin@rythme.test')->firstOrFail();
        $customer = User::where('email', 'test@example.com')->firstOrFail();

        Auth::guard('admin')->login($admin);
        Auth::guard('web')->login($customer);

        $this->post(route('logout'))->assertRedirect('/');

        $this->assertFalse(Auth::guard('web')->check());
        $this->assertTrue(Auth::guard('admin')->check());
        $this->assertTrue(Auth::guard('admin')->user()->is($admin));
    }
}
