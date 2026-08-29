<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Product;

final class ProductHomepageObserver
{
    public function saved(Product $product): void
    {
        HomepageDataObserver::flush();
    }

    public function deleted(Product $product): void
    {
        HomepageDataObserver::flush();
    }

    public function restored(Product $product): void
    {
        HomepageDataObserver::flush();
    }
}
