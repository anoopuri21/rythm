<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Faq;
use App\Models\HeroSlide;
use App\Models\HomepageBlock;
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
     *   popularCategories: Collection<int, array{name:string, slug:string, count:int}>,
     * }
     */
    public function all(): array
    {
        return Cache::remember(HomepageDataObserver::CACHE_KEY, 3600, function (): array {
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
                'bestsellers' => Product::query()->active()->featured()
                    ->with(['brand', 'category.parent', 'media'])
                    ->orderByRaw('featured_rank IS NULL')->orderBy('featured_rank')->orderBy('updated_at', 'desc')->limit(8)->get(),
                // Curated (not pure created_at): deterministic set, every product
                // has a hero image, and order never drifts when new rows are seeded.
                'newArrivals' => $this->curatedProducts([
                    'yamaha-f310-acoustic-guitar',
                    'squier-affinity-stratocaster-hss',
                    'yamaha-trbx174-bass-guitar',
                    'yamaha-p-145-digital-piano',
                    'yamaha-psr-e373-portable-keyboard',
                    'alesis-nitro-mesh-kit',
                    'shure-sm58-vocal-microphone',
                    'pioneer-dj-ddj-flx4-controller',
                    'boss-katana-50-mkii',
                    'focusrite-scarlett-solo-3rd-gen',
                ]),
                'trending' => Product::query()->active()->trending()
                    ->with(['brand', 'category.parent', 'media'])
                    ->orderByDesc('updated_at')->limit(8)->get(),
                'dealsOfDay' => $this->curatedProducts([
                    'boss-katana-50-mkii',
                    'shure-sm58-vocal-microphone',
                    'focusrite-scarlett-solo-3rd-gen',
                    'alesis-nitro-mesh-kit',
                ]),
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
                'popularCategories' => $this->popularCategories(),
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
        $products = Product::query()->active()
            ->whereIn('slug', $slugs)
            ->with(['brand', 'category.parent', 'media'])
            ->get();

        return collect($slugs)
            ->map(fn (string $slug) => $products->firstWhere('slug', $slug))
            ->filter()
            ->values();
    }

    /**
     * Category cards for the "Popular Categories" carousel —
     * curated order: 6 roots + 4 popular subcategories.
     * Root counts include products from all child categories.
     *
     * @return Collection<int, array{name:string, slug:string, count:int}>
     */
    private function popularCategories(): Collection
    {
        $order = [
            'guitars', 'electric-guitars', 'acoustic-guitars',
            'keyboards-pianos', 'digital-pianos',
            'drums-percussion', 'pro-audio',
            'dj-stage', 'dj-controllers', 'accessories',
        ];

        $categories = Category::query()
            ->whereIn('slug', $order)
            ->where('is_active', true)
            ->with('children:id,parent_id')
            ->get(['id', 'parent_id', 'name', 'slug']);

        return collect($order)
            ->map(function (string $slug) use ($categories): ?array {
                $category = $categories->firstWhere('slug', $slug);
                if ($category === null) {
                    return null;
                }

                $ids = $category->children->pluck('id')->push($category->id);

                return [
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'count' => Product::query()->active()->whereIn('category_id', $ids)->count(),
                ];
            })
            ->filter()
            ->values();
    }
}
