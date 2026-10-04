<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\HeroBanner;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

/**
 * Homepage banner slots — "built-in default, admin override".
 *
 * Owner rule: for the hero and banner sections the hardcoded content stays in
 * the code AND stays manageable from the admin panel. So every slot has a
 * built-in default below; an active `hero_banners` row replaces it, and any
 * field the admin leaves blank falls back to the default so a banner can never
 * render half-empty.
 *
 * Not cached: it is one indexed query for at most four rows, and skipping the
 * cache means an admin edit is live on the next request.
 */
final class HeroBannerService
{
    public const SLOT_HERO_TALL = 'hero-tall';

    public const SLOT_HERO_SMALL_1 = 'hero-small-1';

    public const SLOT_HERO_SMALL_2 = 'hero-small-2';

    public const SLOT_LAUNCH_BANNER = 'launch-banner';

    /**
     * Built-in defaults — the copy and imagery the storefront shipped with.
     * `image` is a public-path asset; an admin-uploaded image is a full URL.
     *
     * @var array<string, array{href:string, title:string, subtitle:string, cta_label:string, image:string, alt:string}>
     */
    public const DEFAULTS = [
        self::SLOT_HERO_TALL => [
            'href' => '/shop?category=keyboards-pianos',
            'title' => 'Stage Pianos',
            'subtitle' => 'As expressive as it is portable',
            'cta_label' => 'Shop now',
            'image' => 'images/hero/grid-banner-piano.jpg',
            'alt' => 'Digital stage piano',
        ],
        self::SLOT_HERO_SMALL_1 => [
            'href' => '/shop?category=drums-percussion',
            'title' => 'Tabla Sets',
            'subtitle' => 'Explore percussion instruments',
            'cta_label' => 'Shop now →',
            'image' => 'images/hero/grid-banner-tabla.jpg',
            'alt' => 'Tabla set',
        ],
        self::SLOT_HERO_SMALL_2 => [
            'href' => '/shop?category=pro-audio',
            'title' => 'Studio Gear',
            'subtitle' => 'Explore current studio offers',
            'cta_label' => 'Shop now →',
            'image' => 'images/hero/grid-banner-headphones.jpg',
            'alt' => 'Studio headphones',
        ],
        self::SLOT_LAUNCH_BANNER => [
            'href' => '/shop',
            // Two lines on the page; the view renders breaks with nl2br(e(...)).
            'title' => "Fresh gear,\nfirst play",
            'subtitle' => 'Just landed',
            'cta_label' => 'Explore all',
            'image' => 'images/brand-feature.jpg',
            'alt' => '',
        ],
    ];

    /** @var array<string, array{href:string, title:string, subtitle:string, cta_label:string, image:string, alt:string}>|null */
    private ?array $resolved = null;

    /** Memoised category liveness, so a repeated slug costs no query. @var array<string, bool> */
    private array $liveCategories = [];

    /**
     * Every slot, defaults merged with any active admin override.
     *
     * @return array<string, array{href:string, title:string, subtitle:string, cta_label:string, image:string, alt:string}>
     */
    public function all(): array
    {
        if ($this->resolved !== null) {
            return $this->resolved;
        }

        $rows = $this->overrides();

        $slots = [];

        foreach (self::DEFAULTS as $slot => $default) {
            $row = $rows->get($slot);

            $slots[$slot] = [
                'href' => $this->safeHref($this->pick($row?->href, $default['href'])),
                'title' => $this->pick($row?->title, $default['title']),
                'subtitle' => $this->pick($row?->subtitle, $default['subtitle']),
                'cta_label' => $this->pick($row?->cta_label, $default['cta_label']),
                'image' => $this->pick($row?->imageUrl(), asset($default['image'])),
                'alt' => $this->pick($row?->alt, $default['alt']),
            ];
        }

        return $this->resolved = $slots;
    }

    /**
     * One slot, defaults merged with its active admin override.
     *
     * @return array{href:string, title:string, subtitle:string, cta_label:string, image:string, alt:string}
     */
    public function get(string $slot): array
    {
        $slots = $this->all();

        // Slots are referenced by the SLOT_* constants above; an unknown one is a
        // programming error and must not silently render the wrong banner.
        if (! isset($slots[$slot])) {
            throw new InvalidArgumentException("Unknown hero banner slot [{$slot}].");
        }

        return $slots[$slot];
    }

    /**
     * @return Collection<string, HeroBanner> keyed by slot
     */
    private function overrides(): Collection
    {
        // Installer / error pages must still render before the table exists.
        if (! Schema::hasTable('hero_banners')) {
            return collect();
        }

        return HeroBanner::query()
            ->where('is_active', true)
            ->with('media')
            ->get()
            ->keyBy('slot');
    }

    private function pick(?string $value, string $default): string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : $default;
    }

    /**
     * Keep the link same-origin and pointing at a live category.
     *
     * An admin (or a renamed category) must not be able to send customers to an
     * empty `/shop?category=…` page, and a `javascript:` or protocol-relative
     * URL must never reach an `href`.
     */
    private function safeHref(string $href): string
    {
        if (! str_starts_with($href, '/') || str_starts_with($href, '//')) {
            return '/shop';
        }

        if (preg_match('~[?&]category=([^&#]+)~', $href, $matches) !== 1) {
            return $href;
        }

        return $this->isLiveCategory(rawurldecode($matches[1])) ? $href : '/shop';
    }

    private function isLiveCategory(string $slug): bool
    {
        return $this->liveCategories[$slug] ??= Schema::hasTable('categories')
            && Category::query()
                ->where('slug', $slug)
                ->where('is_active', true)
                ->exists();
    }
}
