<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Category;
use App\Services\CategoryService;

final class CategoryObserver
{
    public function __construct(private readonly CategoryService $categories) {}

    public function saved(Category $category): void
    {
        $this->flush();
    }

    public function deleted(Category $category): void
    {
        $this->flush();
    }

    public function restored(Category $category): void
    {
        $this->flush();
    }

    private function flush(): void
    {
        $this->categories->flush();
        HomepageDataObserver::flush();
    }
}
