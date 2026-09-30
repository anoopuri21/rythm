<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

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
        // Brand marks (Admin → Settings → Brand logo & marks). Every value is
        // either a path on the "public" disk (branding/…) or an absolute URL.
        // Empty means "use the bundled fallback", so an admin never has to
        // upload anything for the storefront to keep rendering correctly.
        'logo_regular' => '',
        'logo_white' => '',
        'logo_favicon' => '',
        'logo_og' => '',
        // Outbound mail From (Admin → Settings). Live address requires verification.
        'mail_from_address' => '',
        'mail_from_name' => '',
        'mail_from_verified_at' => '',
        'mail_from_pending_address' => '',
        'mail_from_pending_token' => '',
        'mail_from_pending_sent_at' => '',
    ];

    /** Brand-mark keys rendered as file uploads in Admin → Settings. */
    public const MARK_KEYS = ['logo_regular', 'logo_white', 'logo_favicon', 'logo_og'];

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

    /*
    |--------------------------------------------------------------------------
    | Brand marks
    |--------------------------------------------------------------------------
    | Each mark is stored as a plain string so it fits the existing key/value
    | table and cache — no media table, no migration. Absolute URLs are passed
    | through untouched; anything else is treated as a path on the "public"
    | disk and resolved to an absolute URL, so callers never have to remember
    | whether a value is a path or a URL (the mobile drawer used to get this
    | wrong by rendering the raw value).
    */

    /** Absolute URL for a stored mark, or null when the admin has not set one. */
    private function markUrl(string $key): ?string
    {
        $value = trim((string) $this->get($key, ''));

        if ($value === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $value) === 1) {
            return $value;
        }

        return Storage::disk('public')->url(ltrim($value, '/'));
    }

    /**
     * Standard logo for light surfaces (navbar, mobile drawer).
     * Falls back to the shipped Rhythm mark so the header is never empty.
     */
    public function logoUrl(): string
    {
        return $this->markUrl('logo_regular') ?? (string) config('rythme.logo_url');
    }

    /**
     * Light/inverse logo for dark surfaces (footer). Null means the caller
     * should keep tinting the standard logo with a CSS filter instead.
     */
    public function logoWhiteUrl(): ?string
    {
        $mark = $this->markUrl('logo_white');

        if ($mark !== null) {
            return $mark;
        }

        $fallback = trim((string) config('rythme.logo_white_url'));

        return $fallback === '' ? null : $fallback;
    }

    /** Browser tab icon. Falls back to the bundled favicon. */
    public function faviconUrl(): string
    {
        return $this->markUrl('logo_favicon') ?? asset('favicon.png');
    }

    /**
     * MIME type matching the favicon actually in use, so the <link rel="icon">
     * tag never advertises image/png for an uploaded WebP/JPEG mark.
     */
    public function faviconMime(): string
    {
        $path = (string) parse_url($this->faviconUrl(), PHP_URL_PATH);

        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'webp' => 'image/webp',
            'jpg', 'jpeg' => 'image/jpeg',
            'svg' => 'image/svg+xml',
            default => 'image/png',
        };
    }

    /**
     * Site-wide default social share image. Per-page SEO entries still win —
     * this only replaces the (currently missing) bundled fallback image.
     */
    public function ogImageUrl(): ?string
    {
        return $this->markUrl('logo_og');
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
