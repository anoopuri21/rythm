<?php

declare(strict_types=1);

namespace App\Filament\Components;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

/**
 * Shared "On-page SEO" form fields — reused by PageResource and
 * ProductResource (and any future SEO-able resource). Wrapped in a
 * Section bound to the 1-1 `seoEntry` relationship, so Filament
 * creates/updates the related record automatically.
 */
final class SeoFields
{
    /**
     * @return array<int, Section>
     */
    public static function schema(): array
    {
        return [
            Section::make('On-page SEO')
                ->description('Meta tags, Open Graph and Schema JSON rendered on this page. Leave blank to inherit defaults.')
                ->relationship('seoEntry')
                ->columns(2)
                ->collapsible()
                ->schema([
                    TextInput::make('meta_title')->maxLength(120)
                        ->helperText('Browser tab title & Google SERP title (≤ 60 chars recommended).'),
                    Textarea::make('meta_description')->rows(2)->maxLength(300)
                        ->helperText('SERP description (≤ 160 chars recommended).'),
                    TextInput::make('meta_keywords')->maxLength(500)
                        ->helperText('Comma-separated keywords.'),
                    TextInput::make('og_title')->maxLength(120)
                        ->helperText('Social share title — defaults to meta title.'),
                    Textarea::make('og_description')->rows(2)->maxLength(300),
                    TextInput::make('og_image')->maxLength(500)->url()
                        ->helperText('Absolute image URL for social shares.'),
                    TextInput::make('canonical_url')->maxLength(500)->url()
                        ->helperText('Absolute canonical URL (e.g. https://rythme.store/about).'),
                    TextInput::make('robots')->maxLength(100)
                        ->placeholder('index, follow')
                        ->helperText('Robots meta value.'),
                    Textarea::make('schema_json')->rows(5)->json()->maxLength(50000)
                        ->helperText('Valid JSON-LD schema (object or array) — rendered with script-safe JSON encoding.'),
                ]),
        ];
    }
}
