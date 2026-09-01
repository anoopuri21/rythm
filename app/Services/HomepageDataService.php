<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Faq;
use App\Models\HeroSlide;
use App\Models\HomepageBlock;
use App\Models\HomepageCategoryRow;
use App\Models\Product;
use App\Observers\HomepageDataObserver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Single source of truth for ALL homepage data.
 * Everything is DB-driven + cached (1h, flushed by observers).
 */
final class HomepageDataService
{
    private const MAX_CATEGORY_ROWS = 4;

    private const MAX_DISCOVERY_CATEGORIES = 10;
    /**
     * @return array{
     *   heroSlides: Collection<int, HeroSlide>,
     *   promos: Collection<int, HomepageBlock>,
     *   usps: Collection<int, HomepageBlock>,
     *   numbers: Collection<int, HomepageBlock>,
     *   testimonials: Collection<int, HomepageBlock>,
     *   stories: Collection<int, HomepageBlock>,
     *   ugc: Collection<int, HomepageBlock>,
     *   comparison: Collection<int, HomepageBlock>,
     *   faqs: Collection<int, Faq>,
     *   bestsellers: Collection<int, Product>,
     *   newArrivals: Collection<int, Product>,
     *   trending: Collection<int, Product>,
     *   bestDeals: Collection<int, Product>,
     *   categoryRows: Collection<int, array{row:HomepageCategoryRow,category:Category,products:Collection<int,Product>}>,
     *   popularCategories: Collection<int, array{name:string, slug:string, count:int, image:?string}>,
     * }
     */
    public function all(): array
    {
        return Cache::remember(HomepageDataObserver::CACHE_KEY, 3600, function (): array {
            $categoryRows = $this->categoryRows();

            return [
                'heroSlides' => HeroSlide::query()->where('is_active', true)->orderBy('sort_order')->get(),
                'promos' => HomepageBlock::query()->section('promo')->get(),
                'usps' => HomepageBlock::query()->section('usp')->get(),
                'numbers' => HomepageBlock::query()->section('number')->get(),
                'testimonials' => HomepageBlock::query()->section('testimonial')->get(),
                'stories' => HomepageBlock::query()->section('story')->get(),
                'ugc' => HomepageBlock::query()->section('ugc')->get(),
                'comparison' => HomepageBlock::query()->section('comparison')->get(),
                'faqs' => Faq::query()->where('is_active', true)->orderBy('sort_order')->get(),
                'bestsellers' => Product::query()->active()->featured()->withAvailableVariantStock()
                    ->with(['brand', 'category.parent', 'media'])
                    ->orderByRaw('featured_rank IS NULL')->orderBy('featured_rank')->orderBy('updated_at', 'desc')->limit(8)->get(),
                'newArrivals' => Product::query()->active()->withAvailableVariantStock()
                    ->with(['brand', 'category.parent', 'media'])
                    ->latest('created_at')->latest('id')->limit(10)->get(),
                'trending' => Product::query()->active()->trending()->withAvailableVariantStock()
                    ->with(['brand', 'category.parent', 'media'])
                    ->orderByDesc('updated_at')->orderByDesc('id')->limit(10)->get(),
                'bestDeals' => Product::query()->active()->withAvailableVariantStock()
                    ->whereNotNull('compare_at_price')
                    ->whereColumn('compare_at_price', '>', 'price')
                    ->with(['brand', 'category.parent', 'media'])
                    ->orderByRaw('(compare_at_price - price) / NULLIF(compare_at_price, 0) DESC')
                    ->orderByDesc('updated_at')
                    ->limit(8)
                    ->get(),
                // Distinct set from New Arrivals (reference uses a separate pool) —
                // fresh gear that has just landed in the store.
                'recentlyLaunched' => $this->curatedProducts([
                    'roland-fp-30x-digital-piano',
                    'krk-rokit-5-g4-studio-monitor-single',
                    'akg-k240-studio-headphones',
                    'fender-mustang-lt25-modelling-amp',
                    'casio-ct-s300-portable-keyboard',
                    'numark-mixtrack-pro-fx',
                ]),
                'brandNames' => Brand::query()->orderBy('name')->limit(16)->pluck('name'),
                'categoryRows' => $categoryRows,
                'popularCategories' => $this->popularCategories($categoryRows->pluck('category')),
            ];
        });
    }

