<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Brand;
use App\Services\BrandService;
use Illuminate\Support\Facades\Cache;

final class BrandObserver
{
    public function saved(Brand $brand): void
    {
        Cache::forget(BrandService::CACHE_KEY);
    }

    public function deleted(Brand $brand): void
    {
        Cache::forget(BrandService::CACHE_KEY);
    }

    public function restored(Brand $brand): void
    {
        Cache::forget(BrandService::CACHE_KEY);
    }
}
