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
            // ── Verified storefront capabilities ──
            ['section_key' => 'usp', 'title' => 'Catalogue filters', 'content' => 'Browse by category, brand, price, stock and available specifications.', 'sort_order' => 0],
            ['section_key' => 'usp', 'title' => 'Server-verified totals', 'content' => 'Checkout recalculates current prices, discounts, shipping and tax.', 'sort_order' => 1],
            ['section_key' => 'usp', 'title' => 'Protected checkout', 'content' => 'Payment state and inventory transitions are verified on the server.', 'sort_order' => 2],
            ['section_key' => 'usp', 'title' => 'Order tracking', 'content' => 'Customers can follow recorded order-status updates through protected access.', 'sort_order' => 3],
            ['section_key' => 'usp', 'title' => 'Verified reviews', 'content' => 'Only paid, delivered purchases can submit moderated reviews.', 'sort_order' => 4],
            ['section_key' => 'usp', 'title' => 'Product Q&A', 'content' => 'Customer questions and staff answers are moderated before publication.', 'sort_order' => 5],
            // ── Capability labels (no unsupported business metrics) ──
            ['section_key' => 'number', 'title' => 'Curated', 'content' => 'Instrument catalogue', 'sort_order' => 0],
            ['section_key' => 'number', 'title' => 'Verified', 'content' => 'Checkout totals', 'sort_order' => 1],
            ['section_key' => 'number', 'title' => 'Moderated', 'content' => 'Reviews and Q&A', 'sort_order' => 2],
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
            ['section_key' => 'comparison', 'title' => 'Product questions', 'subtitle' => 'Staff answers', 'content' => 'Rythme', 'sort_order' => 2],
            ['section_key' => 'comparison', 'title' => 'Protected order tracking', 'subtitle' => 'Signed or account access', 'content' => 'Rythme', 'sort_order' => 3],
            // ── Promos (3 big banners — reference style) ──
            ['section_key' => 'promo', 'title' => 'Enjoy studio-grade sound', 'subtitle' => 'Pro audio, simplified', 'content' => '/category/pro-audio', 'sort_order' => 0],
            ['section_key' => 'promo', 'title' => 'Keys for every stage', 'subtitle' => 'Pianos & keyboards', 'content' => '/category/keyboards-pianos', 'sort_order' => 1],
            ['section_key' => 'promo', 'title' => 'Browse available accessories', 'subtitle' => 'Current catalogue pricing', 'content' => '/shop?sort=discount', 'sort_order' => 2],
        ];

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
            ['question' => 'How can I ask about an instrument?', 'answer' => 'Signed-in customers can submit a product question. Staff answers appear only after moderation and approval.'],
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
