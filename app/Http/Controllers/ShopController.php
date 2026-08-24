<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\SeoService;
use Illuminate\View\View;

final class ShopController extends Controller
{
    public function __construct(private readonly SeoService $seo) {}

    public function index(): View
    {
        // On-page SEO managed from admin (Page anchor with slug 'shop').
        $shopPage = Page::query()->where('slug', 'shop')->with('seoEntry')->first();

        $this->seo->apply(SeoService::fromEntry($shopPage?->seoEntry, [
            'meta_title' => 'Shop All Instruments — Guitars, Keyboards, Drums, Pro Audio | Rhythm Exports',
            'meta_description' => 'Browse the full Rhythm Exports catalogue — acoustic and electric guitars, digital pianos, electronic drums, pro audio and accessories from Fender, Yamaha, Roland, Shure and more. Free shipping all over India.',
        ]));

        return view('shop.index');
    }
}