    /**
     * Fetch products by slug, preserving the curated order.
     *
     * @param  list<string>  $slugs
     * @return Collection<int, Product>
     */
    private function curatedProducts(array $slugs): Collection
    {
        $products = Product::query()->active()->withAvailableVariantStock()
            ->whereIn('slug', $slugs)
            ->with(['brand', 'category.parent', 'media'])
            ->get();

        return collect($slugs)
            ->map(fn (string $slug) => $products->firstWhere('slug', $slug))
            ->filter()
            ->values();
    }

    /**
     * @return Collection<int, array{row:HomepageCategoryRow,category:Category,products:Collection<int,Product>}>
     */
    private function categoryRows(): Collection
    {
        return HomepageCategoryRow::query()
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query
                ->where('is_active', true)
                ->whereHas('products', fn ($products) => $products->active()))
            ->with('category:id,name,slug,is_active')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(self::MAX_CATEGORY_ROWS)
            ->get()
            ->map(function (HomepageCategoryRow $row): array {
                $products = Product::query()
                    ->active()
                    ->withAvailableVariantStock()
                    ->where('category_id', $row->category_id)
                    ->with(['brand', 'category.parent', 'media'])
                    ->orderByRaw('featured_rank IS NULL')
                    ->orderBy('featured_rank')
                    ->orderByDesc('updated_at')
                    ->limit($row->boundedProductLimit())
                    ->get();

                return [
                    'row' => $row,
                    'category' => $row->category,
                    'products' => $products,
                ];
            });
    }

    /**
     * Configured row categories lead the existing curated fallback.
     * Root counts include direct products and immediate child categories.
     *
     * @param  Collection<int, Category>  $configured
     * @return Collection<int, array{name:string, slug:string, count:int, image:?string}>
     */
    private function popularCategories(Collection $configured): Collection
    {
        $configuredOrder = $configured->pluck('slug')->values();
        $categories = Category::query()
            ->where('is_active', true)
            ->where(function ($query): void {
                $query->whereHas('products', fn ($products) => $products->active())
                    ->orWhereHas('children.products', fn ($products) => $products->active());
            })
            ->with('children:id,parent_id')
            ->orderByRaw('parent_id IS NOT NULL')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(self::MAX_DISCOVERY_CATEGORIES * 2)
            ->get(['id', 'parent_id', 'name', 'slug', 'sort_order']);
        $categoryIds = $categories
            ->flatMap(fn (Category $category) => $category->children->pluck('id')->push($category->id))
            ->unique();
        $counts = Product::query()
            ->active()
            ->whereIn('category_id', $categoryIds)
            ->selectRaw('category_id, count(*) as aggregate')
            ->groupBy('category_id')
            ->pluck('aggregate', 'category_id');

        return $categories
            ->map(function (Category $category) use ($counts, $configuredOrder): array {
                $ids = $category->children->pluck('id')->push($category->id);
                $count = $ids->sum(fn (int $id): int => (int) ($counts[$id] ?? 0));
                $asset = 'images/categories/'.$category->slug.'.jpg';
                // Category art is a local managed asset. Avoid a per-category
                // product/media fallback query on a cold homepage cache.
                $image = is_file(public_path($asset)) ? '/'.$asset : null;
                $configuredRank = $configuredOrder->search($category->slug);

                return [
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'count' => $count,
                    'image' => $image,
                    '_configured_rank' => $configuredRank === false ? PHP_INT_MAX : $configuredRank,
                ];
            })
            ->sortBy(fn (array $category): array => [$category['_configured_rank'], -$category['count'], $category['name']])
            ->take(self::MAX_DISCOVERY_CATEGORIES)
            ->map(function (array $category): array {
                unset($category['_configured_rank']);

                return $category;
            })
            ->values();
    }
}
