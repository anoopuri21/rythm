<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PaymentGatewaySetting;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

final class PaymentSettingsService
{
    public function razorpayRow(): ?PaymentGatewaySetting
    {
        if (! Schema::hasTable('payment_gateway_settings')) {
            return null;
        }

        return PaymentGatewaySetting::query()
            ->where('driver', PaymentGatewaySetting::DRIVER_RAZORPAY)
            ->first();
    }

    public function razorpayRowOrNew(): PaymentGatewaySetting
    {
        return $this->razorpayRow() ?? new PaymentGatewaySetting([
            'driver' => PaymentGatewaySetting::DRIVER_RAZORPAY,
            'mode' => PaymentGatewaySetting::MODE_TEST,
        ]);
    }

    public function applyToConfig(): void
    {
        $row = $this->razorpayRow();
        if ($row === null) {
            return;
        }

        $mode = $row->mode === PaymentGatewaySetting::MODE_LIVE
            ? PaymentGatewaySetting::MODE_LIVE
            : PaymentGatewaySetting::MODE_TEST;

        $keyId = $mode === PaymentGatewaySetting::MODE_LIVE
            ? trim((string) $row->live_key_id)
            : trim((string) $row->test_key_id);
        $secret = $mode === PaymentGatewaySetting::MODE_LIVE
            ? trim((string) $row->live_key_secret)
            : trim((string) $row->test_key_secret);
        $webhook = $mode === PaymentGatewaySetting::MODE_LIVE
            ? trim((string) $row->live_webhook_secret)
            : trim((string) $row->test_webhook_secret);

        if ($keyId === '' && $secret === '') {
            return;
        }

        config([
            'services.razorpay.key_id' => $keyId,
            'services.razorpay.key_secret' => $secret,
            'services.razorpay.webhook_secret' => $webhook !== '' ? $webhook : config('services.razorpay.webhook_secret'),
            'services.razorpay.mode' => $mode,
        ]);
    }

    public function publicKeyId(): string
    {
        $this->applyToConfig();

        return trim((string) config('services.razorpay.key_id', ''));
    }

    public function webhookUrl(): string
    {
        return rtrim((string) config('app.url'), '/').'/payment/razorpay/webhook';
    }

    public static function userCanManage(?User $user): bool
    {
        return $user instanceof User && $user->isSuperAdmin();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function saveRazorpay(array $data): PaymentGatewaySetting
    {
        $row = $this->razorpayRowOrNew();
        $row->driver = PaymentGatewaySetting::DRIVER_RAZORPAY;
        $row->mode = ($data['mode'] ?? PaymentGatewaySetting::MODE_TEST) === PaymentGatewaySetting::MODE_LIVE
            ? PaymentGatewaySetting::MODE_LIVE
            : PaymentGatewaySetting::MODE_TEST;

        if (isset($data['test_key_id'])) {
            $row->test_key_id = trim((string) $data['test_key_id']);
        }
        if (isset($data['live_key_id'])) {
            $row->live_key_id = trim((string) $data['live_key_id']);
        }
        if (filled($data['test_key_secret'] ?? null)) {
            $row->test_key_secret = (string) $data['test_key_secret'];
        }
        if (filled($data['live_key_secret'] ?? null)) {
            $row->live_key_secret = (string) $data['live_key_secret'];
        }
        if (filled($data['test_webhook_secret'] ?? null)) {
            $row->test_webhook_secret = (string) $data['test_webhook_secret'];
        }
        if (filled($data['live_webhook_secret'] ?? null)) {
            $row->live_webhook_secret = (string) $data['live_webhook_secret'];
        }

        $row->save();
        $this->applyToConfig();

        return $row;
    }
}
