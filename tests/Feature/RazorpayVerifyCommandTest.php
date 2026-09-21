<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class RazorpayVerifyCommandTest extends TestCase
{
    public function test_verify_fails_when_keys_missing(): void
    {
        Config::set('services.razorpay.key_id', '');
        Config::set('services.razorpay.key_secret', '');
        Config::set('services.razorpay.allow_fake', false);

        $this->artisan('razorpay:verify')
            ->expectsOutputToContain('isConfigured')
            ->assertFailed();
    }

    public function test_verify_succeeds_when_test_keys_present_without_printing_secret(): void
    {
        Config::set('services.razorpay.key_id', 'rzp_test_abcdefghijklmnop');
        Config::set('services.razorpay.key_secret', 'super_secret_value_do_not_leak');
        Config::set('services.razorpay.webhook_secret', 'whsec_test');
        Config::set('services.razorpay.allow_fake', false);

        $this->artisan('razorpay:verify')
            ->expectsOutputToContain('rzp_test_')
            ->expectsOutputToContain('yes (hidden)')
            ->doesntExpectOutputToContain('super_secret_value_do_not_leak')
            ->doesntExpectOutputToContain('whsec_test')
            ->assertSuccessful();
    }

    public function test_verify_fails_when_fake_allowed_outside_local(): void
    {
        Config::set('services.razorpay.key_id', 'rzp_test_abcdefghijklmnop');
        Config::set('services.razorpay.key_secret', 'secret');
        Config::set('services.razorpay.allow_fake', true);

        // PHPUnit env is typically "testing" — not local.
        $this->artisan('razorpay:verify')
            ->expectsOutputToContain('ALLOW_FAKE')
            ->assertFailed();
    }
}
