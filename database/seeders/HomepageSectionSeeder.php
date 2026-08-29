<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\HomepageSection;
use Illuminate\Database\Seeder;

/**
 * Default homepage section content — mirrors the blade partial copy so
 * admins can edit kickers/titles/bodies from Filament. Footer + hero
 * slides stay locked (config-driven).
 */
class HomepageSectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            ['categories', 'Explore by category', 'Popular', 'Categories', 'Choose from acoustic and electric guitars, digital pianos, drums, pro audio and accessories from the world\'s most trusted makers.', 1],
            ['bestsellers', 'Played. Loved. Recommended.', 'Recently', 'Launched', 'Our most-loved instruments — handpicked and road-tested by the Rythme team.', 2],
            ['why-rythme', 'The Rythme standard', 'Our', 'Advantages', 'Every instrument is set up, intonated and inspected before it ships — because the right feel matters.', 3],
            ['brands', 'The names behind the music', 'Popular', 'Brands', 'Fender, Yamaha, Roland, Shure and more — the brands that shaped popular music, all under one roof.', 4],
            ['numbers', 'Storefront capabilities', 'Built for clearer', 'shopping.', 'Explore verified catalogue, checkout and order-management capabilities.', 5],
            ['new-arrivals', 'Recently added', 'New Arrival', 'Products', 'Active products ordered by their recorded catalogue date.', 6],
            ['deals', 'Current catalogue savings', 'Available', 'Deals', 'Products with a current compare-at price greater than their selling price.', 7],
            ['video-showcase', 'Rythme Sound', 'Feel the music.', 'Live the moment.', 'Watch what happens when the right instrument meets the right hands.', 8],
            ['stories', 'The Rythme journal', 'Ideas for a life', 'lived in music.', 'Practice tips, gear guides and stories from the Rythme community.', 9],
            ['testimonials', 'Verified customer feedback', 'Approved reviews,', 'from delivered orders.', 'Only moderated reviews tied to paid, delivered purchases may appear publicly.', 10],
            ['comparison', 'Verified capabilities', 'How the', 'storefront works.', 'Server-derived totals, moderated interactions and protected order access.', 11],
            ['ugc', 'Product help', 'Ask it.', 'Get a staff answer.', 'Signed-in customers can submit product questions for moderation.', 12],
            ['faq', 'Good to know', 'Questions,', 'answered.', 'Storefront guidance without invented shipping, warranty, payment or return promises.', 13],
        ];

        foreach ($sections as [$key, $kicker, $title, $accent, $content, $sort]) {
            HomepageSection::updateOrCreate(
                ['section_key' => $key],
                [
                    'kicker' => $kicker,
                    'title' => $title,
                    'title_accent' => $accent,
                    'content' => '<p>'.$content.'</p>',
                    'sort_order' => $sort,
                    'is_active' => true,
                ],
            );
        }
    }
}
