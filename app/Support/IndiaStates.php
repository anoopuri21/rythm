<?php

declare(strict_types=1);

namespace App\Support;

final class IndiaStates
{
    /** @var list<string> */
    public const NAMES = [
        'Andaman and Nicobar Islands',
        'Andhra Pradesh',
        'Arunachal Pradesh',
        'Assam',
        'Bihar',
        'Chandigarh',
        'Chhattisgarh',
        'Dadra and Nagar Haveli and Daman and Diu',
        'Delhi',
        'Goa',
        'Gujarat',
        'Haryana',
        'Himachal Pradesh',
        'Jammu and Kashmir',
        'Jharkhand',
        'Karnataka',
        'Kerala',
        'Ladakh',
        'Lakshadweep',
        'Madhya Pradesh',
        'Maharashtra',
        'Manipur',
        'Meghalaya',
        'Mizoram',
        'Nagaland',
        'Odisha',
        'Puducherry',
        'Punjab',
        'Rajasthan',
        'Sikkim',
        'Tamil Nadu',
        'Telangana',
        'Tripura',
        'Uttar Pradesh',
        'Uttarakhand',
        'West Bengal',
    ];

    /** @var array<string, string> */
    private const ALIASES = [
        'nct of delhi' => 'delhi',
        'nct delhi' => 'delhi',
        'new delhi' => 'delhi',
        'orissa' => 'odisha',
        'pondicherry' => 'puducherry',
        'uttaranchal' => 'uttarakhand',
        'daman and diu' => 'dadra and nagar haveli and daman and diu',
        'dadra and nagar haveli' => 'dadra and nagar haveli and daman and diu',
        'andaman & nicobar islands' => 'andaman and nicobar islands',
        'jammu & kashmir' => 'jammu and kashmir',
    ];

    /** @return array<string, string> */
    public static function options(): array
    {
        return array_combine(self::NAMES, self::NAMES) ?: [];
    }

    /** @return list<string> */
    public static function names(): array
    {
        return self::NAMES;
    }

    public static function normalize(?string $state): string
    {
        $raw = strtolower(trim((string) $state));
        $raw = preg_replace('/\s+/', ' ', $raw) ?? $raw;
        $raw = self::ALIASES[$raw] ?? $raw;

        return $raw;
    }

    public static function same(?string $a, ?string $b): bool
    {
        $left = self::normalize($a);
        $right = self::normalize($b);

        return $left !== '' && $left === $right;
    }
}
