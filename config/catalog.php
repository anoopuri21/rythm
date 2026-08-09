<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Catalog — Bajaao-inspired category tree
    |--------------------------------------------------------------------------
    | Categories, subcategories and real product imagery (sourced from
    | bajaao.com — project rule: product images ONLY from Bajaao/Amazon).
    | Used by: navbar mega menu + homepage "Explore by Category" section.
    */

    'categories' => [
        [
            'name' => 'Guitars',
            'slug' => 'guitars',
            'tagline' => 'Acoustic · Electric · Bass',
            'count' => '480+ instruments',
            'image' => 'https://www.bajaao.com/cdn/shop/files/FEN-0373152506.jpg?v=1779349747',
            'children' => [
                ['label' => 'Acoustic Guitars', 'slug' => 'acoustic-guitars'],
                ['label' => 'Electric Guitars', 'slug' => 'electric-guitars'],
                ['label' => 'Bass Guitars', 'slug' => 'bass-guitars'],
                ['label' => 'Classical Guitars', 'slug' => 'classical-guitars'],
                ['label' => 'Ukuleles', 'slug' => 'ukuleles'],
            ],
        ],
        [
            'name' => 'Keyboards & Pianos',
            'slug' => 'keyboards-pianos',
            'tagline' => 'Digital · Synth · MIDI',
            'count' => '210+ instruments',
            'image' => 'https://www.bajaao.com/cdn/shop/files/ROL-FP30XBK.jpg?v=1779349747',
            'children' => [
                ['label' => 'Digital Pianos', 'slug' => 'digital-pianos'],
                ['label' => 'Synthesizers', 'slug' => 'synthesizers'],
                ['label' => 'Arranger Keyboards', 'slug' => 'arranger-keyboards'],
                ['label' => 'MIDI Controllers', 'slug' => 'midi-controllers'],
                ['label' => 'Stage Pianos', 'slug' => 'stage-pianos'],
            ],
        ],
        [
            'name' => 'Drums & Percussion',
            'slug' => 'drums-percussion',
            'tagline' => 'Acoustic · Electronic · Cajons',
            'count' => '190+ instruments',
            'image' => 'https://www.bajaao.com/cdn/shop/files/ALE-NITROMAXKIT.jpg?v=1780654577',
            'children' => [
                ['label' => 'Acoustic Drums', 'slug' => 'acoustic-drums'],
                ['label' => 'Electronic Drums', 'slug' => 'electronic-drums'],
                ['label' => 'Cajons', 'slug' => 'cajons'],
                ['label' => 'Cymbals', 'slug' => 'cymbals'],
                ['label' => 'Hand Drums', 'slug' => 'hand-drums'],
            ],
        ],
        [
            'name' => 'Pro Audio',
            'slug' => 'pro-audio',
            'tagline' => 'Mics · Interfaces · Monitors',
            'count' => '350+ essentials',
            'image' => 'https://www.bajaao.com/cdn/shop/files/FCR-SCR2I24.jpg?v=1782732174',
            'children' => [
                ['label' => 'Microphones', 'slug' => 'microphones'],
                ['label' => 'Audio Interfaces', 'slug' => 'audio-interfaces'],
                ['label' => 'Studio Monitors', 'slug' => 'studio-monitors'],
                ['label' => 'Mixers', 'slug' => 'mixers'],
                ['label' => 'Studio Bundles', 'slug' => 'studio-bundles'],
            ],
        ],
        [
            'name' => 'Live Sound',
            'slug' => 'live-sound',
            'tagline' => 'Speakers · Amps · DJ Gear',
            'count' => '160+ systems',
            'image' => 'https://www.bajaao.com/cdn/shop/files/Mackie_revised_Website_Banner_1400_x_486.jpg?v=1776311162',
            'children' => [
                ['label' => 'PA Speakers', 'slug' => 'active-pa-speakers'],
                ['label' => 'Guitar Amps', 'slug' => 'guitar-amplifiers'],
                ['label' => 'DJ Controllers', 'slug' => 'dj-controllers-interfaces'],
                ['label' => 'DJ Mixers', 'slug' => 'dj-mixers'],
                ['label' => 'DJ Headphones', 'slug' => 'dj-headphones'],
            ],
        ],
        [
            'name' => 'Wind Instruments',
            'slug' => 'wind-instruments',
            'tagline' => 'Harmonica · Flute · Brass',
            'count' => '120+ instruments',
            'image' => 'https://www.bajaao.com/cdn/shop/files/vault-harmonicas-red-vault-ha500-key-c-10-hole-harmonica-29054261919923.jpg?v=1744670088',
            'children' => [
                ['label' => 'Harmonicas', 'slug' => 'harmonicas'],
                ['label' => 'Flutes', 'slug' => 'flutes'],
                ['label' => 'Saxophones', 'slug' => 'saxophones'],
                ['label' => 'Trumpets', 'slug' => 'trumpets'],
                ['label' => 'Clarinets', 'slug' => 'clarinets'],
            ],
        ],
        [
            'name' => 'Indian Instruments',
            'slug' => 'indian-instruments',
            'tagline' => 'Tabla · Sitar · Harmonium',
            'count' => '140+ instruments',
            'image' => 'https://www.bajaao.com/cdn/shop/files/ultimate-guru-tablas-ultimate-guru-student-tabla-12538672771.jpg?v=1765878892',
            'children' => [
                ['label' => 'Tabla', 'slug' => 'tablas'],
                ['label' => 'Sitar', 'slug' => 'sitars'],
                ['label' => 'Harmonium', 'slug' => 'harmoniums'],
                ['label' => 'Dholak', 'slug' => 'dholaks'],
                ['label' => 'Other Percussion', 'slug' => 'other-indian-percussion'],
            ],
        ],
        [
            'name' => 'Ukuleles',
            'slug' => 'ukuleles',
            'tagline' => 'Soprano · Concert · Baritone',
            'count' => '80+ ukuleles',
            'image' => 'https://www.bajaao.com/cdn/shop/files/vault-soprano-ukuleles-vault-uk-003-soprano-colourful-ukulele-21-inch-with-gig-bag-29054246819.jpg?v=1744669013',
            'children' => [
                ['label' => 'Soprano Ukuleles', 'slug' => 'soprano-ukuleles'],
                ['label' => 'Concert Ukuleles', 'slug' => 'concert-ukuleles'],
                ['label' => 'Baritone Ukuleles', 'slug' => 'baritone-ukuleles'],
                ['label' => 'Ukulele Bundles', 'slug' => 'ukulele-bundles'],
            ],
        ],
        [
            'name' => 'Recording',
            'slug' => 'recording',
            'tagline' => 'Headphones · Studio Gear',
            'count' => '260+ essentials',
            'image' => 'https://www.bajaao.com/cdn/shop/files/beyerdynamic-studio-headphones-beyerdynamic-dt-770-pro-32-ohm-studio-headphone-black-11587197.jpg?v=1775854027',
            'children' => [
                ['label' => 'Studio Headphones', 'slug' => 'studio-headphones'],
                ['label' => 'Studio Accessories', 'slug' => 'studio-accessories'],
                ['label' => 'DAW Software', 'slug' => 'daw-software'],
                ['label' => 'Sound Treatment', 'slug' => 'sound-proofing-acoustic-treatment'],
            ],
        ],
        [
            'name' => 'Accessories',
            'slug' => 'accessories',
            'tagline' => 'Strings · Picks · Cases',
            'count' => '900+ essentials',
            'image' => 'https://www.bajaao.com/cdn/shop/files/ernie-ball-electric-guitar-strings-ernie-ball-2239-super-slinky-rps9-electric-guita.png?v=1744656943',
            'children' => [
                ['label' => 'Guitar Strings', 'slug' => 'guitar-strings'],
                ['label' => 'Picks & Plectrums', 'slug' => 'picks-plectrums'],
                ['label' => 'Cases & Gig Bags', 'slug' => 'cases-gig-bags'],
                ['label' => 'Stands', 'slug' => 'stands'],
                ['label' => 'Cables & Tuners', 'slug' => 'cables-tuners'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Featured products (Bajaao real products — names + imagery from bajaao.com)
    |--------------------------------------------------------------------------
    */
    'featured' => [
        [
            'name' => 'Squier Sonic Stratocaster Electric Guitar',
            'brand' => 'Fender',
            'price' => 17999,
            'compare_at' => 21999,
            'badge' => 'Best Seller',
            'reviews' => 128,
            'image' => 'https://www.bajaao.com/cdn/shop/files/FEN-0373152506.jpg?v=1779349747',
        ],
        [
            'name' => 'Roland FP-30X 88-Key Digital Piano',
            'brand' => 'Roland',
            'price' => 54999,
            'badge' => 'Hot',
            'reviews' => 94,
            'image' => 'https://www.bajaao.com/cdn/shop/files/ROL-FP30XBK.jpg?v=1779349747',
        ],
        [
            'name' => 'Alesis Nitro Max Electronic Drum Kit',
            'brand' => 'Alesis',
            'price' => 43999,
            'compare_at' => 49999,
            'badge' => 'Deal',
            'reviews' => 71,
            'image' => 'https://www.bajaao.com/cdn/shop/files/ALE-NITROMAXKIT.jpg?v=1780654577',
        ],
        [
            'name' => 'Focusrite Scarlett 2i2 4th Gen Audio Interface',
            'brand' => 'Focusrite',
            'price' => 24999,
            'badge' => 'New',
            'reviews' => 45,
            'image' => 'https://www.bajaao.com/cdn/shop/files/FCR-SCR2I24.jpg?v=1782732174',
        ],
        [
            'name' => 'Shure SM58S Dynamic Vocal Microphone',
            'brand' => 'Shure',
            'price' => 10999,
            'compare_at' => 12999,
            'reviews' => 112,
            'image' => 'https://www.bajaao.com/cdn/shop/files/shure-dynamic-microphones-shure-sm58s-mic.jpg?v=1779307521',
        ],
        [
            'name' => 'Beyerdynamic DT-770 PRO Studio Headphones',
            'brand' => 'Beyerdynamic',
            'price' => 18999,
            'badge' => 'Popular',
            'reviews' => 87,
            'image' => 'https://www.bajaao.com/cdn/shop/files/beyerdynamic-studio-headphones-beyerdynamic-dt-770-pro-32-ohm-studio-headphone-black-11587197.jpg?v=1775854027',
        ],
        [
            'name' => 'Ibanez GRG170DX RG Gio Electric Guitar',
            'brand' => 'Ibanez',
            'price' => 19999,
            'compare_at' => 23999,
            'reviews' => 63,
            'image' => 'https://www.bajaao.com/cdn/shop/files/IBANEZGRG170DX.jpg?v=1773474252',
        ],
        [
            'name' => 'Casio Privia PX-S1100 Digital Piano',
            'brand' => 'Casio',
            'price' => 51999,
            'badge' => 'New',
            'reviews' => 39,
            'image' => 'https://www.bajaao.com/cdn/shop/files/casio-digital-pianos-black-only-piano-casio-privia-px-s1100-88-key-digital-piano.jpg?v=1779274201',
        ],
    ],
];
