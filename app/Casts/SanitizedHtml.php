<?php

declare(strict_types=1);

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Sanitizes trusted-admin rich text both when persisted and when read, so
 * legacy rows cannot bypass the current write boundary.
 */
final class SanitizedHtml implements CastsAttributes
{
    private static ?HtmlSanitizer $sanitizer = null;

    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return $this->sanitize($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return $this->sanitize($value);
    }

    private function sanitize(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        self::$sanitizer ??= new HtmlSanitizer(
            (new HtmlSanitizerConfig())
                ->allowSafeElements()
                ->allowRelativeLinks(),
        );

        return self::$sanitizer->sanitize((string) $value);
    }
}
