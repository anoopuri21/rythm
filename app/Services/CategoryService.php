<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;

/**
 * Cached 2-level category tree shared by the navbar drawer, the shop
 * filter sidebar and breadcrumbs. Cache is flushed by CategoryObserver.
 */
final class CategoryService
{
    private const CACHE_KEY = 'categories.tree';

    /**
     * @return array<int, array{id:int, name:string, slug:string, children:array<int, array{name:string, slug:string}>}>
     */
    public function tree(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            return Category::query()
                ->with(['children' => fn ($query) => $query->orderBy('sort_order')->orderBy('name')])
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'sort_order'])
                ->map(fn (Category $category): array => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'children' => $category->children
                        ->map(fn (Category $child): array => [
                            'name' => $child->name,
                            'slug' => $child->slug,
                        ])
                        ->all(),
                ])
                ->all();
        });
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
