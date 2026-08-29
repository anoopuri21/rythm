<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Faq;
use App\Models\HeroSlide;
use App\Models\HomepageBlock;
use App\Models\HomepageCategoryRow;
use Illuminate\Support\Facades\Cache;

/**
 * Flushes the homepage data cache whenever any admin-managed
 * homepage content changes (hero slides, blocks, faqs).
 */
final class HomepageDataObserver
{
    public const CACHE_KEY = 'homepage.data';

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function saved(HeroSlide|HomepageBlock|HomepageCategoryRow|Faq $model): void
    {
        self::flush();
    }

    public function deleted(HeroSlide|HomepageBlock|HomepageCategoryRow|Faq $model): void
    {
        self::flush();
    }

    public function restored(HeroSlide|HomepageBlock|HomepageCategoryRow|Faq $model): void
    {
        self::flush();
    }
}
