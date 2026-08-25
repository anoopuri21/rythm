<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ShopFilters;
use App\Models\Brand;
use App\Models\Product;
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
        $this->applySort($query, $filters->sort);

        return $query;
    }

    public function paginate(Builder $query): LengthAwarePaginator
    {
        return $query->paginate(self::PER_PAGE)->withQueryString();
    }

    /**
     * Related products: same category (or parent category), excluding self,
     * highest discount first. Eager-loaded, capped.
     *
     * @return Collection<int, Product>
     */
    public function related(Product $product, int $take = 4): Collection
    {
        return Product::query()
            ->active()
            ->whereKeyNot($product->id)
            ->where(function (Builder $q) use ($product): void {
                $q->where('category_id', $product->category_id)
                    ->orWhereHas('category', fn (Builder $c): Builder => $c->where('parent_id', $product->category?->parent_id));
            })
            ->with(['brand', 'media'])
            ->orderByRaw('(COALESCE(compare_at_price, 0) - price) DESC')
            ->orderByDesc('id')
            ->limit($take)
            ->get();
    }

    /**
     * Matches a child category directly, or every product inside a
     * parent category (and its children).
     */
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

    /**
     * @param  string[]  $brandSlugs
     */
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

        $query->where(function (Builder $q) use ($term): void {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('sku', 'like', "%{$term}%")
                ->orWhereHas('brand', fn (Builder $brand): Builder => $brand->where('name', 'like', "%{$term}%"));
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

    /**
     * Values inside one attribute are ORed; separate attributes are ANDed.
     * Product-level and variant-level normalized assignments both qualify.
     *
     * @param  array<string, string[]>  $attributes
     */
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

    private function applySort(Builder $query, string $sort): void
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
                // Static expression, zero user input — safe raw sort.
                $query->orderByRaw('(COALESCE(compare_at_price, 0) - price) DESC')->orderBy('id');
                break;

            default: // featured
                $query->orderByDesc('is_featured')->orderByDesc('id');
        }
    }
}
