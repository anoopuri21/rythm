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
