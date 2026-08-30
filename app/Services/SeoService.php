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
            // Only safe metadata tags are exposed. Arbitrary script markup is
            // never passed through to the document head.
            'head_tags' => self::safeHeadTags($entry),
            'robots' => $entry->robots,
        ];

        return array_merge(
            $defaults,
            array_filter($fromEntry, fn ($value): bool => $value !== null && $value !== ''),
        );
    }

    /**
     * Preserve harmless metadata snippets from legacy SEO records without
     * allowing arbitrary HTML, event handlers, links or executable markup.
     */
    private static function safeHeadTags(SeoEntry $entry): ?string
    {
        $raw = trim((string) $entry->getAttribute('head_scripts'));

        if ($raw === '') {
            return null;
        }

        preg_match_all('/<meta\\b[^>]*>/is', $raw, $matches);
        $safe = [];

        foreach ($matches[0] as $tag) {
            preg_match_all(
                '/([a-zA-Z_:][a-zA-Z0-9_.:-]*)\\s*=\\s*([\"\\\'])(.*?)\\2/is',
                $tag,
                $attributes,
                PREG_SET_ORDER,
            );

            if ($attributes === []) {
                continue;
            }

            $rendered = [];
            $valid = true;

            foreach ($attributes as $attribute) {
                $name = strtolower($attribute[1]);

                if (! in_array($name, ['name', 'property', 'content', 'charset', 'itemprop'], true)) {
                    $valid = false;
                    break;
                }

                $value = htmlspecialchars($attribute[3], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $rendered[] = $name.'="'.$value.'"';
            }

            if ($valid) {
                $safe[] = '<meta '.implode(' ', $rendered).'>';
            }
        }

        return $safe === [] ? null : implode("\\n", $safe);
    }
}
