<?php

declare(strict_types=1);

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
            'image' => 'https://www.bajaao.com/cdn/shop/files/ultimate-guru-other-indian-percussion-taal-sangat-digital-tabla-12538672771.jpg?v=1688490765',
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
            'image' => 'https://www.bajaao.com/cdn/shop/files/kala-soprano-ukuleles-kala-makala-mk-s-soprano-ukulele-18300244328609.jpg?v=1686443810',
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
            'image' => 'https://www.bajaao.com/cdn/shop/files/vault-studio-headphones-black-vault-sonic-m50-studio-monitoring-headphones-1158719799.jpg?v=1769755244',
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
            'image' => 'https://www.bajaao.com/cdn/shop/files/ernie-ball-electric-guitar-strings-ernie-ball-2239-super-slinky-rps9-electric-guitar-strings-34264274206899.png?v=1707561395',
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

    'nav' => [
        [
            'name' => 'Guitars',
            'slug' => 'guitars',
            'children' => [
                [
                    'label' => 'Acoustic Guitars',
                    'slug' => 'acoustic-guitars'
                ],
                [
                    'label' => 'Electric Guitars',
                    'slug' => 'electric-guitars'
                ],
                [
                    'label' => 'Bass Guitars',
                    'slug' => 'bass-guitars'
                ],
                [
                    'label' => 'Classical Guitars',
                    'slug' => 'classical-guitars'
                ],
                [
                    'label' => 'Guitar Amps',
                    'slug' => 'guitar-amplifiers'
                ],
                [
                    'label' => 'Effects & Pedals',
                    'slug' => 'guitar-effects'
                ]
            ]
        ],
        [
            'name' => 'Ukuleles & Violins',
            'slug' => 'ukuleles-violins',
            'children' => [
                [
                    'label' => 'Soprano Ukuleles',
                    'slug' => 'soprano-ukuleles'
                ],
                [
                    'label' => 'Concert Ukuleles',
                    'slug' => 'concert-ukuleles'
                ],
                [
                    'label' => 'Baritone Ukuleles',
                    'slug' => 'baritone-ukuleles'
                ],
                [
                    'label' => 'Violins',
                    'slug' => 'violins'
                ],
                [
                    'label' => 'Violas',
                    'slug' => 'violas'
                ],
                [
                    'label' => 'Cellos',
                    'slug' => 'cellos'
                ]
            ]
        ],
        [
            'name' => 'Keyboards & Pianos',
            'slug' => 'keyboards-pianos',
            'children' => [
                [
                    'label' => 'Digital Pianos',
                    'slug' => 'digital-pianos'
                ],
                [
                    'label' => 'Synthesizers',
                    'slug' => 'synthesizers'
                ],
                [
                    'label' => 'Arranger Keyboards',
                    'slug' => 'arranger-keyboards'
                ],
                [
                    'label' => 'MIDI Controllers',
                    'slug' => 'midi-controllers'
                ],
                [
                    'label' => 'Stage Pianos',
                    'slug' => 'stage-pianos'
                ]
            ]
        ],
        [
            'name' => 'Studio & Recording',
            'slug' => 'studio-recording',
            'children' => [
                [
                    'label' => 'Audio Interfaces',
                    'slug' => 'audio-interfaces'
                ],
                [
                    'label' => 'Studio Monitors',
                    'slug' => 'studio-monitors'
                ],
                [
                    'label' => 'Studio Headphones',
                    'slug' => 'studio-headphones'
                ],
                [
                    'label' => 'Microphones',
                    'slug' => 'microphones'
                ],
                [
                    'label' => 'Studio Bundles',
                    'slug' => 'studio-bundles'
                ],
                [
                    'label' => 'Sound Treatment',
                    'slug' => 'sound-proofing-acoustic-treatment'
                ]
            ]
        ],
        [
            'name' => 'Drums & Percussion',
            'slug' => 'drums-percussion',
            'children' => [
                [
                    'label' => 'Acoustic Drums',
                    'slug' => 'acoustic-drums'
                ],
                [
                    'label' => 'Electronic Drums',
                    'slug' => 'electronic-drums'
                ],
                [
                    'label' => 'Cajons',
                    'slug' => 'cajons'
                ],
                [
                    'label' => 'Cymbals',
                    'slug' => 'cymbals'
                ],
                [
                    'label' => 'Hand Drums',
                    'slug' => 'hand-drums'
                ],
                [
                    'label' => 'Drum Hardware',
                    'slug' => 'drum-hardware'
                ]
            ]
        ],
        [
            'name' => 'Software & Plugins',
            'slug' => 'software-plugins',
            'children' => [
                [
                    'label' => 'DAW Software',
                    'slug' => 'daw-software'
                ],
                [
                    'label' => 'Virtual Instruments',
                    'slug' => 'virtual-instruments'
                ],
                [
                    'label' => 'Plugins & Effects',
                    'slug' => 'plugins-effects'
                ],
                [
                    'label' => 'Sample Packs',
                    'slug' => 'sample-packs'
                ]
            ]
        ],
        [
            'name' => 'Other',
            'slug' => 'other',
            'children' => [
                [
                    'label' => 'Wind Instruments',
                    'slug' => 'wind-instruments'
                ],
                [
                    'label' => 'Indian Instruments',
                    'slug' => 'indian-instruments'
                ],
                [
                    'label' => 'DJ Gear',
                    'slug' => 'dj-gear'
                ],
                [
                    'label' => 'Live Sound',
                    'slug' => 'live-sound'
                ],
                [
                    'label' => 'Accessories',
                    'slug' => 'accessories'
                ],
                [
                    'label' => 'Music Books',
                    'slug' => 'music-books'
                ]
            ]
        ],
        [
            'name' => 'Deals',
            'slug' => 'deals',
            'hot' => true,
            'children' => [
                [
                    'label' => 'Today\'s Deals',
                    'slug' => 'deals'
                ],
                [
                    'label' => 'Clearance',
                    'slug' => 'clearance'
                ],
                [
                    'label' => 'Open Box',
                    'slug' => 'open-box'
                ],
                [
                    'label' => 'Festival Offers',
                    'slug' => 'festival-offers'
                ]
            ]
        ],
        [
            'name' => 'More',
            'slug' => 'more',
            'children' => [
                [
                    'label' => 'Brands',
                    'slug' => 'brands'
                ],
                [
                    'label' => 'About Us',
                    'slug' => 'about'
                ],
                [
                    'label' => 'Contact',
                    'slug' => 'contact'
                ],
                [
                    'label' => 'Journal',
                    'slug' => 'stories'
                ],
                [
                    'label' => 'FAQs',
                    'slug' => 'faqs'
                ],
                [
                    'label' => 'Track Order',
                    'slug' => 'orders/track'
                ]
            ]
        ]
    ],

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
            'image' => 'https://www.bajaao.com/cdn/shop/files/shure-dynamic-microphones-shure-sm58s-mic-with-switch-31252663599283.jpg?v=1743170195',
        ],
        [
            'name' => 'Beyerdynamic DT-770 PRO Studio Headphones',
            'brand' => 'Beyerdynamic',
            'price' => 18999,
            'badge' => 'Popular',
            'reviews' => 87,
            'image' => 'https://www.bajaao.com/cdn/shop/files/beyerdynamic-studio-headphones-beyerdynamic-dt-770-pro-32-ohm-studio-headphone-black-30363420426419.jpg?v=1687030415',
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
            'image' => 'https://www.bajaao.com/cdn/shop/files/casio-digital-pianos-black-only-piano-casio-privia-series-px-s1100-88-key-digital-piano-1183115381.jpg?v=1768374749',
        ],
    ],

    'carousel' => [
        [
            'name' => 'Squier Sonic Stratocaster Electric Guitar',
            'brand' => 'Fender',
            'price' => 17999,
            'reviews' => 128,
            'image' => 'https://www.bajaao.com/cdn/shop/files/FEN-0373152506.jpg?v=1779349747'
        ],
        [
            'name' => 'Yamaha F310 Dreadnought Acoustic Guitar',
            'brand' => 'Yamaha',
            'price' => 7050,
            'reviews' => 42,
            'image' => 'https://www.bajaao.com/cdn/shop/files/yamaha-acoustic-guitars-yamaha-f310-dreadnought-acoustic-guitar-open-box-1151639175.jpg?v=1768191291&width=1920'
        ],
        [
            'name' => 'Kala Makala MK-S Soprano Ukulele',
            'brand' => 'Kala',
            'price' => 4999,
            'reviews' => 18,
            'image' => 'https://www.bajaao.com/cdn/shop/files/kala-soprano-ukuleles-kala-makala-mk-s-soprano-ukulele-18300244328609.jpg?v=1686443810&width=1920'
        ],
        [
            'name' => 'Roland JUPITER-XM Rising Jupiter Series Synthesizer',
            'brand' => 'Roland',
            'price' => 181261,
            'reviews' => 25,
            'image' => 'https://www.bajaao.com/cdn/shop/files/roland-synthesizers-roland-jupiter-xm-rising-jupiter-series-synthesizer-1176521097.png?v=1768280708&width=1920'
        ],
        [
            'name' => 'Akai MPK Mini Play Mini Controller Keyboard with Built-in Speakers With MPC Beats Software Pack',
            'brand' => 'Akai',
            'price' => 9375,
            'reviews' => 61,
            'image' => 'https://www.bajaao.com/cdn/shop/files/akai-midi-keyboards-mk3-akai-mpk-mini-play-mini-controller-keyboard-with-built-in-speakers-with-mpc-beats-software-pack-1177300429.jpg?v=1769755592&width=1920'
        ],
        [
            'name' => 'Focusrite Scarlett 2i2 4th Gen USB 2.0 Audio Interface',
            'brand' => 'Focusrite',
            'price' => 26533,
            'reviews' => 33,
            'image' => 'https://www.bajaao.com/cdn/shop/files/FCR-SCR2I24.jpg?v=1782732174&width=1920'
        ],
        [
            'name' => 'KRK Classic 7 Active 2-Way Professional Studio Monitor - Single - Black',
            'brand' => 'KRK',
            'price' => 22299,
            'reviews' => 27,
            'image' => 'https://www.bajaao.com/cdn/shop/files/krk-monitor-speakers-krk-classic-7-active-2-way-professional-studio-monitor-single-black-31865208471731.jpg?v=1687776513&width=1920'
        ],
        [
            'name' => 'Audio-Technica ATH-M20X Headphones',
            'brand' => 'Audio-Technica',
            'price' => 4197,
            'reviews' => 54,
            'image' => 'https://www.bajaao.com/cdn/shop/files/audio-technica-headphones-audio-technica-ath-m20x-headphones-22877388439731.jpg?v=1742013789&width=1920'
        ],
        [
            'name' => 'Audio-Technica ATM510 Cardioid Dynamic Handheld Microphone',
            'brand' => 'Audio-Technica',
            'price' => 8200,
            'reviews' => 19,
            'image' => 'https://www.bajaao.com/cdn/shop/files/audio-technica-dynamic-microphones-audio-technica-atm510-cardioid-dynamic-handheld-microphone-13893553881160.jpg?v=1688169095&width=1920'
        ],
        [
            'name' => 'Alesis Nitro Pro XL 10-Piece Electronic Drum kit with Mesh Heads & Bluetooth',
            'brand' => 'Alesis',
            'price' => 90608,
            'reviews' => 38,
            'image' => 'https://www.bajaao.com/cdn/shop/files/alesis-electronic-drum-kits-alesis-nitro-pro-xl-10-piece-electronic-drum-kit-with-mesh-heads-bluetooth-1187135840.jpg?v=1768145949&width=1920'
        ],
        [
            'name' => 'Roland EC10 El Cajon Hybrid Cajon',
            'brand' => 'Roland',
            'price' => 57485,
            'reviews' => 22,
            'image' => 'https://www.bajaao.com/cdn/shop/files/roland-cajons-roland-ec10-el-cajon-hybrid-cajon-1176521320.png?v=1768094242&width=1920'
        ],
        [
            'name' => 'Ultimate Guru Taal Sangat Digital Tabla',
            'brand' => 'Ultimate Guru',
            'price' => 6200,
            'reviews' => 17,
            'image' => 'https://www.bajaao.com/cdn/shop/files/ultimate-guru-other-indian-percussion-taal-sangat-digital-tabla-12538672771.jpg?v=1688490765&width=1920'
        ],
        [
            'name' => 'Hohner M254001S Ocean Star 24-Hole Tremolo Harmonica/organ - Key C',
            'brand' => 'Hohner',
            'price' => 836,
            'reviews' => 45,
            'image' => 'https://www.bajaao.com/cdn/shop/files/hohner-harmonicas-hohner-m254001s-ocean-star-24-hole-tremolo-harmonica-organ-key-c-1252842615.jpeg?v=1686113679&width=1920'
        ],
        [
            'name' => 'Ernie Ball 2239 Super Slinky RPS9 Electric Guitar Strings',
            'brand' => 'Ernie Ball',
            'price' => 909,
            'reviews' => 30,
            'image' => 'https://www.bajaao.com/cdn/shop/files/ernie-ball-electric-guitar-strings-ernie-ball-2239-super-slinky-rps9-electric-guitar-strings-34264274206899.png?v=1707561395&width=1920'
        ],
        [
            'name' => 'Granada Adagio Complete Violin with Bow & Case - Full Size',
            'brand' => 'Granada',
            'price' => 7505,
            'reviews' => 12,
            'image' => 'https://www.bajaao.com/cdn/shop/files/granada-violins-granada-adagio-complete-violin-with-bow-case-full-size-34991213772979.jpg?v=1714818097&width=1920'
        ],
        [
            'name' => 'Casio Privia PX-860 88-Key Digital Piano With Piano Stool',
            'brand' => 'Casio',
            'price' => 76937,
            'reviews' => 29,
            'image' => 'https://www.bajaao.com/cdn/shop/files/casio-digital-pianos-casio-privia-px-860-88-key-digital-piano-with-piano-stool-12837748867.jpg?v=1686255161&width=1920'
        ]
    ],
];
