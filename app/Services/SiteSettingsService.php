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
        'tax_rules_enabled' => '0', // disabled until professional approval
        'tax_rate' => '0',           // optional approved default rate
        'returns_enabled' => '0',    // disabled until an approved business policy is configured
        'return_window_days' => '0', // no eligibility window is assumed
        'contact_email' => 'support@rythme.store',
        'contact_phone' => '+91 98765 43210',
        'address_line' => '42, Music Lane, Karol Bagh, New Delhi 110005',
        'social_instagram' => 'https://instagram.com',
        'social_youtube' => 'https://youtube.com',
        'social_facebook' => 'https://facebook.com',
        'social_x' => 'https://x.com',
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
}
