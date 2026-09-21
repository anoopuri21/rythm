<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Page;
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
                    'meta_description' => 'Explore guitars, keyboards, drums, pro audio and musical-instrument accessories from leading brands at Rhythm Exports.',
                    'meta_keywords' => 'music store india, buy guitars online, digital pianos, pro audio, rythme',
                    'og_title' => 'Rhythm Exports - Feel The Music, Own The Sound',
                    'og_description' => 'Explore musical instruments and studio gear from leading brands at Rhythm Exports.',
                    'robots' => 'index, follow',
                ],
            ],
            [
                'slug' => 'about',
                'title' => 'About Rhythm Exports',
                'template' => 'about',
                'content' => '<p>Rhythm Exports is a musical-instrument storefront for browsing catalogue information, managing a customer account and placing orders.</p><p>The application provides server-verified checkout totals, protected order access and moderated verified-purchase reviews.</p>',
                'settings' => [
                    'hero_kicker' => 'Our story',
                    'stats' => [
                        ['value' => 'Curated', 'label' => 'Instrument catalogue'],
                        ['value' => 'Verified', 'label' => 'Checkout totals'],
                        ['value' => 'Moderated', 'label' => 'Verified reviews'],
                        ['value' => 'Protected', 'label' => 'Order tracking'],
                    ],
                    'promise_kicker' => 'Our promise',
                    'promise_heading' => 'A clearer way to explore musical instruments',
                    'promise_text' => 'Rhythm Exports presents catalogue details, availability and checkout totals through the storefront. Verified-purchase reviews are moderated before they appear publicly.',
                    'promise_points' => [
                        'Category, brand, price and specification filters',
                        'Server-verified checkout totals',
                        'Moderated verified-purchase reviews',
                        'Protected order tracking and invoice access',
                    ],
                    'cta_label' => 'Explore the collection',
                    'cta_url' => null,
                    'quote_emoji' => '🎹',
                    'quote_text' => '"The right instrument doesn\'t make you better overnight — it makes every hour of practice worth it." — The Rythme team',
                    'values_heading' => 'What we stand for',
                    'values' => [
                        ['icon' => '🎸', 'title' => 'Honest catalogue', 'text' => 'Specifications, availability and pricing are presented exactly as recorded — no inflated claims.'],
                        ['icon' => '🛡️', 'title' => 'Protected orders', 'text' => 'Order tracking, invoices and returns stay behind protected, verified access.'],
                        ['icon' => '💬', 'title' => 'Real answers', 'text' => 'Verified-purchase reviews are moderated so shoppers see genuine, useful information.'],
                    ],
                ],
                'seo' => [
                    'meta_title' => 'About Us - Rhythm Exports',
                    'meta_description' => 'Learn about the Rhythm Exports musical-instrument catalogue, customer accounts, checkout and protected order tools.',
                    'meta_keywords' => 'about rhythm exports, musical instruments, online catalogue',
                    'og_title' => 'About Rhythm Exports',
                ],
            ],
            [
                'slug' => 'contact',
                'title' => 'Contact Rhythm Exports',
                'template' => 'contact',
                'content' => '<p>Setup advice, order questions, warranty help - our team plays the same instruments we sell. Write to us and you will hear back within 24 hours.</p>',
                'settings' => [
                    // No fake phone/email/address — owner fills cards in Admin → Pages (C5).
                    'contact_kicker' => "We're listening",
                    'cards' => [],
                    'whatsapp_enabled' => false,
                    'whatsapp_number' => '',
                    'whatsapp_title' => 'Prefer WhatsApp?',
                    'whatsapp_text' => 'Send a message about your order or an instrument — the store replies when available.',
                    'whatsapp_button' => 'Chat on WhatsApp',
                    'map_embed_url' => null,
                ],
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
                    'meta_description' => 'Browse guitars, digital pianos, drums, pro audio and musical-instrument accessories from leading brands at Rhythm Exports.',
                    'meta_keywords' => 'buy musical instruments online, guitars india, keyboards, pro audio, rhythm exports',
                    'og_title' => 'Shop Instruments at Rhythm Exports',
                    'robots' => 'index, follow',
                ],
            ],

            [
                'slug' => 'shipping',
                'title' => 'Shipping & Delivery',
                'template' => 'generic',
                'content' => '<h2>Charges</h2><p>Shipping is calculated at checkout. Free shipping applies when your order reaches the amount set in store settings.</p><h2>Delivery</h2><p>Delivery time depends on your PIN code and the carrier we use for that order.</p><h2>Tracking</h2><p>Once an order ships, you can follow it from your account.</p>',
                'seo' => [
                    'meta_title' => 'Shipping & Delivery — Rhythm Exports',
                    'meta_description' => 'How shipping charges and recorded order-status tracking work at Rhythm Exports.',
                    'meta_keywords' => 'shipping, delivery, order tracking, music store delivery',
                ],
            ],
            [
                'slug' => 'returns',
                'title' => 'Returns & Refunds',
                'template' => 'generic',
                'content' => '<h2>Cancel before dispatch</h2><p>You can cancel an unpaid or unshipped order from the order page.</p><h2>Refunds</h2><p>If a paid order is cancelled or approved for return, we process the refund to the original payment method after it is approved.</p><h2>After delivery</h2><p>Contact us with your order number and what went wrong. We will tell you what we can do.</p>',
                'seo' => [
                    'meta_title' => 'Returns & Refunds — Rhythm Exports',
                    'meta_description' => 'How to cancel an order or ask for a refund at Rhythm Exports.',
                    'meta_keywords' => 'returns, refunds, exchange, music store returns',
                ],
            ],
            [
                'slug' => 'refund',
                'title' => 'Refunds',
                'template' => 'generic',
                'content' => '<h2>How refunds work</h2><p>Refunds go back to the same payment method used at checkout, after we approve the request.</p><h2>Timing</h2><p>Banks and UPI apps can take a few working days to show the credit.</p>',
                'seo' => [
                    'meta_title' => 'Refunds — Rhythm Exports',
                    'meta_description' => 'How Rhythm Exports refunds work.',
                    'meta_keywords' => 'refunds, payment refund, rhythm exports',
                ],
            ],
            [
                'slug' => 'warranty',
                'title' => 'Warranty',
                'template' => 'generic',
                'content' => '<h2>Warranty</h2><p>Cover depends on the brand and the papers that come with the product. Ask us if you are unsure.</p>',
                'seo' => [
                    'meta_title' => 'Warranty — Rhythm Exports',
                    'meta_description' => 'How to review product-specific warranty information and request assistance from Rhythm Exports.',
                    'meta_keywords' => 'warranty, instrument warranty, music store warranty',
                ],
            ],
            [
                'slug' => 'faqs',
                'title' => 'Frequently Asked Questions',
                'template' => 'generic',
                'content' => '<h2>How is shipping shown?</h2><p>At checkout, based on store settings.</p><h2>How can I pay?</h2><p>Razorpay shows the methods available for your order.</p><h2>How do I ask about a product?</h2><p>Use the contact form or WhatsApp button.</p><h2>How do I track an order?</h2><p>Open the order in your account.</p>',
                'seo' => [
                    'meta_title' => 'FAQs — Rhythm Exports',
                    'meta_description' => 'Answers about checkout totals, payment options, reviews and protected order tracking.',
                    'meta_keywords' => 'faq, help, shipping, payment, reviews, order tracking',
                ],
            ],
            [
                'slug' => 'terms',
                'title' => 'Terms & Conditions',
                'template' => 'generic',
                'content' => '<h2>Orders</h2><p>When you place an order you agree to pay the total shown at checkout. We re-check stock and prices before payment.</p><h2>Payments</h2><p>Payments are collected by Razorpay. We keep the payment reference, amount and status.</p><h2>Cancellations</h2><p>You can cancel an order until it is packed for shipping. Paid cancellations are refunded after we approve them.</p>',
                'seo' => [
                    'meta_title' => 'Terms & Conditions — Rhythm Exports',
                    'meta_description' => 'Operational information about Rhythm Exports orders, payments and cancellations pending final legal approval.',
                    'meta_keywords' => 'terms, conditions, terms of service',
                ],
            ],
            [
                'slug' => 'privacy',
                'title' => 'Privacy Policy',
                'template' => 'generic',
                'content' => '<h2>What we keep</h2><p>We store your name, email, phone, addresses and order history so we can fulfil and support orders.</p><h2>Payments</h2><p>Card and UPI details are entered on Razorpay. We store payment IDs, amounts and status, not card numbers.</p><h2>Questions</h2><p>Email us if you want a copy of your data or want an account closed.</p>',
                'seo' => [
                    'meta_title' => 'Privacy Policy — Rhythm Exports',
                    'meta_description' => 'Operational information about account, order and payment-reference data pending final privacy approval.',
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
