<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ShopFilters;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductMerchandisingRule;
use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Single responsibility: build the filtered, sorted, eager-loaded shop
 * product query. All filters are validated/normalised before hitting SQL.
 */
final class ProductQueryService
{
    private const PER_PAGE = 12;

    public function shopQuery(ShopFilters $filters): Builder
    {
        $query = Product::query()
            ->active()
            ->with(['brand', 'category', 'media'])
            ->withAvg(['reviews as reviews_avg_rating' => fn (Builder $review): Builder => $review
                ->where('status', Review::STATUS_APPROVED)
                ->where('is_approved', true)], 'rating')
            ->withCount(['reviews as reviews_count' => fn (Builder $review): Builder => $review
                ->where('status', Review::STATUS_APPROVED)
                ->where('is_approved', true)]);

        $this->applyCategoryFilter($query, $filters->category);
        $this->applyBrandFilter($query, $filters->brands);
        $this->applyPriceFilter($query, $filters->minPrice, $filters->maxPrice);
        $this->applyAvailabilityFilter($query, $filters->inStockOnly);
        $this->applySaleFilter($query, $filters->onSale);
        $this->applySearchFilter($query, $filters->search);
        $this->applyRatingFilter($query, $filters->minRating);
        $this->applyAttributeFilters($query, $filters->attributes);
        $this->applySort($query, $filters->sort, trim((string) $filters->search) !== '');

        return $query;
    }

    public function paginate(Builder $query): LengthAwarePaginator
    {
        return $query->paginate(self::PER_PAGE)->withQueryString();
    }

    /**
     * Curated related products first, then a bounded same-category fallback.
     * Admin rules only change discovery order; product price/stock stay owned
     * by the Product model and are never copied into a recommendation.
     *
     * @return Collection<int, Product>
     */
    public function related(
        Product $product,
        int $take = 4,
        string $ruleType = ProductMerchandisingRule::TYPE_RELATED,
    ): Collection {
        $take = max(1, min(12, $take));
        $curatedIds = ProductMerchandisingRule::query()
            ->activeNow()
            ->where('source_product_id', $product->id)
            ->where('rule_type', $ruleType)
            ->orderByDesc('priority')
            ->orderBy('id')
            ->limit($take)
            ->pluck('target_product_id')
            ->map(static fn ($id): int => (int) $id)
            ->values()
            ->all();

        $curated = Product::query()
            ->active()
            ->whereKey($curatedIds)
            ->whereKeyNot($product->id)
            ->with(['brand', 'media'])
            ->get()
            ->keyBy('id');
        $curatedInRuleOrder = new Collection(array_values(array_filter(array_map(
            static fn (int $id): ?Product => $curated->get($id),
            $curatedIds,
        ))));

        if ($curatedInRuleOrder->count() >= $take || $ruleType !== ProductMerchandisingRule::TYPE_RELATED) {
            return $curatedInRuleOrder->take($take)->values();
        }

        $fallback = Product::query()
            ->active()
            ->whereKeyNot($product->id)
            ->whereNotIn('id', $curatedInRuleOrder->pluck('id')->all())
            ->where(function (Builder $q) use ($product): void {
                $q->where('category_id', $product->category_id)
                    ->orWhereHas('category', fn (Builder $c): Builder => $c->where('parent_id', $product->category?->parent_id));
            })
            ->with(['brand', 'media'])
            ->orderByRaw('(COALESCE(compare_at_price, 0) - price) DESC')
            ->orderByDesc('id')
            ->limit($take - $curatedInRuleOrder->count())
            ->get();

        return $curatedInRuleOrder->concat($fallback)->values();
    }

    /**
     * Resolve a bounded, session-supplied recently-viewed list while preserving
     * its customer-visible order. Only active products can reappear.
     *
     * @param list<int> $productIds
     * @return Collection<int, Product>
     */
    public function recentlyViewed(array $productIds, int $take = 4): Collection
    {
        $ids = array_slice(array_values(array_unique(array_filter(
            array_map('intval', $productIds),
            fn (int $id): bool => $id > 0,
        ))), 0, max(1, min(12, $take)));

        if ($ids === []) {
            return new Collection();
        }

        $products = Product::query()
            ->active()
            ->whereKey($ids)
            ->with(['brand', 'category', 'media'])
            ->get()
            ->keyBy('id');

        return new Collection(array_values(array_filter(array_map(
            fn (int $id): ?Product => $products->get($id),
            $ids,
        ))));
    }

    /** Matches a child category directly, or products inside a parent category. */
    private function applyCategoryFilter(Builder $query, ?string $categorySlug): void
    {
        if ($categorySlug === null || $categorySlug === '') {
            return;
        }

        $query->whereHas('category', function (Builder $q) use ($categorySlug): void {
            $q->where('slug', $categorySlug)
                ->orWhereHas('parent', fn (Builder $parent): Builder => $parent->where('slug', $categorySlug));
        });
    }

    /** @param string[] $brandSlugs */
    private function applyBrandFilter(Builder $query, array $brandSlugs): void
    {
        $brandSlugs = array_values(array_filter($brandSlugs));

        if ($brandSlugs === []) {
            return;
        }

        $brandIds = Brand::query()
            ->whereIn('slug', $brandSlugs)
            ->pluck('id');

        $query->whereIn('brand_id', $brandIds);
    }

