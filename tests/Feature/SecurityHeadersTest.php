<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_present_on_all_responses(): void
    {
        $this->seed();

        $this->get('/')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    public function test_csp_header_present(): void
    {
        $this->seed();

        $response = $this->get('/');
        $csp = $response->headers->get('Content-Security-Policy');

        $this->assertNotNull($csp);
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("frame-src https://checkout.razorpay.com", $csp);
        $this->assertStringContainsString("form-action 'self'", $csp);
    }

    public function test_csrf_token_present_on_all_forms(): void
    {
        $this->seed();

        $this->get('/')
            ->assertOk()
            ->assertSee('name="csrf-token"', escape: false);

        $this->get('/login')
            ->assertOk()
            ->assertSee('name="csrf-token"', escape: false);

        $this->get('/contact')
            ->assertOk()
            ->assertSee('name="csrf-token"', escape: false);
    }

    public function test_contact_honeypot_rejected(): void
    {
        $this->seed();

        $this->post('/contact', [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'message' => 'A message long enough to pass validation otherwise.',
            'company' => 'spam',
        ])->assertSessionHasErrors('company');
    }

    public function test_mass_assignment_blocks_unknown_attributes(): void
    {
        $this->seed();

        $product = \App\Models\Product::create([
            'name' => 'Guard Check',
            'slug' => 'guard-check',
            'sku' => 'RYM-GUARD-9',
            'price' => 100,
            'stock' => 1,
            'is_admin' => 1,
        ]);

        $this->assertArrayNotHasKey('is_admin', $product->getAttributes());
    }

    public function test_login_rate_limited(): void
    {
        $this->seed();

        for ($i = 0; $i < 6; $i++) {
            $response = $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password-'.$i,
            ]);
        }

        // 6th attempt within the minute → 429
        $this->assertTrue(in_array($response->getStatusCode(), [419, 429], true));
    }
}
