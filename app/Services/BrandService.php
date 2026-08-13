<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Brand;
use Illuminate\Support\Collection;

final class BrandService
{
    /**
     * @return Collection<int, Brand> active brands with product counts
     */
    public function allWithCounts(): Collection
    {
        return Brand::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->withCount(['products' => fn ($query) => $query->where('is_active', true)])
            ->get();
    }
}
