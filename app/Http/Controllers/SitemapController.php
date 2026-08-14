<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Http\Response;

final class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = [];

        // Home
        $urls[] = ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily'];
        $urls[] = ['loc' => route('shop.index'), 'priority' => '0.9', 'changefreq' => 'daily'];

        // Dynamic pages (about, contact, support…)
        foreach (Page::query()->whereNotNull('slug')->where('is_active', true)->get() as $page) {
            $urls[] = ['loc' => url('/'.$page->slug), 'priority' => '0.8', 'changefreq' => 'monthly'];
        }

        // Categories
        foreach (Category::query()->where('is_active', true)->get() as $category) {
            $urls[] = ['loc' => route('shop.index', ['category' => $category->slug]), 'priority' => '0.7', 'changefreq' => 'weekly'];
        }

        // Products
        foreach (Product::query()->active()->get() as $product) {
            $urls[] = ['loc' => route('product.show', $product), 'priority' => '0.6', 'changefreq' => 'weekly'];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.e($url['loc'])."</loc>\n";
            $xml .= '    <changefreq>'.$url['changefreq']."</changefreq>\n";
            $xml .= '    <priority>'.$url['priority']."</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml)->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $content = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /cart',
            'Disallow: /checkout',
            'Disallow: /account',
            'Disallow: /orders',
            'Disallow: /track-order',
            'Disallow: /wishlist',
            'Disallow: /login',
            'Disallow: /register',
            '',
            'Sitemap: '.url('/sitemap.xml'),
            '',
        ]);

        return response($content)->header('Content-Type', 'text/plain');
    }
}
