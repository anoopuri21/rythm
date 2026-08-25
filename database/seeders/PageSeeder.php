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
                'content' => '<p>Rhythm Exports is a musical-instrument storefront for browsing catalogue information, managing a customer account and placing orders.</p><p>The application provides server-verified checkout totals, protected order access, verified-purchase reviews and moderated product questions.</p>',
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
                'content' => '<h2>Shipping charges</h2><p>Any configured shipping charge is calculated during checkout and recorded on the order before payment.</p><h2>Delivery information</h2><p>Serviceability, carrier and delivery estimates depend on the destination and fulfillment arrangement confirmed for the order.</p><h2>Order tracking</h2><p>Customers can follow recorded order-status updates from their account or through protected guest tracking.</p>',
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
                'content' => '<h2>Cancellation requests</h2><p>Eligible orders can be cancelled from the protected order page before fulfillment reaches a non-cancellable status.</p><h2>Paid cancellations</h2><p>When a captured-payment order is cancelled, the application records a pending refund request for staff processing. This does not claim that the payment provider has completed a refund.</p><h2>Post-delivery requests</h2><p>Contact the store with the order number and issue details. Eligibility and the available resolution depend on the approved business policy and recorded order condition.</p>',
                'seo' => [
                    'meta_title' => 'Returns & Refunds — Rhythm Exports',
                    'meta_description' => 'Cancellation, post-delivery request and pending-refund information for Rhythm Exports orders.',
                    'meta_keywords' => 'returns, refunds, exchange, music store returns',
                ],
            ],
            [
                'slug' => 'warranty',
                'title' => 'Warranty',
                'template' => 'generic',
                'content' => '<h2>Warranty information</h2><p>No universal warranty term is asserted by this storefront. Coverage may vary by product, manufacturer and the documentation supplied with the order.</p><h2>Requesting assistance</h2><p>Contact the store with the order number, product details and a description of the issue so the recorded coverage and available next steps can be reviewed.</p>',
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
                'content' => '<h2>How are shipping charges shown?</h2><p>Any configured shipping charge is calculated and displayed during checkout.</p><h2>Which payment methods are available?</h2><p>The configured payment provider displays the methods available for the specific checkout attempt.</p><h2>How can I ask about a product?</h2><p>Signed-in customers can submit a product question. Approved staff answers are published after moderation.</p><h2>How can I track an order?</h2><p>Use the protected account order page or the signed guest tracking journey.</p>',
                'seo' => [
                    'meta_title' => 'FAQs — Rhythm Exports',
                    'meta_description' => 'Answers about checkout totals, payment options, product questions and protected order tracking.',
                    'meta_keywords' => 'faq, help, shipping, payment, product questions, order tracking',
                ],
            ],
            [
                'slug' => 'terms',
                'title' => 'Terms & Conditions',
                'template' => 'generic',
                'content' => '<h2>Orders</h2><p>Product availability and checkout totals are revalidated on the server before an order is created.</p><h2>Payments</h2><p>The configured payment provider presents payment methods. The application records gateway identifiers and payment status for reconciliation.</p><h2>Cancellations</h2><p>Cancellation availability depends on the recorded order status. A paid cancellation creates a pending refund request for staff processing.</p><h2>Legal review</h2><p>Final commercial terms, liability language and governing-law provisions require owner and legal approval before production launch.</p>',
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
                'content' => '<h2>Account and order data</h2><p>The application records account identifiers, saved addresses, order address snapshots and order contact details needed for storefront and order workflows.</p><h2>Payments</h2><p>The configured payment provider presents payment input. The application stores gateway identifiers, amounts, currency and payment status for reconciliation.</p><h2>Customer requests</h2><p>Contact the store about account or order data. The final request-handling and retention procedure requires owner and legal approval before production launch.</p>',
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
