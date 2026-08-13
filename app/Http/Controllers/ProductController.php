<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductQueryService;
use Illuminate\View\View;

final class ProductController extends Controller
{
    public function __construct(private readonly ProductQueryService $products) {}

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load([
            'brand',
            'category.parent',
            'variants' => fn ($query) => $query->where('is_active', true),
            'media',
        ]);

        return view('product.show', [
            'product' => $product,
            'related' => $this->products->related($product),
        ]);
    }
}
