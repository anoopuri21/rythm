<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AdminAuditLog;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\AdminAuditService;
use App\Support\AdminAccess;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LogicException;
use Tests\TestCase;

class AdminGovernanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_approved_staff_roles_can_access_the_admin_panel(): void
    {
        $panel = Filament::getPanel('admin');

        foreach (AdminAccess::staffRoles() as $role) {
            $user = User::factory()->create()->forceFill(['role' => $role]);
            $this->assertTrue($user->canAccessPanel($panel), $role);
        }

        $this->assertFalse(User::factory()->create()->canAccessPanel($panel));
    }

    public function test_role_permission_matrix_is_least_privilege_and_deny_by_default(): void
    {
        $catalogue = $this->staff(User::ROLE_CATALOGUE_MANAGER);
        $orders = $this->staff(User::ROLE_ORDER_MANAGER);
        $support = $this->staff(User::ROLE_SUPPORT);
        $marketing = $this->staff(User::ROLE_MARKETING);
        $finance = $this->staff(User::ROLE_FINANCE);

        $this->assertTrue($catalogue->hasAdminPermission(AdminAccess::CATALOGUE_MANAGE));
        $this->assertFalse($catalogue->hasAdminPermission(AdminAccess::ORDERS_VIEW));
        $this->assertTrue($orders->hasAdminPermission(AdminAccess::ORDERS_MANAGE));
        $this->assertFalse($orders->hasAdminPermission(AdminAccess::FINANCE_MANAGE));
        $this->assertTrue($support->hasAdminPermission(AdminAccess::INTERACTIONS_MANAGE));
        $this->assertFalse($support->hasAdminPermission(AdminAccess::CONTENT_MANAGE));
        $this->assertTrue($marketing->hasAdminPermission(AdminAccess::MARKETING_MANAGE));
        $this->assertFalse($marketing->hasAdminPermission(AdminAccess::CUSTOMERS_VIEW));
        $this->assertTrue($finance->hasAdminPermission(AdminAccess::FINANCE_MANAGE));
        $this->assertFalse($finance->hasAdminPermission(AdminAccess::ORDERS_MANAGE));
        $this->assertFalse(User::factory()->create()->hasAdminPermission(AdminAccess::CATALOGUE_VIEW));
    }

    public function test_every_staff_role_matches_the_complete_permission_contract(): void
    {
        $permissions = [
            AdminAccess::CATALOGUE_VIEW, AdminAccess::CATALOGUE_MANAGE,
            AdminAccess::ORDERS_VIEW, AdminAccess::ORDERS_MANAGE,
            AdminAccess::CUSTOMERS_VIEW, AdminAccess::INTERACTIONS_MANAGE,
            AdminAccess::CONTENT_MANAGE, AdminAccess::MARKETING_MANAGE,
            AdminAccess::FINANCE_VIEW, AdminAccess::FINANCE_MANAGE,
            AdminAccess::SETTINGS_MANAGE, AdminAccess::STAFF_MANAGE,
            AdminAccess::AUDIT_VIEW, AdminAccess::NOTIFICATIONS_VIEW,
        ];
        $all = $permissions;
        $expected = [
            User::ROLE_SUPER_ADMIN => $all,
            User::ROLE_ADMIN => $all,
            User::ROLE_CATALOGUE_MANAGER => [AdminAccess::CATALOGUE_VIEW, AdminAccess::CATALOGUE_MANAGE],
            User::ROLE_ORDER_MANAGER => [AdminAccess::ORDERS_VIEW, AdminAccess::ORDERS_MANAGE, AdminAccess::CUSTOMERS_VIEW, AdminAccess::CATALOGUE_VIEW],
            User::ROLE_SUPPORT => [AdminAccess::ORDERS_VIEW, AdminAccess::CUSTOMERS_VIEW, AdminAccess::INTERACTIONS_MANAGE, AdminAccess::CATALOGUE_VIEW, AdminAccess::NOTIFICATIONS_VIEW],
            User::ROLE_MARKETING => [AdminAccess::CATALOGUE_VIEW, AdminAccess::CONTENT_MANAGE, AdminAccess::MARKETING_MANAGE],
            User::ROLE_FINANCE => [AdminAccess::ORDERS_VIEW, AdminAccess::CUSTOMERS_VIEW, AdminAccess::FINANCE_VIEW, AdminAccess::FINANCE_MANAGE],
        ];

        foreach ($expected as $role => $granted) {
            $user = $this->staff($role);
            foreach ($permissions as $permission) {
                $this->assertSame(
                    in_array($permission, $granted, true),
                    $user->hasAdminPermission($permission),
                    "Unexpected {$permission} result for {$role}",
                );
            }
        }
    }

    public function test_model_authorization_uses_the_central_matrix(): void
    {
        $catalogue = $this->staff(User::ROLE_CATALOGUE_MANAGER);
        $support = $this->staff(User::ROLE_SUPPORT);
        $finance = $this->staff(User::ROLE_FINANCE);

        $this->assertTrue($catalogue->can('viewAny', Product::class));
        $this->assertTrue($catalogue->can('create', Product::class));
        $this->assertFalse($catalogue->can('viewAny', Order::class));
        $this->assertTrue($support->can('update', new Review));
        $this->assertFalse($support->can('create', Coupon::class));
        $this->assertTrue($finance->can('viewAny', Order::class));
        $this->assertFalse($finance->can('update', new Order));
    }

    public function test_direct_admin_routes_hide_unassigned_modules(): void
    {
        $catalogue = $this->staff(User::ROLE_CATALOGUE_MANAGER);
        $this->actingAs($catalogue)->get('/admin/products')->assertOk();
        $this->actingAs($catalogue)->get('/admin/orders')->assertForbidden();
        $this->actingAs($catalogue)->get('/admin/settings')->assertForbidden();
        $this->actingAs($catalogue)->get('/admin/admin-audit-logs')->assertForbidden();
        $this->actingAs($catalogue)->get('/admin/staff')->assertForbidden();

        $superAdmin = $this->staff(User::ROLE_SUPER_ADMIN);
        $this->actingAs($superAdmin)->get('/admin/admin-audit-logs')->assertOk();
        $this->actingAs($superAdmin)->get('/admin/staff')->assertOk();
        $this->actingAs($superAdmin)->get('/admin/settings')->assertOk();
    }

    public function test_final_super_admin_cannot_be_demoted(): void
    {
        $superAdmin = $this->staff(User::ROLE_SUPER_ADMIN);

        try {
            $superAdmin->forceFill(['role' => User::ROLE_SUPPORT])->save();
            $this->fail('The final Super Admin demotion should be rejected.');
        } catch (\DomainException $exception) {
            $this->assertSame('The final Super Admin cannot be demoted.', $exception->getMessage());
        }

        $this->assertSame(User::ROLE_SUPER_ADMIN, $superAdmin->fresh()->role);

        $this->staff(User::ROLE_SUPER_ADMIN);
        $superAdmin->forceFill(['role' => User::ROLE_SUPPORT])->save();
        $this->assertSame(User::ROLE_SUPPORT, $superAdmin->fresh()->role);
    }

    public function test_audit_records_are_redacted_at_rest_and_immutable(): void
    {
        $actor = $this->staff(User::ROLE_SUPER_ADMIN);
        $product = Product::factory()->create();

        $audit = app(AdminAuditService::class)->record(
            $actor,
            'product.price.changed',
            $product,
            ['price' => '100.00', 'api_token' => 'old-secret'],
            ['price' => '120.00', 'nested' => ['payment_signature' => 'signature']],
            'Approved price correction',
        );

        $this->assertSame('[REDACTED]', $audit->before_values['api_token']);
        $this->assertSame('[REDACTED]', $audit->after_values['nested']['payment_signature']);
        $this->assertSame('100.00', $audit->before_values['price']);
        $this->assertSame($product->getMorphClass(), $audit->subject_type);
        $this->assertSame($product->id, $audit->subject_id);
        $this->assertSame('Approved price correction', $audit->reason);
        $this->assertDatabaseCount('admin_audit_logs', 1);

        $this->expectException(LogicException::class);
        $audit->delete();
    }

    public function test_sensitive_admin_changes_are_automatically_audited_with_bounded_values(): void
    {
        $actor = $this->staff(User::ROLE_SUPER_ADMIN);
        $product = Product::factory()->create(['price' => 100, 'stock' => 4]);
        $this->actingAs($actor);

        $product->update(['price' => 120, 'stock' => 3, 'name' => 'Audited product name']);
        $audit = AdminAuditLog::sole();
        $this->assertSame('product.updated', $audit->action);
        $this->assertSame(['name', 'price', 'stock'], array_keys($audit->before_values));
        $this->assertSame('100.00', $audit->before_values['price']);
        $this->assertSame('120', (string) $audit->after_values['price']);
        $this->assertSame('Audited product name', $audit->after_values['name']);

        SiteSetting::create(['key' => 'provider_api_secret', 'value' => 'old'])->update(['value' => 'new-secret']);
        $settingAudit = AdminAuditLog::query()->where('action', 'site_setting.updated')->sole();
        $this->assertSame('[REDACTED]', $settingAudit->before_values['value']);
        $this->assertSame('[REDACTED]', $settingAudit->after_values['value']);
    }

    public function test_filament_totp_provider_is_configured_with_encrypted_recovery_data(): void
    {
        $panel = Filament::getPanel('admin');
        $providers = $panel->getMultiFactorAuthenticationProviders();
        $this->assertArrayHasKey('app', $providers);
        $this->assertInstanceOf(AppAuthentication::class, $providers['app']);
        $this->assertTrue($providers['app']->isRecoverable());

        $user = $this->staff(User::ROLE_SUPPORT);
        $user->saveAppAuthenticationSecret('TOP-SECRET-TOTP-SEED');
        $user->saveAppAuthenticationRecoveryCodes(['hashed-code-one', 'hashed-code-two']);
        $user->refresh();

        $this->assertSame('TOP-SECRET-TOTP-SEED', $user->getAppAuthenticationSecret());
        $this->assertSame(['hashed-code-one', 'hashed-code-two'], $user->getAppAuthenticationRecoveryCodes());
        $raw = DB::table('users')->where('id', $user->id)->first();
        $this->assertNotSame('TOP-SECRET-TOTP-SEED', $raw->app_authentication_secret);
        $this->assertStringNotContainsString('hashed-code-one', $raw->app_authentication_recovery_codes);
        $this->assertArrayNotHasKey('app_authentication_secret', $user->toArray());
        $this->assertArrayNotHasKey('app_authentication_recovery_codes', $user->toArray());
    }

    public function test_governance_schema_has_queryable_audit_indexes_and_no_updated_at(): void
    {
        $this->assertTrue(Schema::hasColumns('admin_audit_logs', [
            'actor_id', 'action', 'subject_type', 'subject_id', 'reason', 'before_values',
            'after_values', 'ip_hash', 'user_agent', 'request_id', 'created_at',
        ]));
        $this->assertFalse(Schema::hasColumn('admin_audit_logs', 'updated_at'));
        $this->assertSame(0, AdminAuditLog::count());
    }

    private function staff(string $role): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role])->save();

        return $user;
    }
}
