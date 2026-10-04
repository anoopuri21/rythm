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

        /*
        |----------------------------------------------------------------------
        | Storefront presentation (NO-HARDCODE rule — docs/NO_HARDCODE_PLAN.md)
        | Every default below is the value that used to be written into the
        | Blade/PHP source, so switching to settings changes nothing until an
        | admin edits it.
        |----------------------------------------------------------------------
        */
        'currency_symbol' => '₹',
        'shop_category_shortcuts' => '8',
        'home_hero_slides' => '3',
        'home_bestsellers_limit' => '8',
        'home_new_arrivals_limit' => '10',
        'home_trending_limit' => '10',
        'home_deals_limit' => '8',
        'home_brands_limit' => '16',
        'home_brands_shown' => '2',
        'home_category_banners' => '3',
        'home_testimonials' => '3',
        'home_faqs' => '6',
        'home_promo_banners' => '2',
        'offer_marquee_items' => '8',
        'offer_min_discount' => '10',
        'offer_max_discount' => '50',
        'footer_category_links' => '5',
        'footer_brand_links' => '5',
        // Comma-separated product slugs for the "Recently launched" rail.
        'recently_launched_slugs' => 'roland-fp-30x-digital-piano,krk-rokit-5-g4-studio-monitor-single,'
            .'akg-k240-studio-headphones,fender-mustang-lt25-modelling-amp,'
            .'casio-ct-s300-portable-keyboard,numark-mixtrack-pro-fx',

        /*
        |----------------------------------------------------------------------
        | Brand & media — empty means "use config/rythme.php", so existing
        | installs keep working until an admin saves real values here.
        |----------------------------------------------------------------------
        */
        'brand_name' => '',
        'brand_short' => '',
        'brand_logo_url' => '',
    ];

    /**
     * config/rythme.php fallback for each brand/media setting. These stay as a
     * last-resort default only; the admin value always wins.
     */
    private const CONFIG_FALLBACKS = [
        'brand_name' => 'rythme.brand_name',
        'brand_short' => 'rythme.brand_short',
        'brand_logo_url' => 'rythme.logo_url',
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
     * A count / limit setting. Falls back to DEFAULTS and is clamped, so a
     * blank or absurd admin value can never render an empty section or a row
     * that overflows its grid.
     */
    public function getCount(string $key, int $min = 1, int $max = 24): int
    {
        $value = (int) ($this->all()[$key] ?? $min);

        return max($min, min($max, $value));
    }

    /** Brand / media setting, falling back to config/rythme.php when unset. */
    public function brandOrMedia(string $key): string
    {
        $value = trim((string) $this->get($key, ''));

        if ($value !== '') {
            return $value;
        }

        return (string) config(self::CONFIG_FALLBACKS[$key] ?? '', '');
    }

    /**
     * Storefront currency symbol. Rendered through the `@currency` Blade
     * directive so no view carries a literal symbol.
     */
    public function currencySymbol(): string
    {
        $symbol = trim((string) $this->get('currency_symbol', ''));

        return $symbol !== '' ? $symbol : self::DEFAULTS['currency_symbol'];
    }

    /** @return list<string> Comma-separated setting as a clean slug list. */
    public function slugList(string $key): array
    {
        $parts = explode(',', (string) $this->get($key, ''));

        return array_values(array_unique(array_filter(
            array_map(fn (string $part): string => trim($part), $parts),
            static fn (string $part): bool => $part !== '',
        )));
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
