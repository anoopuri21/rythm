<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            \Illuminate\Support\Facades\Hash::check('newsecret123', $this->user->fresh()->password)
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

    public function test_cannot_delete_others_address(): void
    {
        $other = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $other->id]);

        $this->delete('/account/addresses/'.$address->id)->assertForbidden();
        $this->assertDatabaseHas('addresses', ['id' => $address->id]);
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
