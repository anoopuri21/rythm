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
        $this->categories->flush();
    }

    public function deleted(Category $category): void
    {
        $this->categories->flush();
    }

    public function restored(Category $category): void
    {
        $this->categories->flush();
    }
}
