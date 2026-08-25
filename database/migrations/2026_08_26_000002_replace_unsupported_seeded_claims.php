<?php

declare(strict_types=1);

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $pages = [
            'about' => '<p>Rhythm Exports is a musical-instrument storefront for browsing catalogue information, managing a customer account and placing orders.</p><p>The application provides server-verified checkout totals, protected order access, verified-purchase reviews and moderated product questions.</p>',
            'shipping' => '<h2>Shipping charges</h2><p>Any configured shipping charge is calculated during checkout and recorded on the order before payment.</p><h2>Delivery information</h2><p>Serviceability, carrier and delivery estimates depend on the destination and fulfillment arrangement confirmed for the order.</p><h2>Order tracking</h2><p>Customers can follow recorded order-status updates from their account or through protected guest tracking.</p>',
            'returns' => '<h2>Cancellation requests</h2><p>Eligible orders can be cancelled from the protected order page before fulfillment reaches a non-cancellable status.</p><h2>Paid cancellations</h2><p>When a captured-payment order is cancelled, the application records a pending refund request for staff processing. This does not claim that the payment provider has completed a refund.</p><h2>Post-delivery requests</h2><p>Contact the store with the order number and issue details. Eligibility and the available resolution depend on the approved business policy and recorded order condition.</p>',
            'warranty' => '<h2>Warranty information</h2><p>No universal warranty term is asserted by this storefront. Coverage may vary by product, manufacturer and the documentation supplied with the order.</p><h2>Requesting assistance</h2><p>Contact the store with the order number, product details and a description of the issue so the recorded coverage and available next steps can be reviewed.</p>',
            'faqs' => '<h2>How are shipping charges shown?</h2><p>Any configured shipping charge is calculated and displayed during checkout.</p><h2>Which payment methods are available?</h2><p>The configured payment provider displays the methods available for the specific checkout attempt.</p><h2>How can I ask about a product?</h2><p>Signed-in customers can submit a product question. Approved staff answers are published after moderation.</p><h2>How can I track an order?</h2><p>Use the protected account order page or the signed guest tracking journey.</p>',
            'terms' => '<h2>Orders</h2><p>Product availability and checkout totals are revalidated on the server before an order is created.</p><h2>Payments</h2><p>The configured payment provider presents payment methods. The application records gateway identifiers and payment status for reconciliation.</p><h2>Cancellations</h2><p>Cancellation availability depends on the recorded order status. A paid cancellation creates a pending refund request for staff processing.</p><h2>Legal review</h2><p>Final commercial terms, liability language and governing-law provisions require owner and legal approval before production launch.</p>',
            'privacy' => '<h2>Account and order data</h2><p>The application records account identifiers, saved addresses, order address snapshots and order contact details needed for storefront and order workflows.</p><h2>Payments</h2><p>The configured payment provider presents payment input. The application stores gateway identifiers, amounts, currency and payment status for reconciliation.</p><h2>Customer requests</h2><p>Contact the store about account or order data. The final request-handling and retention procedure requires owner and legal approval before production launch.</p>',
        ];

        foreach ($pages as $slug => $content) {
            DB::table('pages')
                ->where('slug', $slug)
                ->whereColumn('created_at', 'updated_at')
                ->update(['content' => $content, 'updated_at' => now()]);
        }

        $pageSeo = [
            'about' => ['Learn about the Rhythm Exports musical-instrument catalogue, customer accounts, checkout and protected order tools.', 'about rhythm exports, musical instruments, online catalogue'],
            'shipping' => ['How shipping charges and recorded order-status tracking work at Rhythm Exports.', 'shipping, delivery, order tracking, music store delivery'],
            'returns' => ['Cancellation, post-delivery request and pending-refund information for Rhythm Exports orders.', 'returns, refunds, cancellation, order support'],
            'warranty' => ['How to review product-specific warranty information and request assistance from Rhythm Exports.', 'warranty, product support, order assistance'],
            'faqs' => ['Answers about checkout totals, payment options, product questions and protected order tracking.', 'faq, help, shipping, payment, product questions, order tracking'],
            'terms' => ['Operational information about Rhythm Exports orders, payments and cancellations pending final legal approval.', 'terms, conditions, orders, payments'],
            'privacy' => ['Operational information about account, order and payment-reference data pending final privacy approval.', 'privacy, account data, order data, payment references'],
        ];

        foreach ($pageSeo as $slug => [$description, $keywords]) {
            $pageId = DB::table('pages')->where('slug', $slug)->value('id');
            if ($pageId !== null) {
                DB::table('seo_entries')
                    ->where('seoable_type', Page::class)
                    ->where('seoable_id', $pageId)
                    ->whereColumn('created_at', 'updated_at')
                    ->update([
                        'meta_description' => $description,
                        'meta_keywords' => $keywords,
                        'updated_at' => now(),
                    ]);
            }
        }

        $blocks = [
            'usp' => [
                ['Catalogue filters', null, 'Browse by category, brand, price, stock and available specifications.'],
                ['Server-verified totals', null, 'Checkout recalculates current prices, discounts, shipping and tax.'],
                ['Protected checkout', null, 'Payment state and inventory transitions are verified on the server.'],
                ['Order tracking', null, 'Customers can follow recorded order-status updates through protected access.'],
                ['Verified reviews', null, 'Only paid, delivered purchases can submit moderated reviews.'],
                ['Product Q&A', null, 'Customer questions and staff answers are moderated before publication.'],
            ],
            'number' => [
                ['Curated', null, 'Instrument catalogue'],
                ['Verified', null, 'Checkout totals'],
                ['Moderated', null, 'Reviews and Q&A'],
                ['Protected', null, 'Order tracking'],
            ],
            'comparison' => [
                ['Current catalogue pricing', 'Server-derived', 'Rythme'],
                ['Verified-purchase reviews', 'Moderated', 'Rythme'],
                ['Product questions', 'Staff answers', 'Rythme'],
                ['Protected order tracking', 'Signed or account access', 'Rythme'],
            ],
        ];

        foreach ($blocks as $section => $rows) {
            foreach ($rows as $sortOrder => [$title, $subtitle, $content]) {
                DB::table('homepage_blocks')
                    ->where('section_key', $section)
                    ->where('sort_order', $sortOrder)
                    ->whereColumn('created_at', 'updated_at')
                    ->update([
                        'title' => $title,
                        'subtitle' => $subtitle,
                        'content' => $content,
                        'updated_at' => now(),
                    ]);
            }
        }

        DB::table('homepage_blocks')
            ->where('section_key', 'testimonial')
            ->whereColumn('created_at', 'updated_at')
            ->delete();

        $faqs = [
            ['How are shipping charges calculated?', 'Any configured shipping charge is calculated from server settings and shown during checkout before payment.'],
            ['How can I ask about an instrument?', 'Signed-in customers can submit a product question. Staff answers appear only after moderation and approval.'],
            ['Who can submit a product review?', 'A customer with a paid, delivered order containing the product can submit one review for moderation.'],
            ['Which payment methods can I use?', 'The configured payment provider shows the methods available for the specific checkout attempt.'],
            ['How do I check warranty information?', 'Review product or manufacturer documentation and contact the store with the order number for product-specific assistance.'],
            ['How can I track an order?', 'Use the protected order page in your account or the signed guest tracking journey with the order number and matching email.'],
        ];

        foreach ($faqs as $sortOrder => [$question, $answer]) {
            DB::table('faqs')
                ->where('sort_order', $sortOrder)
                ->whereColumn('created_at', 'updated_at')
                ->update(['question' => $question, 'answer' => $answer, 'updated_at' => now()]);
        }

        $sectionContent = [
            'numbers' => 'Explore verified catalogue, checkout and order-management capabilities.',
            'new-arrivals' => 'Active products ordered by their recorded catalogue date.',
            'deals' => 'Products with a current compare-at price greater than their selling price.',
            'testimonials' => 'Only moderated reviews tied to paid, delivered purchases may appear publicly.',
            'comparison' => 'Server-derived totals, moderated interactions and protected order access.',
            'ugc' => 'Signed-in customers can submit product questions for moderation.',
            'faq' => 'Storefront guidance without invented shipping, warranty, payment or return promises.',
        ];

        foreach ($sectionContent as $sectionKey => $content) {
            DB::table('homepage_sections')
                ->where('section_key', $sectionKey)
                ->whereColumn('created_at', 'updated_at')
                ->update(['content' => $content, 'updated_at' => now()]);
        }

        DB::table('hero_slides')
            ->where('sort_order', 0)
            ->whereColumn('created_at', 'updated_at')
            ->update([
                'copy' => 'Browse active instruments, current catalogue pricing and available product specifications.',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Unsupported claims and synthetic testimonials are intentionally not restored.
    }
};
