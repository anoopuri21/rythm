<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Str;

/**
 * SKU generation for admin-created products and variants.
 *
 * The `sku` columns on products/product_variants are NOT NULL + UNIQUE,
 * so "optional SKU" in the admin form means: leave blank and get a
 * generated, collision-free code instead. User-provided SKUs are always
 * kept as-is (trimmed only).
 */
final class SkuGenerator
{
    public const PREFIX = 'RYM-';

    private const RANDOM_LENGTH = 8;

    /** Generate a SKU that is unique across products (incl. trashed) and variants. */
    public static function generate(): string
    {
        do {
            $sku = self::PREFIX.Str::upper(Str::random(self::RANDOM_LENGTH));
        } while (self::exists($sku));

        return $sku;
    }

    /** Check a SKU against both unique indexes (soft-deleted rows still hold SKUs). */
    public static function exists(string $sku): bool
    {
        return Product::withTrashed()->where('sku', $sku)->exists()
            || ProductVariant::query()->where('sku', $sku)->exists();
    }

    /**
     * Fill `$data[$key]` with a generated SKU when blank, else keep it trimmed.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function fillIfBlank(array $data, string $key = 'sku'): array
    {
        $value = trim((string) ($data[$key] ?? ''));

        $data[$key] = $value !== '' ? $value : self::generate();

        return $data;
    }
}
