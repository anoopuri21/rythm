<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\SeoService;
use Illuminate\View\View;

final class PageController extends Controller
{
    public function __construct(private readonly SeoService $seo) {}

    public function show(string $slug): View
    {
        $page = Page::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with('seoEntry')
            ->firstOrFail();

        $this->seo->apply(SeoService::fromEntry($page->seoEntry, [
            'meta_title' => $page->title.' — Rythme Music Store',
            'meta_description' => strip_tags((string) $page->content),
        ]));

        return view('pages.show', ['page' => $page]);
    }
}
