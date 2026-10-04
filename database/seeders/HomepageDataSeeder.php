<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\HeroSlide;
use App\Models\HomepageBlock;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Seeds the admin-driven homepage content from the current hardcoded/
 * config data — so the homepage looks IDENTICAL after switching to DB.
 * Idempotent: safe to re-run.
 */
class HomepageDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedHeroSlides();
        $this->seedBlocks();
        $this->seedFaqs();
        $this->seedProductFlags();
    }

    private function seedHeroSlides(): void
    {
        $slides = [
            ['eyebrow' => 'High quality · Best sellers', 'title' => 'Premium gear.', 'accent' => 'Zero compromise.', 'copy' => 'Every instrument we ship is inspected, set up and ready to perform — from beginner favourites to stage-ready pro models. Real products, real quality.', 'cta_label' => 'Explore instruments', 'cta_href' => '/shop'],
            ['eyebrow' => 'High quality · Keys & pianos', 'title' => 'Play the piano.', 'accent' => 'Feel every note.', 'copy' => 'Digital pianos with weighted keys and rich, expressive sound — crafted for practice rooms and stages alike.', 'cta_label' => 'Shop keyboards', 'cta_href' => '/shop?category=keyboards-pianos'],
            ['eyebrow' => 'Craft your signature sound', 'title' => 'Feel the music.', 'accent' => 'Own the sound.', 'copy' => 'Browse active instruments, current catalogue pricing and available product specifications.', 'cta_label' => 'Explore instruments', 'cta_href' => '/shop'],
            ['eyebrow' => 'The keys to expression', 'title' => 'Every note.', 'accent' => 'Entirely yours.', 'copy' => 'From first melodies to concert stages, discover keys that move with your ambition.', 'cta_label' => 'Shop keyboards', 'cta_href' => '/shop?category=keyboards-pianos'],
            ['eyebrow' => 'Build your perfect studio', 'title' => 'Capture the moment.', 'accent' => 'Keep it forever.', 'copy' => 'Professional recording essentials selected for clarity, character and lasting performance.', 'cta_label' => 'Explore pro audio', 'cta_href' => '/shop?category=pro-audio'],
        ];

        foreach ($slides as $i => $slide) {
            HeroSlide::updateOrCreate(
                ['title' => $slide['title'], 'accent' => $slide['accent']],
                $slide + ['sort_order' => $i, 'is_active' => true],
            );
        }
    }

    private function seedBlocks(): void
    {
        $blocks = [
            // ── USP strip: exactly what the storefront showed while the copy
            //    was still hardcoded in _usp-strip.blade.php, now editable in
            //    Admin → Homepage blocks → "Why Rythme (USPs)".
            ['section_key' => 'usp', 'icon' => 'box', 'title' => 'Instrument-first', 'content' => 'catalogue for every stage', 'sort_order' => 0],
            ['section_key' => 'usp', 'icon' => 'truck', 'title' => 'Clear', 'content' => 'order tracking from your account', 'sort_order' => 1],
            ['section_key' => 'usp', 'icon' => 'shield-check', 'title' => 'Category-led', 'content' => 'browsing for faster discovery', 'sort_order' => 2],
            ['section_key' => 'usp', 'icon' => 'credit-card', 'title' => 'Secure checkout', 'content' => 'with server-verified totals', 'sort_order' => 3],
            ['section_key' => 'usp', 'icon' => 'sparkles', 'title' => 'Stock-aware', 'content' => 'product availability', 'sort_order' => 4],
            // ── Capability labels (no unsupported business metrics) ──
            ['section_key' => 'number', 'title' => 'Curated', 'content' => 'Instrument catalogue', 'sort_order' => 0],
            ['section_key' => 'number', 'title' => 'Verified', 'content' => 'Checkout totals', 'sort_order' => 1],
            ['section_key' => 'number', 'title' => 'Moderated', 'content' => 'Verified reviews', 'sort_order' => 2],
            ['section_key' => 'number', 'title' => 'Protected', 'content' => 'Order tracking', 'sort_order' => 3],
            // ── Stories ──
            ['section_key' => 'story', 'title' => 'First guitar, right way', 'content' => 'How to choose your first acoustic without breaking the bank.', 'sort_order' => 0],
            ['section_key' => 'story', 'title' => 'Studio on a budget', 'content' => 'Five essentials to start recording at home in 2026.', 'sort_order' => 1],
            ['section_key' => 'story', 'title' => 'Practice that sticks', 'content' => 'A simple 20-minute routine that actually builds skill.', 'sort_order' => 2],
            // ── UGC ──
            ['section_key' => 'ugc', 'title' => '#RythmeFamily', 'content' => 'Share your sound with the community — the best setups get featured here.', 'sort_order' => 0],
            // ── Verified platform rows ──
            ['section_key' => 'comparison', 'title' => 'Current catalogue pricing', 'subtitle' => 'Server-derived', 'content' => 'Rythme', 'sort_order' => 0],
            ['section_key' => 'comparison', 'title' => 'Verified-purchase reviews', 'subtitle' => 'Moderated', 'content' => 'Rythme', 'sort_order' => 1],
            ['section_key' => 'comparison', 'title' => 'Verified-purchase reviews', 'subtitle' => 'Moderated', 'content' => 'Rythme', 'sort_order' => 2],
            ['section_key' => 'comparison', 'title' => 'Protected order tracking', 'subtitle' => 'Signed or account access', 'content' => 'Rythme', 'sort_order' => 3],
            // ── Promos (3 big banners — reference style) ──
            ['section_key' => 'promo', 'title' => 'Enjoy studio-grade sound', 'subtitle' => 'Pro audio, simplified', 'content' => '/category/pro-audio', 'sort_order' => 0],
            ['section_key' => 'promo', 'title' => 'Keys for every stage', 'subtitle' => 'Pianos & keyboards', 'content' => '/category/keyboards-pianos', 'sort_order' => 1],
            ['section_key' => 'promo', 'title' => 'Browse available accessories', 'subtitle' => 'Current catalogue pricing', 'content' => '/shop?sort=discount', 'sort_order' => 2],
        ];

        // Superseded default USP rows. Deactivated rather than deleted: the
        // storefront only renders active rows (scopeSection), the admin list
        // still shows them, and an admin can switch one back on.
        HomepageBlock::query()
            ->where('section_key', 'usp')
            ->whereIn('title', [
                'Catalogue filters',
                'Server-verified totals',
                'Protected checkout',
                'Order tracking',
                'Verified reviews',
            ])
            ->update(['is_active' => false]);

        foreach ($blocks as $block) {
            HomepageBlock::updateOrCreate(
                ['section_key' => $block['section_key'], 'title' => $block['title']],
                $block,
            );
        }
    }

    private function seedFaqs(): void
    {
        $faqs = [
            ['question' => 'How are shipping charges calculated?', 'answer' => 'Any configured shipping charge is calculated from server settings and shown during checkout before payment.'],
            ['question' => 'How can I ask about an instrument?', 'answer' => 'Use the contact form or the product support link on the product page. The store team replies when available.'],
            ['question' => 'Who can submit a product review?', 'answer' => 'A customer with a paid, delivered order containing the product can submit one review for moderation.'],
            ['question' => 'Which payment methods can I use?', 'answer' => 'The configured payment provider shows the methods available for the specific checkout attempt.'],
            ['question' => 'How do I check warranty information?', 'answer' => 'Review product or manufacturer documentation and contact the store with the order number for product-specific assistance.'],
            ['question' => 'How can I track an order?', 'answer' => 'Use the protected order page in your account or the signed guest tracking journey with the order number and matching email.'],
        ];

        foreach ($faqs as $i => $faq) {
            Faq::updateOrCreate(['question' => $faq['question']], $faq + ['sort_order' => $i, 'is_active' => true]);
        }
    }

    private function seedProductFlags(): void
    {
        // Real DB products (config featured were legacy names — map by brand).
        $featuredSlugs = [
            'yamaha-f310-acoustic-guitar',
            'squier-affinity-stratocaster-hss',
            'roland-fp-30x-digital-piano',
            'alesis-nitro-mesh-kit',
            'focusrite-scarlett-solo-3rd-gen',
            'shure-sm58-vocal-microphone',
            'yamaha-psr-e373-portable-keyboard',
            'fender-cd-60s-dreadnought-acoustic-guitar',
        ];

        foreach ($featuredSlugs as $rank => $slug) {
            Product::where('slug', $slug)->update([
                'is_featured' => true,
                'featured_rank' => $rank,
            ]);
        }

        // Trending = a handpicked 6 (the old carousel feel)
        $trendingSlugs = [
            'yamaha-f310-acoustic-guitar',
            'roland-fp-30x-digital-piano',
            'squier-affinity-stratocaster-hss',
            'alesis-nitro-mesh-kit',
            'focusrite-scarlett-solo-3rd-gen',
            'shure-sm58-vocal-microphone',
        ];

        Product::whereIn('slug', $trendingSlugs)->update(['is_trending' => true]);
    }
}
