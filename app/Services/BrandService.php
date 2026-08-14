<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Brand;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class BrandService
{
    public const CACHE_KEY = 'brands.with_counts';

    /**
     * @return Collection<int, Brand> active brands with product counts
     */
    public function allWithCounts(): Collection
    {
        // Cached 1h — used by the global footer on EVERY page.
        return Cache::remember(self::CACHE_KEY, 3600, function (): Collection {
            return Brand::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->withCount(['products' => fn ($query) => $query->where('is_active', true)])
                ->get();
        });
    }
}
