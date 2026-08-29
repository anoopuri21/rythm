<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductQueryService;
use App\Services\ReviewService;
use App\Services\SeoService;
use Illuminate\View\View;

final class ProductController extends Controller
{
    public function __construct(
        private readonly ProductQueryService $products,
        private readonly ReviewService $reviews,
        private readonly SeoService $seo,
    ) {}

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load([
            'brand',
            'category.parent',
            'variants' => fn ($query) => $query->where('is_active', true),
            'media',
            'seoEntry',
        ]);

        $this->seo->apply(SeoService::fromEntry($product->seoEntry, [
            'meta_title' => $product->meta_title ?: $product->name.' — Buy Online in India | Rythme Music Store',
            'meta_description' => $product->meta_description ?: (string) $product->short_description,
            'og_image' => $product->getFirstMediaUrl('og') ?: $product->heroImage(),
        ]));

        return view('product.show', [
            'product' => $product,
            'related' => $this->products->related($product),
            'reviewSummary' => $this->reviews->summary($product),
        ]);
    }
}
