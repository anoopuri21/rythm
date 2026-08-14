<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SeoEntry;
use Illuminate\Support\Facades\View;

/**
 * On-page SEO rendering. Controllers call apply() with resolved SEO
 * data; layouts/app.blade.php reads the shared $seo array and falls
 * back to Blade @yield('title'/'meta_description') when not set —
 * so every page keeps working even without an SEO entry.
 */
final class SeoService
{
    /**
     * @param  array<string, mixed>  $seo
     */
    public function apply(array $seo = []): void
    {
        View::share('seo', $seo);
    }

    /**
     * Build a seo array from a (nullable) SeoEntry, merged over defaults.
     *
     * @param  array<string, mixed>  $defaults
     * @return array<string, mixed>
     */
    public static function fromEntry(?SeoEntry $entry, array $defaults = []): array
    {
        if ($entry === null) {
            return $defaults;
        }

        $fromEntry = [
            'meta_title' => $entry->meta_title,
            'meta_description' => $entry->meta_description,
            'meta_keywords' => $entry->meta_keywords,
            'og_title' => $entry->og_title,
            'og_description' => $entry->og_description,
            'og_image' => $entry->og_image,
            'canonical_url' => $entry->canonical_url,
            'schema_json' => $entry->schema_json,
            'head_scripts' => $entry->head_scripts,
            'robots' => $entry->robots,
        ];

        return array_merge(
            $defaults,
            array_filter($fromEntry, fn ($value): bool => $value !== null && $value !== ''),
        );
    }
}
