<?php

declare(strict_types=1);

namespace App\Livewire;

use App\DTOs\ShopFilters;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Services\BrandService;
use App\Services\CategoryService;
use App\Services\ProductQueryService;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class ShopIndex extends Component
{
    use WithPagination;

    #[Url(as: 'category', history: true)]
    public ?string $category = null;

    /** @var string[] */
    #[Url(as: 'brand[]', history: true)]
    public array $selectedBrands = [];

    #[Url(as: 'min', history: true)]
    public ?int $minPrice = null;

    #[Url(as: 'max', history: true)]
    public ?int $maxPrice = null;

    #[Url(as: 'sort', history: true)]
    public string $sort = 'featured';

    #[Url(as: 'rating', history: true)]
    public ?int $minRating = null;

    /** @var array<string, string[]> */
    #[Url(as: 'attribute', history: true)]
    public array $selectedAttributes = [];

    #[Url(as: 'instock', history: true)]
    public bool $inStockOnly = false;

    #[Url(as: 'on_sale', history: true)]
    public bool $onSale = false;

    #[Url(as: 'q', history: true)]
    public ?string $search = null;

    public function mount(): void
    {
        // Normalize nested arrays supplied by a plain GET query string.
        $queryBrands = request()->query('brand');
        $queryAttributes = request()->query('attribute');

        if (is_array($queryBrands)) {
            $this->selectedBrands = array_values(array_filter(array_map('strval', $queryBrands)));
        }

        if (is_array($queryAttributes)) {
            $this->selectedAttributes = collect($queryAttributes)
                ->map(fn ($values): array => array_values(array_filter(array_map('strval', (array) $values))))
                ->filter()
                ->all();
        }
    }

    public function setCategory(?string $slug): void
    {
        $this->category = $slug === '' ? null : $slug;
        $this->selectedAttributes = [];
        $this->resetPage();
    }

    public function toggleBrand(string $slug): void
    {
        $this->selectedBrands = in_array($slug, $this->selectedBrands, true)
            ? array_values(array_diff($this->selectedBrands, [$slug]))
            : [...$this->selectedBrands, $slug];

        $this->resetPage();
    }

    public function setSort(string $sort): void
    {
        $this->sort = $sort;
        $this->resetPage();
    }

    public function toggleInStock(): void
    {
        $this->inStockOnly = ! $this->inStockOnly;
        $this->resetPage();
    }

    public function toggleOnSale(): void
    {
        $this->onSale = ! $this->onSale;
        $this->resetPage();
    }

    public function setMinRating(?int $rating): void
    {
        $this->minRating = $rating === null ? null : min(5, max(1, $rating));
        $this->resetPage();
    }

    public function toggleAttribute(string $attribute, string $value): void
    {
        $selected = $this->selectedAttributes[$attribute] ?? [];
        $selected = in_array($value, $selected, true)
            ? array_values(array_diff($selected, [$value]))
            : [...$selected, $value];

        if ($selected === []) {
            unset($this->selectedAttributes[$attribute]);
        } else {
            $this->selectedAttributes[$attribute] = $selected;
        }

        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('category', 'selectedBrands', 'minPrice', 'maxPrice', 'inStockOnly', 'onSale', 'search', 'minRating', 'selectedAttributes');
        $this->sort = 'featured';
        $this->resetPage();
    }

    public function render(): View
    {
        $attributeFacets = $this->attributeFacets();
        $attributeSelections = $this->normalizedAttributeSelections($attributeFacets);
        $products = app(ProductQueryService::class)
            ->paginate(app(ProductQueryService::class)->shopQuery($this->filters($attributeSelections)));

        return view('livewire.shop-index', [
            'products' => $products,
            'catalogHasProducts' => Product::query()->active()->exists(),
            'categories' => app(CategoryService::class)->tree(),
            'brands' => app(BrandService::class)->allWithCounts(),
            'attributeFacets' => $attributeFacets,
            'attributeSelections' => $attributeSelections,
            'activeFilterCount' => $this->activeFilterCount($attributeSelections),
        ]);
    }

    /** @param array<string, string[]> $attributeSelections */
    private function filters(array $attributeSelections): ShopFilters
    {
        return new ShopFilters(
            category: $this->category,
            brands: $this->selectedBrands,
            minPrice: $this->minPrice,
            maxPrice: $this->maxPrice,
            sort: $this->sort,
            inStockOnly: $this->inStockOnly,
            onSale: $this->onSale,
            search: $this->search,
            minRating: $this->minRating,
            attributes: $attributeSelections,
        );
    }

    /**
     * @param  Collection<int, ProductAttribute>  $facets
     * @return array<string, string[]>
     */
    private function normalizedAttributeSelections(Collection $facets): array
    {
        return $facets
            ->mapWithKeys(function (ProductAttribute $attribute): array {
                $allowedValues = $attribute->values->pluck('slug')->all();
                $selected = array_values(array_intersect(
                    $this->selectedAttributes[$attribute->slug] ?? [],
                    $allowedValues,
                ));

                return $selected === [] ? [] : [$attribute->slug => $selected];
            })
            ->all();
    }

    /** @param array<string, string[]> $attributeSelections */
    private function activeFilterCount(array $attributeSelections): int
    {
        return count(array_filter([
            $this->category !== null,
            $this->selectedBrands !== [],
            $this->minPrice !== null,
            $this->maxPrice !== null,
            $this->inStockOnly,
            $this->onSale,
            trim((string) $this->search) !== '',
            $this->minRating !== null,
            $attributeSelections !== [],
        ]));
    }

    /**
     * Return only category-applicable attributes with real assigned values.
     * Parent-category selection includes attributes attached to its children.
     *
     * @return Collection<int, ProductAttribute>
     */
    private function attributeFacets(): Collection
    {
        if ($this->category === null) {
            return collect();
        }

        $category = Category::query()->with('children:id,parent_id')->where('slug', $this->category)->first();

        if ($category === null) {
            return collect();
        }

        $categoryIds = $category->children->pluck('id')->push($category->id)->all();

        return ProductAttribute::query()
            ->where('is_active', true)
            ->where('is_filterable', true)
            ->whereHas('categories', fn ($query) => $query
                ->whereIn('categories.id', $categoryIds)
                ->where('category_product_attribute.is_filterable', true))
            ->with(['values' => fn ($query) => $query
                ->where(function ($value) use ($categoryIds): void {
                    $value->whereHas('products', fn ($product) => $product
                        ->active()
                        ->whereIn('category_id', $categoryIds))
                        ->orWhereHas('variants.product', fn ($product) => $product
                            ->active()
                            ->whereIn('category_id', $categoryIds));
                })
                ->orderBy('sort_order')
                ->orderBy('value')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->filter(fn (ProductAttribute $attribute): bool => $attribute->values->isNotEmpty())
            ->values();
    }
}
