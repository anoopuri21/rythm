<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Bajaao-inspired category tree (2 levels) — names are generic
 * instrument categories (not copyrighted); copy uniquely written.
 */
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            'Guitars' => [
                'desc' => 'Acoustic, electric and bass guitars from the world’s most trusted builders, set up and ready to play.',
                'children' => [
                    'Acoustic Guitars' => 'Dreadnoughts, auditoriums and travel guitars with rich, balanced tone.',
                    'Electric Guitars' => 'Solid-body classics and modern axes for every genre.',
                    'Bass Guitars' => 'Four and five-string basses built for deep, punchy low end.',
                    'Classical Guitars' => 'Nylon-string instruments with warm, mellow character.',
                    'Ukuleles' => 'Playful sopranos and tenors — the friendliest instrument to learn.',
                    'Guitar Amps' => 'Practice combos to stage-ready heads with effects built in.',
                ],
            ],
            'Keyboards & Pianos' => [
                'desc' => 'Digital pianos, synths and portable keyboards with authentic sounds and premium keybeds.',
                'children' => [
                    'Digital Pianos' => 'Weighted-key pianos that feel and sound like the real thing.',
                    'Portable Keyboards' => 'All-in-one keyboards with hundreds of voices and rhythms.',
                    'Synthesizers' => 'Compact analogue and digital synths for sound design.',
                    'MIDI Controllers' => 'Keys and pads to control your DAW and virtual instruments.',
                ],
            ],
            'Drums & Percussion' => [
                'desc' => 'Acoustic kits, electronic drums and cymbals for every drummer and budget.',
                'children' => [
                    'Electronic Drum Kits' => 'Mesh-head kits with silent practice and studio-grade sounds.',
                    'Acoustic Drum Kits' => 'Shell packs and complete kits for live performance.',
                    'Cymbals' => 'Crash, ride and hi-hat packs that cut through the mix.',
                    'Hand Percussion' => 'Congas, djembe and shakers for organic grooves.',
                ],
            ],
            'Pro Audio' => [
                'desc' => 'Interfaces, monitors, mics and headphones trusted by studios across India.',
                'children' => [
                    'Studio Monitors' => 'Reference speakers for honest, detailed mixes.',
                    'Microphones' => 'Vocal and instrument mics for stage and studio.',
                    'Audio Interfaces' => 'USB and Thunderbolt interfaces with pristine preamps.',
                    'Headphones' => 'Closed-back studio cans and open-back references.',
                    'Mixers' => 'Compact and full-size mixers for bands and home studios.',
                ],
            ],
            'DJ & Stage' => [
                'desc' => 'Controllers, turntables and PA gear to take your set from bedroom to club.',
                'children' => [
                    'DJ Controllers' => 'Two and four-channel controllers with pro software.',
                    'Turntables' => 'Direct-drive decks for vinyl and digital DVS setups.',
                    'Stage & PA' => 'Speakers, mixers and stands for live sound.',
                ],
            ],
            'Accessories' => [
                'desc' => 'Everything your instrument needs — strings, cables, stands and more.',
                'children' => [
                    'Guitar Strings' => 'Coated and uncoated strings for acoustic and electric.',
                    'Cables & Connectors' => 'Instrument and XLR cables with lifetime-grade connectors.',
                    'Picks & Capos' => 'Everyday essentials that always find their way into your case.',
                    'Stands & Bags' => 'Guitar stands, gig bags and cases for safe travel.',
                ],
            ],
        ];

        foreach ($tree as $parentName => $data) {
            $parent = Category::firstOrCreate(
                ['slug' => Str::slug($parentName)],
                [
                    'name' => $parentName,
                    'description' => $data['desc'],
                    'sort_order' => 0,
                    'is_active' => true,
                    'seo_title' => "{$parentName} — Buy Online in India | Rythme Music Store",
                    'seo_description' => $data['desc'],
                ],
            );

            foreach ($data['children'] as $childName => $childDesc) {
                Category::firstOrCreate(
                    ['slug' => Str::slug($childName)],
                    [
                        'parent_id' => $parent->id,
                        'name' => $childName,
                        'description' => $childDesc,
                        'sort_order' => 0,
                        'is_active' => true,
                        'seo_title' => "{$childName} — Buy Online in India | Rythme Music Store",
                        'seo_description' => $childDesc,
                    ],
                );
            }
        }
    }
}
