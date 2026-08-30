<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Product;
use App\Models\ProductMerchandisingRule;
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

        $session = request()->session();
        $recentIds = array_values(array_filter(
            array_map('intval', (array) $session->get('storefront.recent_products', [])),
            fn (int $id): bool => $id > 0 && $id !== $product->id,
        ));
        $recentlyViewed = $this->products->recentlyViewed($recentIds);
        $session->put('storefront.recent_products', array_slice([
            $product->id,
            ...array_values(array_unique($recentIds)),
        ], 0, 12));

        return view('product.show', [
            'product' => $product,
            'related' => $this->products->related($product),
            'complementary' => $this->products->related($product, 4, ProductMerchandisingRule::TYPE_COMPLEMENTARY),
            'frequentlyBought' => $this->products->related($product, 4, ProductMerchandisingRule::TYPE_FREQUENTLY_BOUGHT_TOGETHER),
            'recentlyViewed' => $recentlyViewed,
            'productFaqs' => Faq::query()->where('is_active', true)->orderBy('sort_order')->limit(5)->get(),
            'reviewSummary' => $this->reviews->summary($product),
        ]);
    }
}
