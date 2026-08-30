<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Address;
use App\Models\BackInStockSubscription;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\BackInStockSubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::where('email', 'test@example.com')->firstOrFail();
        $this->actingAs($this->user);
    }

    public function test_account_requires_login(): void
    {
        auth()->logout();

        $this->get('/account')->assertRedirect('/login');
    }

    public function test_account_page_renders_tabs(): void
    {
        $this->get('/account')
            ->assertOk()
            ->assertSee('My Account')
            ->assertSee('Overview', escape: false)
            ->assertSee('Orders', escape: false)
            ->assertSee('Addresses', escape: false)
            ->assertSee('Stock alerts', escape: false)
            ->assertSee('Settings', escape: false)
            ->assertSee($this->user->name);
    }

    public function test_account_page_is_noindex_for_seo(): void
    {
        $this->get('/account')
            ->assertOk()
            ->assertSee('name="robots" content="noindex, follow"', escape: false);
    }

    public function test_profile_update_works(): void
    {
        $this->patch('/account/profile', [
            'name' => 'New Name',
            'email' => 'newname@example.com',
        ])->assertRedirect()->assertSessionHas('profile_success');

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'New Name',
            'email' => 'newname@example.com',
        ]);
    }

    public function test_changing_email_requires_reverification(): void
    {
        $this->user->forceFill(['email_verified_at' => now()])->save();

        $this->patch('/account/profile', [
            'name' => $this->user->name,
            'email' => 'verify-again@example.com',
        ])->assertRedirect()->assertSessionHas('profile_success');

        $this->assertNull($this->user->fresh()->email_verified_at);
    }

    public function test_keeping_email_preserves_verification(): void
    {
        $verifiedAt = now()->startOfSecond();
        $this->user->forceFill(['email_verified_at' => $verifiedAt])->save();

        $this->patch('/account/profile', [
            'name' => 'Updated Name',
            'email' => $this->user->email,
        ])->assertRedirect();

        $this->assertTrue($this->user->fresh()->email_verified_at->equalTo($verifiedAt));
    }

    public function test_profile_email_must_be_unique(): void
    {
        $admin = User::where('email', 'admin@rythme.test')->firstOrFail();

        $this->patch('/account/profile', [
            'name' => 'Test User',
            'email' => $admin->email,
        ])->assertSessionHasErrors('email');
    }

    public function test_password_change_requires_correct_current(): void
    {
        $this->patch('/account/password', [
            'current_password' => 'wrong-password',
            'password' => 'newsecret123',
            'password_confirmation' => 'newsecret123',
        ])->assertSessionHasErrors('current_password');
    }

    public function test_password_change_works(): void
    {
        $this->patch('/account/password', [
            'current_password' => 'password',
            'password' => 'newsecret123',
            'password_confirmation' => 'newsecret123',
        ])->assertRedirect()->assertSessionHas('password_success');

        $this->assertTrue(
            Hash::check('newsecret123', $this->user->fresh()->password)
        );
    }

    public function test_address_store_and_delete(): void
    {
        $this->post('/account/addresses', [
            'name' => 'Anoop Puri',
            'phone' => '9876543210',
            'line1' => '42, Music Lane',
            'city' => 'New Delhi',
            'state' => 'Delhi',
            'pincode' => '110001',
            'is_default' => 1,
        ])->assertRedirect()->assertSessionHas('address_success');

        $address = Address::where('user_id', $this->user->id)->firstOrFail();
        $this->assertTrue($address->is_default);

        $this->delete('/account/addresses/'.$address->id)->assertRedirect();
        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }

    public function test_address_update_default_and_default_replacement(): void
    {
        $first = Address::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'First Address',
            'is_default' => true,
        ]);
        $second = Address::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Second Address',
            'is_default' => false,
        ]);

        $this->patch(route('account.addresses.update', $second), [
            'name' => 'Updated Address',
            'phone' => '9876543210',
            'line1' => '99 Music Road',
            'city' => 'Delhi',
            'state' => 'Delhi',
            'pincode' => '110001',
        ])->assertRedirect()->assertSessionHas('address_success');

        $this->assertSame('Updated Address', $second->fresh()->name);

        $this->patch(route('account.addresses.default', $second))
            ->assertRedirect()
            ->assertSessionHas('address_success');
        $this->assertFalse($first->fresh()->is_default);
        $this->assertTrue($second->fresh()->is_default);

        $this->delete(route('account.addresses.destroy', $second))->assertRedirect();
        $this->assertTrue($first->fresh()->is_default);
    }

    public function test_cannot_update_or_default_others_address(): void
    {
        $other = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $other->id]);

        $this->patch(route('account.addresses.update', $address), [
            'name' => 'Tampered',
            'phone' => '9876543210',
            'line1' => '99 Music Road',
            'city' => 'Delhi',
            'state' => 'Delhi',
            'pincode' => '110001',
        ])->assertForbidden();
        $this->patch(route('account.addresses.default', $address))->assertForbidden();
    }

    public function test_cannot_delete_others_address(): void
    {
        $other = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $other->id]);

        $this->delete('/account/addresses/'.$address->id)->assertForbidden();
        $this->assertDatabaseHas('addresses', ['id' => $address->id]);
    }

    public function test_account_lists_and_cancels_owned_stock_alert(): void
    {
        $product = Product::factory()->create([
            'category_id' => Category::firstOrFail()->id,
            'brand_id' => Brand::firstOrFail()->id,
            'slug' => 'account-stock-alert',
            'sku' => 'RYM-ACCOUNT-STOCK',
            'stock' => 0,
        ]);
        $subscription = app(BackInStockSubscriptionService::class)->subscribe(
            $this->user,
            $product,
            null,
            true,
        );

        $this->get('/account')
            ->assertOk()
            ->assertSee('Stock alerts')
            ->assertSee($product->name)
            ->assertSee('Cancel');

        $this->delete(route('account.stock-alerts.destroy', $subscription))
            ->assertRedirect()
            ->assertSessionHas('stock_alert_success');

        $this->assertNotNull($subscription->fresh()->cancelled_at);
    }

    public function test_customer_cannot_cancel_another_customers_stock_alert(): void
    {
        $other = User::factory()->create();
        $product = Product::factory()->create([
            'category_id' => Category::firstOrFail()->id,
            'brand_id' => Brand::firstOrFail()->id,
            'slug' => 'foreign-stock-alert',
            'sku' => 'RYM-FOREIGN-STOCK',
            'stock' => 0,
        ]);
        $subscription = app(BackInStockSubscriptionService::class)->subscribe($other, $product, null, true);

        $this->delete(route('account.stock-alerts.destroy', $subscription))
            ->assertForbidden();

        $this->assertDatabaseHas('back_in_stock_subscriptions', [
            'id' => $subscription->id,
            'cancelled_at' => null,
        ]);
    }

    public function test_account_paginates_stock_alerts_without_changing_the_total(): void
    {
        $categoryId = Category::firstOrFail()->id;
        $brandId = Brand::firstOrFail()->id;

        foreach (range(1, 13) as $number) {
            $product = Product::factory()->create([
                'category_id' => $categoryId,
                'brand_id' => $brandId,
                'name' => "Account stock alert {$number}",
                'slug' => "account-stock-alert-{$number}",
                'sku' => sprintf('RYM-ACCOUNT-%02d', $number),
                'stock' => 0,
            ]);

            app(BackInStockSubscriptionService::class)->subscribe($this->user, $product, null, true);
        }

        $this->get('/account?stock_alert_page=2')
            ->assertOk()
            ->assertSee('Stock alerts')
            ->assertSee('13')
            ->assertSee('Account stock alert 1')
            ->assertDontSee('Account stock alert 13')
            ->assertSee('stock_alert_page=1', escape: false);

        $this->get('/account?stock_alert_page=99')
            ->assertOk()
            ->assertSee('No requests on this page')
            ->assertDontSee('No active stock alerts')
            ->assertSee('View first page');
    }

    public function test_orders_listed_for_owner(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id, 'total' => 8499]);

        $this->get('/account')
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('₹8,499');
    }
}
