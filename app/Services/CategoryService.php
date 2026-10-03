<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Cached 2-level category tree shared by the navbar drawer, the shop
 * filter sidebar and breadcrumbs. Cache is flushed by CategoryObserver.
 */
final class CategoryService
{
    private const CACHE_KEY = 'categories.tree';

    /**
     * @return array<int, array{id:int, name:string, slug:string, image:?string, children:array<int, array{name:string, slug:string}>}>
     */
    public function tree(): array
    {
        // Error and installer pages must still render when the configured
        // database is unavailable or has not been migrated.
        if (! Schema::hasTable('categories')) {
            return [];
        }

        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            $columns = ['id', 'name', 'slug', 'sort_order'];

            if (Schema::hasColumn('categories', 'icon_url')) {
                $columns[] = 'icon_url';
            }

            return Category::query()
                ->with([
                    'children' => fn ($query) => $query->orderBy('sort_order')->orderBy('name'),
                    'media',
                ])
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get($columns)
                ->map(function (Category $category): array {
                    $asset = 'images/categories/'.$category->slug.'.jpg';

                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'image' => $category->iconUrl()
                            ?? (is_file(public_path($asset)) ? '/'.$asset : null),
                        'children' => $category->children
                            ->map(fn (Category $child): array => [
                                'name' => $child->name,
                                'slug' => $child->slug,
                            ])
                            ->all(),
                    ];
                })
                ->all();
        });
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