    private function applyPriceFilter(Builder $query, ?int $minPrice, ?int $maxPrice): void
    {
        $min = $minPrice !== null ? max(0, $minPrice) : null;
        $max = $maxPrice !== null ? max(0, $maxPrice) : null;

        if ($min !== null && $max !== null && $min > $max) {
            [$min, $max] = [$max, $min];
        }

        if ($min !== null) {
            $query->where('price', '>=', $min);
        }

        if ($max !== null) {
            $query->where('price', '<=', $max);
        }
    }

    private function applyAvailabilityFilter(Builder $query, bool $inStockOnly): void
    {
        if ($inStockOnly) {
            $query->where('stock', '>', 0);
        }
    }

    /** On sale = a real compare-at price above the selling price. */
    private function applySaleFilter(Builder $query, bool $onSale): void
    {
        if ($onSale) {
            $query->whereNotNull('compare_at_price')
                ->whereColumn('compare_at_price', '>', 'price');
        }
    }

    private function applySearchFilter(Builder $query, ?string $search): void
    {
        $term = trim((string) $search);

        if ($term === '') {
            return;
        }

        // Portable MySQL 8/SQLite baseline. The bounded token/stem fallbacks
        // provide useful typo tolerance without a persistent search service.
        $term = mb_substr($term, 0, 80);
        $likeTerm = $this->escapeLike($term);
        $tokens = array_values(array_filter(preg_split('/\s+/', $term, -1, PREG_SPLIT_NO_EMPTY) ?: []));
        $tokens = array_slice($tokens, 0, 5);

        $query->selectRaw('products.*, CASE
            WHEN products.name = ? THEN 120
            WHEN products.sku = ? THEN 115
            WHEN products.name LIKE ? THEN 90
            WHEN products.sku LIKE ? THEN 85
            ELSE 0
        END AS search_relevance', [$term, $term, "%{$likeTerm}%", "%{$likeTerm}%"]);

        $query->where(function (Builder $q) use ($likeTerm, $tokens): void {
            $matches = function (Builder $match, string $value): void {
                $match->where('products.name', 'like', "%{$value}%")
                    ->orWhere('products.sku', 'like', "%{$value}%")
                    ->orWhereHas('brand', fn (Builder $brand): Builder => $brand->where('name', 'like', "%{$value}%"))
                    ->orWhereHas('category', fn (Builder $category): Builder => $category
                        ->where('name', 'like', "%{$value}%")
                        ->orWhere('slug', 'like', "%{$value}%"))
                    ->orWhereHas('attributeValues', fn (Builder $attribute): Builder => $attribute
                        ->where('value', 'like', "%{$value}%")
                        ->orWhere('slug', 'like', "%{$value}%"))
                    ->orWhereHas('variants.attributeValues', fn (Builder $attribute): Builder => $attribute
                        ->where('value', 'like', "%{$value}%")
                        ->orWhere('slug', 'like', "%{$value}%"));
            };

            $matches($q, $likeTerm);

            foreach ($tokens as $token) {
                if (mb_strlen($token) < 5) {
                    continue;
                }

                // Example: "guitr" still discovers "guitar". The one-char
                // stem rule is intentionally bounded to avoid broad scans.
                $stem = $this->escapeLike(mb_substr($token, 0, -1));
                $q->orWhere(fn (Builder $fallback): Builder => $fallback
                    ->where('products.name', 'like', "%{$stem}%")
                    ->orWhere('products.sku', 'like', "%{$stem}%")
                    ->orWhereHas('brand', fn (Builder $brand): Builder => $brand->where('name', 'like', "%{$stem}%"))
                    ->orWhereHas('category', fn (Builder $category): Builder => $category->where('name', 'like', "%{$stem}%")));
            }
        });
    }

    private function applyRatingFilter(Builder $query, ?int $minRating): void
    {
        if ($minRating === null) {
            return;
        }

        $minimum = min(5, max(1, $minRating));
        $ratedProductIds = Review::query()
            ->select('product_id')
            ->where('status', Review::STATUS_APPROVED)
            ->where('is_approved', true)
            ->groupBy('product_id')
            ->havingRaw('AVG(rating) >= ?', [$minimum]);

        $query->whereIn('id', $ratedProductIds);
    }

    /** @param array<string, string[]> $attributes */
    private function applyAttributeFilters(Builder $query, array $attributes): void
    {
        foreach ($attributes as $attributeSlug => $valueSlugs) {
            $values = array_values(array_filter(array_map('strval', (array) $valueSlugs)));

            if ($values === []) {
                continue;
            }

            $query->where(function (Builder $product) use ($attributeSlug, $values): void {
                $matchesValue = fn (Builder $value): Builder => $value
                    ->whereIn('slug', $values)
                    ->whereHas('attribute', fn (Builder $attribute): Builder => $attribute
                        ->where('slug', (string) $attributeSlug)
                        ->where('is_active', true)
                        ->where('is_filterable', true));

                $product->whereHas('attributeValues', $matchesValue)
                    ->orWhereHas('variants.attributeValues', $matchesValue);
            });
        }
    }

    private function applySort(Builder $query, string $sort, bool $hasSearch = false): void
    {
        switch ($sort) {
            case 'price-asc':
                $query->orderBy('price')->orderBy('id');
                break;

            case 'price-desc':
                $query->orderByDesc('price')->orderBy('id');
                break;

            case 'newest':
                $query->orderByDesc('created_at')->orderByDesc('id');
                break;

            case 'discount':
                $query->orderByRaw('(COALESCE(compare_at_price, 0) - price) DESC')->orderBy('id');
                break;

            default: // featured
                if ($hasSearch) {
                    $query->orderByDesc('search_relevance');
                }
                $query->orderByDesc('is_featured')->orderByDesc('featured_rank')->orderByDesc('id');
        }
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
