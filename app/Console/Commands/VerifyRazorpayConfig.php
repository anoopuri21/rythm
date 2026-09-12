<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Payment\RazorpayGateway;
use App\Support\PaymentAvailability;
use Illuminate\Console\Command;
use Throwable;

/**
 * C6 — owner/ops readiness check. Never prints key secret or webhook secret.
 */
final class VerifyRazorpayConfig extends Command
{
    protected $signature = 'razorpay:verify
                            {--ping : Call Razorpay API with keys (orders create dry-run skipped; fetches empty list / auth check)}';

    protected $description = 'Report Razorpay env readiness without exposing secrets (C6 / W2)';

    public function handle(): int
    {
        $keyId = trim((string) config('services.razorpay.key_id', ''));
        $secret = trim((string) config('services.razorpay.key_secret', ''));
        $webhook = trim((string) config('services.razorpay.webhook_secret', ''));
        $allowFake = (bool) config('services.razorpay.allow_fake', false);

        $this->info('Razorpay configuration status');
        $this->line('  APP_ENV:          '.app()->environment());
        $this->line('  APP_URL:          '.(string) config('app.url'));
        $this->line('  Key ID set:       '.($keyId !== '' ? 'yes' : 'no'));
        $this->line('  Key ID prefix:    '.($keyId !== '' ? $this->keyPrefix($keyId) : '—'));
        $this->line('  Key Secret set:   '.($secret !== '' ? 'yes (hidden)' : 'no'));
        $this->line('  Webhook secret:   '.($webhook !== '' ? 'yes (hidden)' : 'no — webhooks will fail signature'));
        $this->line('  ALLOW_FAKE:       '.($allowFake ? 'true' : 'false'));
        $this->line('  isConfigured:     '.(RazorpayGateway::isConfigured() ? 'yes' : 'no'));
        $this->line('  Payment mode:     '.PaymentAvailability::mode());
        $this->line('  canCheckout:      '.(PaymentAvailability::canCheckout() ? 'yes' : 'no'));

        if ($keyId !== '' && str_starts_with($keyId, 'rzp_live_')) {
            $this->warn('  Live key detected. Real money path — only after successful test-mode smoke.');
        }

        if ($keyId !== '' && str_starts_with($keyId, 'rzp_test_')) {
            $this->info('  Test key detected — safe for UAT (no real charge).');
        }

        if ($allowFake && ! app()->environment('local')) {
            $this->error('  FAIL: RAZORPAY_ALLOW_FAKE_PAYMENTS must be false outside local.');

            return self::FAILURE;
        }

        if (app()->environment('production') && $allowFake) {
            $this->error('  FAIL: fake payments enabled in production.');

            return self::FAILURE;
        }

        if (! RazorpayGateway::isConfigured()) {
            $this->warn('  Not ready for real Razorpay checkout. Set RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET.');
            $this->line('  See docs/C6_RAZORPAY_TEST_CHECKOUT.md');

            return self::FAILURE;
        }

        if ($this->option('ping')) {
            return $this->pingApi($keyId);
        }

        $this->newLine();
        $this->info('Keys present. Next: dashboard webhook → '.rtrim((string) config('app.url'), '/').'/payment/razorpay/webhook');
        $this->line('Full checklist: docs/C6_RAZORPAY_TEST_CHECKOUT.md');

        return self::SUCCESS;
    }

    private function keyPrefix(string $keyId): string
    {
        if (str_starts_with($keyId, 'rzp_test_')) {
            return 'rzp_test_… ('.strlen($keyId).' chars)';
        }

        if (str_starts_with($keyId, 'rzp_live_')) {
            return 'rzp_live_… ('.strlen($keyId).' chars)';
        }

        return 'unknown prefix ('.strlen($keyId).' chars)';
    }

    private function pingApi(string $keyId): int
    {
        $this->newLine();
        $this->line('Pinging Razorpay API (orders->all count=1)…');

        try {
            $api = new \Razorpay\Api\Api(
                (string) config('services.razorpay.key_id'),
                (string) config('services.razorpay.key_secret'),
            );
            // Lightweight auth check — list at most one order.
            $api->order->all(['count' => 1]);
            $this->info('  API auth OK for '.$this->keyPrefix($keyId));

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('  API call failed: '.$e->getMessage());
            $this->line('  Check Key ID / Secret pair and that Test/Live mode matches the key type.');

            return self::FAILURE;
        }
    }
}
