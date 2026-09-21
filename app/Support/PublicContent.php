<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Page;
use App\Services\SiteSettingsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Storefront visibility helpers for client-owned content.
 *
 * Rule: if the client has not published a page or enabled a setting,
 * the storefront must hide related chrome — never invent policy copy,
 * never link to withheld/404 destinations, never crash checkout.
 */
final class PublicContent
{
    private const ACTIVE_PAGES_CACHE = 'public_content.active_page_slugs';

    /**
     * @return list<string>
     */
    public static function withheldSlugs(): array
    {
        $slugs = config('rythme.withheld_public_pages', []);

        return array_values(array_filter(array_map('strval', is_array($slugs) ? $slugs : [])));
    }

    public static function isWithheld(string $slug): bool
    {
        return in_array($slug, self::withheldSlugs(), true);
    }

    /**
     * Page is linkable only when not withheld and an active CMS row exists.
     */
    public static function isPagePublic(string $slug): bool
    {
        $slug = trim($slug);
        if ($slug === '' || self::isWithheld($slug)) {
            return false;
        }

        return in_array($slug, self::activePageSlugs(), true);
    }

    /**
     * Absolute path for a public CMS page, or null when it must not be linked.
     */
    public static function pageHref(string $slug): ?string
    {
        return self::isPagePublic($slug) ? '/'.ltrim($slug, '/') : null;
    }

    /**
     * @return list<string>
     */
    public static function activePageSlugs(): array
    {
        if (! Schema::hasTable('pages')) {
            return [];
        }

        return Cache::rememberForever(self::ACTIVE_PAGES_CACHE, function (): array {
            return Page::query()
                ->where('is_active', true)
                ->whereNotNull('slug')
                ->pluck('slug')
                ->map(fn ($slug): string => (string) $slug)
                ->filter()
                ->values()
                ->all();
        });
    }

    public static function forgetPageCache(): void
    {
        Cache::forget(self::ACTIVE_PAGES_CACHE);
    }

    public static function returnsEnabled(?SiteSettingsService $settings = null): bool
    {
        $settings ??= app(SiteSettingsService::class);

        return $settings->get('returns_enabled', '0') === '1';
    }

    public static function taxEnabled(?SiteSettingsService $settings = null): bool
    {
        $settings ??= app(SiteSettingsService::class);

        return $settings->get('tax_rules_enabled', '0') === '1'
            && $settings->getFloat('tax_rate', 0.0) > 0.0;
    }

    /**
     * Whether shipping should be called out as a non-zero fee in UI chrome.
     * Zero fee is still a valid configured state (free shipping).
     */
    public static function shippingFee(float $amount): float
    {
        return max(0.0, $amount);
    }
}
