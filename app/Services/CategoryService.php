<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Cached 2-level category tree shared by the navbar drawer, the shop
 * filter sidebar and breadcrumbs. Cache is flushed by CategoryObserver.
 */
final class CategoryService
{
    private const CACHE_KEY = 'categories.tree';

    /**
     * Shape version of the cached payload.
     *
     * The tree is cached **forever**, so a release that adds/removes/renames a
     * key would otherwise keep serving the payload of the previous release to
     * the new Blade templates — that is how `/shop` died with
     * `Undefined array key "image"` (the `image` key was added in the
     * 2026-10-03 storefront-image release while the cache still held the
     * pre-release payload).
     *
     * **Bump this whenever the payload shape changes.** An entry with another
     * version is treated as a miss and rebuilt on the next request, so a deploy
     * heals itself — no manual `php artisan cache:clear` needed.
     *
     * History: `1` = bare array payload without `image` (releases before
     * 2026-10-03); `2` = versioned envelope, items carry `image` (nullable).
     */
    public const PAYLOAD_VERSION = 2;

    /**
     * @return array<int, array{id:int, name:string, slug:string, image:?string, children:array<int, array{name:string, slug:string}>}>
     */
    public function tree(): array
    {
        // Error and installer pages must still render when the configured
        // database is unavailable or has not been migrated.
        if (! Schema::hasTable('categories')) {
            return [];
        }

        $cached = Cache::get(self::CACHE_KEY);

        if (is_array($cached)
            && (int) ($cached['version'] ?? 0) === self::PAYLOAD_VERSION
            && is_array($cached['items'] ?? null)) {
            return $cached['items'];
        }

        $tree = $this->buildTree();

        // `forever`, not `rememberForever`: the envelope has to be validated
        // before it is trusted, so the miss handling lives here.
        Cache::forever(self::CACHE_KEY, [
            'version' => self::PAYLOAD_VERSION,
            'items' => $tree,
        ]);

        return $tree;
    }

    /**
     * @return array<int, array{id:int, name:string, slug:string, image:?string, children:array<int, array{name:string, slug:string}>}>
     */
    private function buildTree(): array
    {
        $columns = ['id', 'name', 'slug', 'sort_order'];

        if (Schema::hasColumn('categories', 'icon_url')) {
            $columns[] = 'icon_url';
        }

        return Category::query()
            ->with([
                'children' => fn ($query) => $query->orderBy('sort_order')->orderBy('name'),
                'media',
            ])
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get($columns)
            ->map(function (Category $category): array {
                $asset = 'images/categories/'.$category->slug.'.jpg';

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'image' => self::storefrontImage(
                        $category->iconUrl() ?? (is_file(public_path($asset)) ? '/'.$asset : null)
                    ),
                    'children' => $category->children
                        ->map(fn (Category $child): array => [
                            'name' => $child->name,
                            'slug' => $child->slug,
                        ])
                        ->all(),
                ];
            })
            ->all();
    }

    /**
     * Normalise a resolved icon for rendering inside `<img src>`.
     *
     * `icon_url` is free text in the admin form, so blanks and anything that is
     * not a same-origin path or an http(s) URL are dropped here — once, at the
     * cache boundary — instead of being re-checked in every view.
     */
    private static function storefrontImage(?string $url): ?string
    {
        // Browsers ignore control characters inside URLs, so strip them first or
        // "java\nscript:" would slip past the scheme check below.
        $url = trim((string) preg_replace('/[\x00-\x1F\x7F]/', '', (string) $url));

        if ($url === '') {
            return null;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        // No scheme => same-origin (root-)relative asset path. `//host/...` is
        // protocol-relative, not same-origin. (`parse_url` can also return
        // false for a badly formed URL — treat that the same way.)
        if (! is_string($scheme)) {
            return str_starts_with($url, '//') ? null : $url;
        }

        return in_array(strtolower($scheme), ['http', 'https'], true) ? $url : null;
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
