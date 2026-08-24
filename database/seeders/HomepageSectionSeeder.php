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
            ['bestsellers', 'Played. Loved. Recommended.', 'The sound everyone is', 'talking about.', 'Our most-loved instruments — handpicked and road-tested by the Rythme team.', 2],
            ['why-rythme', 'The Rythme standard', 'Why musicians', 'choose Rythme.', 'Every instrument is set up, intonated and inspected before it ships — because the right feel matters.', 3],
            ['brands', 'The names behind the music', 'Legendary makers.', 'One trusted destination.', 'Fender, Yamaha, Roland, Shure and more — the brands that shaped popular music, all under one roof.', 4],
            ['numbers', 'In tune with India', 'A community that keeps', 'growing.', 'Thousands of musicians across India trust Rythme for their first chord and their hundredth gig.', 5],
            ['new-arrivals', 'Fresh from the flight case', 'New Arrival', 'Products', 'The latest instruments and gear, unboxed, tested and ready to play.', 6],
            ['deals', 'The encore sale', 'Deals that', 'sing.', 'Limited-time offers on guitars, keyboards and pro audio — while stock lasts.', 7],
            ['video-showcase', 'Rythme Sound', 'Feel the music.', 'Live the moment.', 'Watch what happens when the right instrument meets the right hands.', 8],
            ['stories', 'The Rythme journal', 'Ideas for a life', 'lived in music.', 'Practice tips, gear guides and stories from the Rythme community.', 9],
            ['testimonials', 'Stories from the Rythme community', 'Loved by players,', 'across India.', 'Real words from real customers — from first-time learners to touring artists.', 10],
            ['comparison', 'The Rythme difference', 'Why buy', 'from Rythme.', 'Expert setup, honest pricing, free shipping and a 1-year warranty on everything we sell.', 11],
            ['ugc', 'Community', 'Play it.', 'Tag it. #RythmeFamily.', 'Share your sound with the community — the best setups get featured here.', 12],
            ['faq', 'Good to know', 'Questions,', 'answered.', 'Everything about shipping, EMI, warranty, returns and setup — before you buy.', 13],
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
