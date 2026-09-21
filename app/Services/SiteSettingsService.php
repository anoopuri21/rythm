<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

/**
 * Cached key-value site settings (shipping, GST, contact, social…).
 * Values editable from Filament Settings page; flushed on save.
 */
final class SiteSettingsService
{
    private const CACHE_KEY = 'site.settings';

    public const DEFAULTS = [
        'shipping_flat_fee' => '0',
        'shipping_free_above' => '0',
        'tax_rules_enabled' => '0',
        'tax_rate' => '0',
        'origin_state' => '',
        'origin_gstin' => '',
        'business_legal_name' => '',
        'business_address' => '',
        'returns_enabled' => '0',
        'return_window_days' => '0',
        // Contact stays empty until the client saves real values in Admin → Settings.
        // Empty values must not render on the storefront (top bar / WhatsApp float).
        'contact_email' => '',
        'contact_phone' => '',
        'whatsapp_number' => '',
        'whatsapp_message' => '',
        'address_line' => '',
        // Social links stay empty until an admin saves a real profile URL,
        // so no placeholder icon is ever shown in the top bar.
        'social_instagram' => '',
        'social_youtube' => '',
        'social_facebook' => '',
        'social_x' => '',
        'social_linkedin' => '',
        // Outbound mail From (Admin → Settings). Live address requires verification.
        'mail_from_address' => '',
        'mail_from_name' => '',
        'mail_from_verified_at' => '',
        'mail_from_pending_address' => '',
        'mail_from_pending_token' => '',
        'mail_from_pending_sent_at' => '',
    ];

    /** @return array<string, string> */
    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            $stored = SiteSetting::query()->pluck('value', 'key')->all();

            return array_merge(self::DEFAULTS, $stored);
        });
    }

    public function get(string $key, ?string $default = null): ?string
    {
        return $this->all()[$key] ?? $default;
    }

    public function getFloat(string $key, float $default = 0.0): float
    {
        return (float) ($this->get($key) ?? $default);
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function saveAll(array $values): void
    {
        foreach ($values as $key => $value) {
            if ($value === null || $value === '') {
                SiteSetting::where('key', $key)->delete();
                continue;
            }

            SiteSetting::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        }

        Cache::forget(self::CACHE_KEY);
    }

    public function put(string $key, string $value): void
    {
        SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget(self::CACHE_KEY);
    }

    /** @param  array<string, string>  $pairs */
    public function putMany(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        }
        Cache::forget(self::CACHE_KEY);
    }

    public function forget(string $key): void
    {
        SiteSetting::where('key', $key)->delete();
        Cache::forget(self::CACHE_KEY);
    }

    /** @param  list<string>  $keys */
    public function forgetMany(array $keys): void
    {
        SiteSetting::whereIn('key', $keys)->delete();
        Cache::forget(self::CACHE_KEY);
    }
}
