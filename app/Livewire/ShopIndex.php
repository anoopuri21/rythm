<?php

declare(strict_types=1);

namespace App\Livewire;

use App\DTOs\ShopFilters;
use App\Services\BrandService;
use App\Services\CategoryService;
use App\Services\ProductQueryService;
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
    public string $sort = 'popularity';

    #[Url(as: 'instock', history: true)]
    public bool $inStockOnly = false;

    #[Url(as: 'on_sale', history: true)]
    public bool $onSale = false;

    #[Url(as: 'q', history: true)]
    public ?string $search = null;

    public function mount(): void
    {
        // Livewire 3.8: #[Url] array props are not hydrated from a plain
        // GET query string (only from its own pushed URLs) — hydrate here.
        $queryBrands = request()->query('brand');

        if (is_array($queryBrands)) {
            $this->selectedBrands = array_values(array_filter(array_map('strval', $queryBrands)));
        }
    }

    public function setCategory(?string $slug): void
    {
        $this->category = $slug === '' ? null : $slug;
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

    public function clearFilters(): void
    {
        $this->reset('category', 'selectedBrands', 'minPrice', 'maxPrice', 'inStockOnly', 'onSale', 'search');
        $this->sort = 'popularity';
        $this->resetPage();
    }

    public function render(): View
    {
        $products = app(ProductQueryService::class)
            ->paginate(app(ProductQueryService::class)->shopQuery($this->filters()));

        return view('livewire.shop-index', [
            'products' => $products,
            'categories' => app(CategoryService::class)->tree(),
            'brands' => app(BrandService::class)->allWithCounts(),
            'activeFilterCount' => $this->activeFilterCount(),
        ]);
    }

    private function filters(): ShopFilters
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
        );
    }

    private function activeFilterCount(): int
    {
        return count(array_filter([
            $this->category !== null,
            $this->selectedBrands !== [],
            $this->minPrice !== null,
            $this->maxPrice !== null,
            $this->inStockOnly,
            $this->onSale,
            trim((string) $this->search) !== '',
        ]));
    }
}
