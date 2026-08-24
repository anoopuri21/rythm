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
            ['eyebrow' => 'High quality · Keys & pianos', 'title' => 'Play the piano.', 'accent' => 'Feel every note.', 'copy' => 'Digital pianos with weighted keys and rich, expressive sound — crafted for practice rooms and stages alike.', 'cta_label' => 'Shop keyboards', 'cta_href' => '/category/keyboards-pianos'],
            ['eyebrow' => 'Craft your signature sound', 'title' => 'Feel the music.', 'accent' => 'Own the sound.', 'copy' => 'Handpicked instruments, expertly set up and delivered with care anywhere in India.', 'cta_label' => 'Explore instruments', 'cta_href' => '/shop'],
            ['eyebrow' => 'The keys to expression', 'title' => 'Every note.', 'accent' => 'Entirely yours.', 'copy' => 'From first melodies to concert stages, discover keys that move with your ambition.', 'cta_label' => 'Shop keyboards', 'cta_href' => '/category/keyboards-pianos'],
            ['eyebrow' => 'Build your perfect studio', 'title' => 'Capture the moment.', 'accent' => 'Keep it forever.', 'copy' => 'Professional recording essentials selected for clarity, character and lasting performance.', 'cta_label' => 'Explore pro audio', 'cta_href' => '/category/pro-audio'],
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
            // ── USPs (Why Rythme) ──
            ['section_key' => 'usp', 'title' => 'Free expert setup', 'content' => 'Every guitar is strung, intonated and inspected before it ships.', 'sort_order' => 0],
            ['section_key' => 'usp', 'title' => '1-year warranty', 'content' => 'All instruments covered against manufacturing defects.', 'sort_order' => 1],
            ['section_key' => 'usp', 'title' => 'Free shipping', 'content' => 'Across India, fully insured, with tracking.', 'sort_order' => 2],
            ['section_key' => 'usp', 'title' => 'Real humans', 'content' => 'Musicians answering your questions, not scripts.', 'sort_order' => 3],
            ['section_key' => 'usp', 'title' => 'EMI available', 'content' => 'Split payments via Razorpay at checkout.', 'sort_order' => 4],
            ['section_key' => 'usp', 'title' => 'Easy returns', 'content' => '7-day no-questions returns on unused items.', 'sort_order' => 5],
            // ── Numbers ──
            ['section_key' => 'number', 'title' => '12+', 'content' => 'Years of craft', 'sort_order' => 0],
            ['section_key' => 'number', 'title' => '35,000+', 'content' => 'Musicians served', 'sort_order' => 1],
            ['section_key' => 'number', 'title' => '80+', 'content' => 'Brands stocked', 'sort_order' => 2],
            ['section_key' => 'number', 'title' => '4.8★', 'content' => 'Average rating', 'sort_order' => 3],
            // ── Testimonials ──
            ['section_key' => 'testimonial', 'title' => 'Arjun Mehta', 'content' => '"The Fender setup was perfect out of the box — action, intonation, everything. Best online instrument experience I\'ve had."', 'sort_order' => 0],
            ['section_key' => 'testimonial', 'title' => 'Priya Sharma', 'content' => '"Ordered a digital piano for my daughter\'s lessons. Expert advice before buying, delivered in 3 days. Wonderful."', 'sort_order' => 1],
            ['section_key' => 'testimonial', 'title' => 'Rahul Verma', 'content' => '"Bought my first electric guitar here. The team even called to check if I needed setup help. Outstanding support."', 'sort_order' => 2],
            // ── Stories ──
            ['section_key' => 'story', 'title' => 'First guitar, right way', 'content' => 'How to choose your first acoustic without breaking the bank.', 'sort_order' => 0],
            ['section_key' => 'story', 'title' => 'Studio on a budget', 'content' => 'Five essentials to start recording at home in 2026.', 'sort_order' => 1],
            ['section_key' => 'story', 'title' => 'Practice that sticks', 'content' => 'A simple 20-minute routine that actually builds skill.', 'sort_order' => 2],
            // ── UGC ──
            ['section_key' => 'ugc', 'title' => '#RythmeFamily', 'content' => 'Share your sound with the community — the best setups get featured here.', 'sort_order' => 0],
            // ── Comparison rows ──
            ['section_key' => 'comparison', 'title' => 'Expert setup included', 'subtitle' => 'Most online stores', 'content' => 'Rythme', 'sort_order' => 0],
            ['section_key' => 'comparison', 'title' => '1-year warranty', 'subtitle' => 'Some offer 3 months', 'content' => 'Rythme', 'sort_order' => 1],
            ['section_key' => 'comparison', 'title' => 'Free insured shipping', 'subtitle' => 'Paid shipping common', 'content' => 'Rythme', 'sort_order' => 2],
            ['section_key' => 'comparison', 'title' => 'Real musicians on support', 'subtitle' => 'Ticket-only bots', 'content' => 'Rythme', 'sort_order' => 3],
            // ── Promos (3 big banners — reference style) ──
            ['section_key' => 'promo', 'title' => 'Enjoy studio-grade sound', 'subtitle' => 'Pro audio, simplified', 'content' => '/category/pro-audio', 'sort_order' => 0],
            ['section_key' => 'promo', 'title' => 'Keys for every stage', 'subtitle' => 'Pianos & keyboards', 'content' => '/category/keyboards-pianos', 'sort_order' => 1],
            ['section_key' => 'promo', 'title' => 'Up to 35% off accessories', 'subtitle' => 'Limited-time sale', 'content' => '/shop?sort=discount', 'sort_order' => 2],
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
            ['question' => 'How long does delivery take across India?', 'answer' => 'Metro cities receive orders in 2–4 working days; most other locations in 4–7 working days. Every order ships with tracking, and shipping is free.'],
            ['question' => 'Are instruments set up before shipping?', 'answer' => 'Every guitar, bass, ukulele and keyboard is inspected, tuned and set up by our in-house technicians before dispatch — free of charge. This includes action, intonation and string checks.'],
            ['question' => 'What is your return policy?', 'answer' => '7-day easy returns on unused items in original packaging. If your instrument arrives damaged or develops a fault in the first 7 days, we replace it free — including pickup.'],
            ['question' => 'Can I pay in EMI?', 'answer' => 'Yes — Razorpay supports card EMI, UPI, netbanking and wallets at checkout. EMI options show up automatically for eligible cards.'],
            ['question' => 'Do you offer warranty?', 'answer' => 'Every instrument carries a 1-year warranty against manufacturing defects — parts and labour included. Contact us with your order number to claim.'],
            ['question' => 'Can I talk to someone before buying?', 'answer' => 'Absolutely. Call, WhatsApp or visit the showroom — our team of musicians is happy to help you pick the right instrument.'],
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
