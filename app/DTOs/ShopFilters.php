<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * Immutable shop filter state passed from Livewire → service.
 */
final readonly class ShopFilters
{
    /**
     * @param  string[]  $brands
     * @param  array<string, string[]>  $attributes
     */
    public function __construct(
        public ?string $category = null,
        public array $brands = [],
        public ?int $minPrice = null,
        public ?int $maxPrice = null,
        public string $sort = 'featured',
        public bool $inStockOnly = false,
        public bool $onSale = false,
        public ?string $search = null,
        public ?int $minRating = null,
        public array $attributes = [],
    ) {}
}
