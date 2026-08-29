<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\HomepageSection;
use App\Models\Page;
use App\Services\HomepageDataService;
use App\Services\SeoService;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

final class HomeController extends Controller
{
    public function __construct(
        private readonly SeoService $seo,
        private readonly HomepageDataService $homepage,
    ) {}

    public function index(): View
    {
        // Hero mode is config-driven (RYTHME_HERO_MODE env): 'slider' | 'video'
        $heroMode = config('rythme.hero_mode', 'slider');

        // Admin-editable section headings (kicker/title/accent/body)
        $homeSections = Cache::remember('homepage.sections', 3600, function (): array {
            return HomepageSection::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->keyBy('section_key')
                ->all();
        });

        // Homepage on-page SEO — managed from admin (Page with slug = null).
        $homePage = Cache::remember('homepage.seo', 3600, fn (): ?Page => Page::query()
            ->whereNull('slug')
            ->with('seoEntry')
            ->first());

        $safeDescription = 'Explore guitars, keyboards, drums, pro audio and musical-instrument accessories from leading brands at Rhythm Exports.';
        $seo = SeoService::fromEntry($homePage?->seoEntry, [
            'meta_title' => 'Rhythm Exports - Feel The Music, Own The Sound',
        ]);
        $seo['meta_description'] = $safeDescription;
        $seo['og_description'] = $safeDescription;
        $seo['canonical_url'] = route('home');
        $seo['robots'] = 'index, follow';
        $this->seo->apply($seo);

        // ALL homepage content — DB-driven + cached (hero, blocks, faqs, products)
        $homepage = $this->homepage->all();

        return view('home.index', compact('heroMode', 'homeSections', 'homepage'));
    }
}
