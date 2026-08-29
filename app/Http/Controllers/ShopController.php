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

        $safeDescription = 'Browse guitars, digital pianos, drums, pro audio and musical-instrument accessories from leading brands at Rhythm Exports.';
        $seo = SeoService::fromEntry($shopPage?->seoEntry, [
            'meta_title' => 'Shop All Instruments — Guitars, Keyboards, Drums, Pro Audio | Rhythm Exports',
        ]);
        $seo['meta_description'] = $safeDescription;
        $seo['og_description'] = $safeDescription;
        $seo['canonical_url'] = route('shop.index');
        $seo['robots'] = 'index, follow';

        $query = request()->query();
        $page = max(1, (int) ($query['page'] ?? 1));
        unset($query['page']);

        if ($query !== []) {
            $seo['canonical_url'] = route('shop.index');
            $seo['robots'] = 'noindex, follow';
        } elseif ($page > 1) {
            $seo['canonical_url'] = route('shop.index', ['page' => $page]);
        }

        $this->seo->apply($seo);

        return view('shop.index');
    }
}
