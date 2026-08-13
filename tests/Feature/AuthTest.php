<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_login_page_renders(): void
    {
        $this->get('/login')->assertOk()->assertSee('Sign in');
    }

    public function test_register_page_renders(): void
    {
        $this->get('/register')->assertOk()->assertSee('Create account');
    }

    public function test_user_can_register_and_is_logged_in(): void
    {
        $this->post('/register', [
            'name' => 'New Musician',
            'email' => 'musician@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertRedirect('/email/verify');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'musician@example.com']);
    }

    public function test_register_validates_password_strength(): void
    {
        $this->post('/register', [
            'name' => 'Weak Pass',
            'email' => 'weak@example.com',
            'password' => 'abc',
            'password_confirmation' => 'abc',
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'weak@example.com']);
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::where('email', 'test@example.com')->firstOrFail();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_login_rejects_wrong_credentials(): void
    {
        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_authenticated_user_cannot_see_login_page(): void
    {
        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->actingAs($user);

        $this->get('/login')->assertRedirect('/');
    }

    public function test_login_redirects_to_intended_url(): void
    {
        $user = User::where('email', 'test@example.com')->firstOrFail();

        // Guest hits a protected page → redirected to login, intended stored.
        $this->get('/checkout')->assertRedirect('/login');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect('/checkout');
    }
    public function test_forgot_password_page_renders(): void
    {
        $this->get('/forgot-password')->assertOk()->assertSee('Reset password');
    }

    public function test_forgot_password_sends_reset_link(): void
    {
        \Illuminate\Support\Facades\Password::shouldReceive('sendResetLink')
            ->once()
            ->andReturn(\Illuminate\Support\Facades\Password::RESET_LINK_SENT);

        $this->post('/forgot-password', ['email' => 'test@example.com'])
            ->assertSessionHas('status');
    }

    public function test_reset_password_page_renders_with_token(): void
    {
        $this->get('/reset-password/fake-token')
            ->assertOk()
            ->assertSee('Choose a new password');
    }

    public function test_reset_password_completes_flow(): void
    {
        $user = User::where('email', 'test@example.com')->firstOrFail();

        \Illuminate\Support\Facades\Password::shouldReceive('reset')
            ->once()
            ->andReturn(\Illuminate\Support\Facades\Password::PASSWORD_RESET);

        $this->post('/reset-password', [
            'token' => 'fake-token',
            'email' => $user->email,
            'password' => 'brandnew123',
            'password_confirmation' => 'brandnew123',
        ])->assertRedirect('/login')->assertSessionHas('status');
    }

    public function test_reset_password_validates_strength(): void
    {
        $this->post('/reset-password', [
            'token' => 'fake-token',
            'email' => 'test@example.com',
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ])->assertSessionHasErrors('password');
    }
    public function test_verification_notice_requires_auth(): void
    {
        auth()->logout();

        $this->get('/email/verify')->assertRedirect('/login');
    }

    public function test_verification_notice_renders(): void
    {
        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->actingAs($user);

        $this->get('/email/verify')
            ->assertOk()
            ->assertSee('Verify your email')
            ->assertSee('Resend verification email');
    }

    public function test_verified_user_can_access_account(): void
    {
        $user = User::where('email', 'test@example.com')->firstOrFail();
        $user->markEmailAsVerified();

        $this->actingAs($user)->get('/account')->assertOk();
    }

    public function test_new_user_is_not_verified(): void
    {
        $user = User::where('email', 'test@example.com')->firstOrFail();

        $this->assertFalse($user->hasVerifiedEmail());
    }
}
