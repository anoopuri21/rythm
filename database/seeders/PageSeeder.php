<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Page;
use App\Models\SeoEntry;
use Illuminate\Database\Seeder;

/**
 * Default dynamic pages. Slug = null row is the homepage SEO entry.
 * 'about' and 'contact' render through their templates with DB content.
 */
class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug' => null,
                'title' => 'Home',
                'template' => 'generic',
                'content' => null,
                'seo' => [
                    'meta_title' => 'Rhythm Exports - Feel The Music, Own The Sound',
                    'meta_description' => 'Shop premium musical instruments at Rhythm Exports. Guitars, Keyboards, Drums, Pro Audio and more from top brands like Fender, Yamaha, Roland. Free shipping all over India.',
                    'meta_keywords' => 'music store india, buy guitars online, digital pianos, pro audio, rythme',
                    'og_title' => 'Rhythm Exports - Feel The Music, Own The Sound',
                    'og_description' => 'Premium musical instruments, expert setup, free shipping across India.',
                    'robots' => 'index, follow',
                ],
            ],
            [
                'slug' => 'about',
                'title' => 'About Rhythm Exports',
                'template' => 'about',
                'content' => '<p>Rythme started with a simple belief - every musician in India deserves the right instrument, honestly priced and properly set up. What began as a small export workshop is now a trusted music store for players from first chords to first festivals.</p><p>Every guitar is strung, intonated and inspected before it ships. Every keyboard is tested voice by voice. We do not sell boxes - we send playable, gig-ready instruments.</p>',
                'seo' => [
                    'meta_title' => 'About Us - Rhythm Exports',
                    'meta_description' => 'Rhythm Exports helps musicians across India find the right instruments - handpicked, expertly set up and delivered with care.',
                    'meta_keywords' => 'about rythme, music store india, instrument setup',
                    'og_title' => 'About Rhythm Exports',
                ],
            ],
            [
                'slug' => 'contact',
                'title' => 'Contact Rhythm Exports',
                'template' => 'contact',
                'content' => '<p>Setup advice, order questions, warranty help - our team plays the same instruments we sell. Write to us and you will hear back within 24 hours.</p>',
                'seo' => [
                    'meta_title' => 'Contact Us - Rhythm Exports',
                    'meta_description' => 'Questions about an instrument, an order or a setup? Contact the Rhythm Exports team - we reply within 24 hours.',
                    'meta_keywords' => 'contact rythme, music store support, order help',
                    'og_title' => 'Contact Rhythm Exports',
                ],
            ],

            [
                'slug' => 'shop',
                'title' => 'Shop All Instruments',
                'template' => 'generic',
                'content' => null,
                'seo' => [
                    'meta_title' => 'Shop All Instruments - Guitars, Keyboards, Drums, Pro Audio | Rhythm Exports',
                    'meta_description' => 'Browse the full Rhythm Exports catalogue - acoustic and electric guitars, digital pianos, electronic drums, pro audio and accessories. Free shipping all over India.',
                    'meta_keywords' => 'buy musical instruments online, guitars india, keyboards, pro audio, rhythm exports',
                    'og_title' => 'Shop Instruments at Rhythm Exports',
                    'robots' => 'index, follow',
                ],
            ],

            [
                'slug' => 'shipping',
                'title' => 'Shipping & Delivery',
                'template' => 'generic',
                'content' => '<h2>Free shipping, everywhere in India</h2><p>Every order ships free with reliable, fully-insured couriers. Instruments are double-boxed with proper padding, and guitars travel in hard cases wherever possible.</p><h2>Delivery timelines</h2><p>Metro cities: 2–4 business days. Rest of India: 4–7 business days. Orders placed before 2 PM IST dispatch the same day.</p><h2>Order tracking</h2><p>You will receive a tracking link by email and SMS the moment your order ships.</p>',
                'seo' => [
                    'meta_title' => 'Shipping & Delivery — Rhythm Exports',
                    'meta_description' => 'Free shipping all over India on every instrument. Fast, insured delivery with tracking — find delivery timelines for metros and beyond.',
                    'meta_keywords' => 'shipping, delivery, free shipping india, music store delivery',
                ],
            ],
            [
                'slug' => 'returns',
                'title' => 'Returns & Refunds',
                'template' => 'generic',
                'content' => '<h2>7-day easy returns</h2><p>Changed your mind? You have 7 days from delivery to request a return. Instruments must be unused and in original packaging.</p><h2>Refunds</h2><p>Refunds are processed within 5–7 business days of the item reaching our warehouse, to the original payment method.</p><h2>Defective items</h2><p>If your instrument arrives damaged or develops a fault in the first 7 days, we replace it free of cost — including pickup.</p>',
                'seo' => [
                    'meta_title' => 'Returns & Refunds — Rhythm Exports',
                    'meta_description' => 'Easy 7-day returns and quick refunds at Rhythm Exports. Free replacement for damaged or faulty instruments.',
                    'meta_keywords' => 'returns, refunds, exchange, music store returns',
                ],
            ],
            [
                'slug' => 'warranty',
                'title' => 'Warranty',
                'template' => 'generic',
                'content' => '<h2>1-year warranty on every instrument</h2><p>All instruments sold by Rhythm Exports carry a 1-year warranty against manufacturing defects — parts and labour included.</p><h2>What is covered</h2><p>Fret issues, electronics failure, hardware defects and structural problems. Normal wear (strings, finish wear) is not covered.</p><h2>How to claim</h2><p>Contact us with your order number and a short video of the issue. We arrange pickup, repair or replacement — usually within a week.</p>',
                'seo' => [
                    'meta_title' => 'Warranty — Rhythm Exports',
                    'meta_description' => 'Every instrument at Rhythm Exports comes with a 1-year warranty against manufacturing defects. See what is covered and how to claim.',
                    'meta_keywords' => 'warranty, instrument warranty, music store warranty',
                ],
            ],
            [
                'slug' => 'faqs',
                'title' => 'Frequently Asked Questions',
                'template' => 'generic',
                'content' => '<h2>Do you provide free shipping?</h2><p>Yes — every order ships free across India, with full insurance.</p><h2>Are guitars set up before dispatch?</h2><p>Every guitar is strung, intonated and action-checked before it leaves our workshop.</p><h2>Can I pay with EMI?</h2><p>Yes — Razorpay supports card EMI, UPI, netbanking and wallets at checkout.</p><h2>What is your return policy?</h2><p>7-day easy returns on unused items; free replacement for faults.</p>',
                'seo' => [
                    'meta_title' => 'FAQs — Rhythm Exports',
                    'meta_description' => 'Answers about shipping, setup, EMI and returns at Rhythm Exports. Everything you need to know before you buy.',
                    'meta_keywords' => 'faq, help, shipping, emi, returns',
                ],
            ],
            [
                'slug' => 'terms',
                'title' => 'Terms & Conditions',
                'template' => 'generic',
                'content' => '<h2>1. Orders</h2><p>All orders are subject to availability. Prices are in Indian Rupees and include applicable taxes.</p><h2>2. Payments</h2><p>Payments are processed securely via Razorpay. We never store card details.</p><h2>3. Liability</h2><p>Rhythm Exports is not liable for indirect damages. Our maximum liability is limited to the order value.</p><h2>4. Governing law</h2><p>These terms are governed by the laws of India; disputes fall under Delhi jurisdiction.</p>',
                'seo' => [
                    'meta_title' => 'Terms & Conditions — Rhythm Exports',
                    'meta_description' => 'The terms and conditions for shopping at Rhythm Exports — orders, payments, liability and governing law.',
                    'meta_keywords' => 'terms, conditions, terms of service',
                ],
            ],
            [
                'slug' => 'privacy',
                'title' => 'Privacy Policy',
                'template' => 'generic',
                'content' => '<h2>What we collect</h2><p>We collect your name, email, phone and delivery address to process orders, plus optional account details.</p><h2>How we use it</h2><p>Order fulfilment, order updates, and — only with consent — occasional offers. We never sell your data.</p><h2>Payments</h2><p>Card and UPI details are handled entirely by Razorpay; we never see or store them.</p><h2>Your rights</h2><p>Request a copy or deletion of your data anytime by writing to support@rythme.store.</p>',
                'seo' => [
                    'meta_title' => 'Privacy Policy — Rhythm Exports',
                    'meta_description' => 'How Rhythm Exports collects, uses and protects your personal data. Your privacy matters to us.',
                    'meta_keywords' => 'privacy, privacy policy, data protection',
                ],
            ],
        ];

        foreach ($pages as $data) {
            $seo = $data['seo'];
            unset($data['seo']);

            $page = Page::updateOrCreate(
                ['slug' => $data['slug']],
                $data,
            );

            if ($seo !== []) {
                $page->seoEntry()->updateOrCreate([], $seo);
            }
        }
    }
}
