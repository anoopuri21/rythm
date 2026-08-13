<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\HomepageSection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

final class HomeController extends Controller
{
    public function index(): View
    {
        // Hero mode is config-driven (RYTHME_HERO_MODE env): 'slider' | 'video'
        $heroMode = config('rythme.hero_mode', 'slider');

        // Admin-editable section content (kicker/title/accent/body) —
        // cached 1h, flushed instantly by HomepageSectionObserver.
        $homeSections = Cache::remember('homepage.sections', 3600, function (): array {
            return HomepageSection::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->keyBy('section_key')
                ->all();
        });

        return view('home.index', compact('heroMode', 'homeSections'));
    }
}
