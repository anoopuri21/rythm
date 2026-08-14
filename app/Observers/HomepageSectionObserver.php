<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\HomepageSection;
use Illuminate\Support\Facades\Cache;

final class HomepageSectionObserver
{
    private const CACHE_KEY = 'homepage.sections';

    public function saved(HomepageSection $section): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function deleted(HomepageSection $section): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function restored(HomepageSection $section): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
