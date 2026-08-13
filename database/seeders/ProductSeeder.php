<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Bajaao-inspired instrument catalog (reference only — copy uniquely
 * rewritten, no verbatim text). Product images land in Phase B/C via
 * admin MediaLibrary uploads (Bajaao product shots per AGENT_RULES).
 *
 * Data shape per product:
 *   category => child-category slug · brand => brand slug
 *   price/compare_at in INR · variants => optional finish/type rows
 */
class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // ───────────────────────── GUITARS ─────────────────────────
            [
                'category' => 'acoustic-guitars', 'brand' => 'yamaha',
                'name' => 'Yamaha F310 Acoustic Guitar',
                'sku' => 'RYM-ACO-001', 'price' => 8499, 'compare_at' => 9999,
                'stock' => 12, 'featured' => true,
                'short' => 'The world’s best-selling beginner acoustic — bold tone, easy playability, unbeatable value.',
                'desc' => '<p>The F310 has introduced more people to the guitar than almost any other instrument on the planet. Its spruce top and meranti back deliver a punchy, resonant voice that keeps up with strumming and fingerstyle alike, while the slim neck makes first chords feel effortless.</p><ul><li>Spruce top with meranti back and sides</li><li>Rosewood fingerboard and bridge</li><li>Die-cast chrome tuners hold tuning reliably</li><li>Includes gig-bag friendly compact body design</li></ul>',
                'variants' => [
                    ['Natural', 'RYM-ACO-001-NAT', null, 7],
                    ['Vintage Tint', 'RYM-ACO-001-VTN', null, 5],
                ],
            ],
            [
                'category' => 'acoustic-guitars', 'brand' => 'fender',
                'name' => 'Fender CD-60S Dreadnought Acoustic Guitar',
                'sku' => 'RYM-ACO-002', 'price' => 17499, 'compare_at' => 20999,
                'stock' => 8, 'featured' => true,
                'short' => 'A solid-voiced Fender dreadnought with a spruce top and comfortable dreadnought body.',
                'desc' => '<p>The CD-60S brings Fender’s classic American design philosophy to an affordable dreadnought. A solid spruce top drives a warm, projecting tone, and the rolled fingerboard edges make long practice sessions genuinely comfortable.</p><ul><li>Solid spruce top with mahogany back and sides</li><li>Easy-to-play neck with rolled edges</li><li>Rosewood fingerboard with 20 frets</li><li>Includes Fender padded gig bag</li></ul>',
                'variants' => [
                    ['Natural', 'RYM-ACO-002-NAT', null, 5],
                    ['Sunburst', 'RYM-ACO-002-SUN', null, 3],
                ],
            ],
            [
                'category' => 'electric-guitars', 'brand' => 'squier',
                'name' => 'Squier Affinity Stratocaster HSS',
                'sku' => 'RYM-ELE-001', 'price' => 34999, 'compare_at' => 39999,
                'stock' => 6, 'featured' => true,
                'short' => 'The classic Strat experience — HSS pickups, sleek Affinity body, ready for any genre.',
                'desc' => '<p>With its humbucking bridge pickup and two single coils, the Affinity Strat HSS covers everything from warm cleans to crunchy rock leads. The slim C-shaped neck and sealed tuners keep it comfortable and dependable from the first gig to the hundredth.</p><ul><li>HSS pickup configuration for maximum versatility</li><li>Six-saddle vintage-style tremolo bridge</li><li>Slim, comfortable C-shaped maple neck</li><li>Sealed die-cast tuning machines</li></ul>',
                'variants' => [
                    ['Black', 'RYM-ELE-001-BLK', null, 3],
                    ['Sunburst', 'RYM-ELE-001-SUN', null, 2],
                    ['Daphne Blue', 'RYM-ELE-001-DBL', null, 1],
                ],
            ],
            [
                'category' => 'electric-guitars', 'brand' => 'epiphone',
                'name' => 'Epiphone Les Paul Special-II',
                'sku' => 'RYM-ELE-002', 'price' => 27999, 'compare_at' => 33999,
                'stock' => 5, 'featured' => false,
                'short' => 'Gibson-grade looks and tone at an approachable price — a true rock workhorse.',
                'desc' => '<p>The Les Paul Special-II carries the unmistakable single-cut silhouette with a pair of open-coil humbuckers that bark, sing and sustain. It is a no-nonsense rock machine that also cleans up beautifully at lower gain.</p><ul><li>Two open-coil humbuckers with 3-way switching</li><li>Set mahogany neck for sustain</li><li>Fixed bridge for rock-solid tuning stability</li><li>Iconic Les Paul styling in glossy finishes</li></ul>',
                'variants' => [
                    ['Ebony', 'RYM-ELE-002-EBO', null, 3],
                    ['Cherry Sunburst', 'RYM-ELE-002-CHS', null, 2],
                ],
            ],
            [
                'category' => 'electric-guitars', 'brand' => 'ibanez',
                'name' => 'Ibanez GRX70QA-TRB Electric Guitar',
                'sku' => 'RYM-ELE-003', 'price' => 17999, 'compare_at' => 21999,
                'stock' => 9, 'featured' => false,
                'short' => 'A flame-topped Ibanez that plays fast and looks faster.',
                'desc' => '<p>The GRX70QA pairs a striking quilted-art top with Ibanez’s famously slim neck profile, giving beginners and intermediates an instrument that keeps up with their speed. Dual humbuckers deliver thick, defined tone for rock and metal.</p><ul><li>Quilted art top with eye-catching finish</li><li>Wizard-style slim neck profile</li><li>Two humbuckers with 5-way switching</li><li>Chrome hardware and smooth tremolo</li></ul>',
            ],
            [
                'category' => 'bass-guitars', 'brand' => 'squier',
                'name' => 'Squier Affinity Precision Bass',
                'sku' => 'RYM-BAS-001', 'price' => 33999, 'compare_at' => 39999,
                'stock' => 4, 'featured' => false,
                'short' => 'The bass that defined rock — now with modern playability and classic tone.',
                'desc' => '<p>From Motown to punk, the Precision Bass shape has carried popular music for seven decades. This Affinity edition pairs the iconic split single-coil growl with a slim neck and lightweight body that feels great on stage and in the studio.</p><ul><li>Split single-coil P-Bass pickup</li><li>Slim, fast-playing neck profile</li><li>Four-saddle bridge for solid intonation</li><li>Classic P-Bass body contours</li></ul>',
                'variants' => [
                    ['Black', 'RYM-BAS-001-BLK', null, 2],
                    ['Butterscotch', 'RYM-BAS-001-BTC', null, 2],
                ],
            ],
            [
                'category' => 'bass-guitars', 'brand' => 'yamaha',
                'name' => 'Yamaha TRBX174 Bass Guitar',
                'sku' => 'RYM-BAS-002', 'price' => 24999, 'compare_at' => 29999,
                'stock' => 7, 'featured' => false,
                'short' => 'Modern ergonomics and punchy pickups — the ideal first serious bass.',
                'desc' => '<p>The TRBX174 is engineered around comfort: a contoured body, smooth neck joint and balanced weight make long rehearsals painless. Its ceramic pickups give a tight, modern low end that sits perfectly in any mix.</p><ul><li>Ceramic pickups with active-style clarity</li><li>Contoured body with deep cutaways</li><li>24-fret rosewood fingerboard</li><li>Die-cast tuners and solid bridge</li></ul>',
                'variants' => [
                    ['Black', 'RYM-BAS-002-BLK', null, 4],
                    ['Vintage White', 'RYM-BAS-002-VWH', null, 3],
                ],
            ],
            [
                'category' => 'classical-guitars', 'brand' => 'yamaha',
                'name' => 'Yamaha C40 Classical Guitar',
                'sku' => 'RYM-CLA-001', 'price' => 7999, 'compare_at' => 9499,
                'stock' => 10, 'featured' => false,
                'short' => 'A trusted nylon-string classic — the standard choice for classical lessons.',
                'desc' => '<p>The C40 is the guitar most Indian music schools recommend, and for good reason: a spruce top over meranti back and sides produces a sweet, balanced nylon-string voice, while the standard classical scale makes proper technique easy to learn.</p><ul><li>Spruce top with warm nylon-string tone</li><li>Traditional classical body shape</li><li>Rosewood fingerboard and bridge</li><li>Built-in tonewoods chosen for durability</li></ul>',
            ],
            [
                'category' => 'ukuleles', 'brand' => 'kala',
                'name' => 'Kala KA-15S Soprano Ukulele',
                'sku' => 'RYM-UKE-001', 'price' => 4499, 'compare_at' => 5499,
                'stock' => 14, 'featured' => false,
                'short' => 'The ukulele that started a million island songs — classic soprano, all-mahogany.',
                'desc' => '<p>Kala made the ukulele famous again, and the KA-15S is their most loved model. An all-mahogany body gives a warm, resonant voice, and the compact soprano size makes it the perfect travel companion and first instrument.</p><ul><li>All-mahogany body with rich, warm tone</li><li>Classic soprano size — great for travel</li><li>Aquila Super Nylgut strings included</li><li>Geared tuners for stable tuning</li></ul>',
            ],
            [
                'category' => 'guitar-amps', 'brand' => 'fender',
                'name' => 'Fender Mustang LT25 Modelling Amp',
                'sku' => 'RYM-AMP-001', 'price' => 16499, 'compare_at' => 19999,
                'stock' => 6, 'featured' => false,
                'short' => 'Twenty-five watts of Fender tone with dozens of amp models and effects built in.',
                'desc' => '<p>The LT25 puts a full rig inside a single compact combo. Twenty presets span sparkling cleans to high-gain crunch, and the built-in tuner, aux input and headphone out make quiet practice genuinely enjoyable.</p><ul><li>25W with 20 presets and 100+ editable tones</li><li>Built-in effects: reverb, delay, chorus and more</li><li>Aux input for jamming along to tracks</li><li>Headphone output for silent practice</li></ul>',
            ],
            [
                'category' => 'guitar-amps', 'brand' => 'boss',
                'name' => 'Boss Katana-50 MKII',
                'sku' => 'RYM-AMP-002', 'price' => 27999, 'compare_at' => 32999,
                'stock' => 5, 'featured' => false,
                'short' => 'The amp that took over bedrooms and stages worldwide — five characters, one box.',
                'desc' => '<p>The Katana-50 MKII builds on the amp that changed the budget market. Five amp characters, over 60 Boss effects, and a power control that delivers authentic tone at any volume make it the single most versatile practice amp you can buy.</p><ul><li>Five amp characters: Clean, Crunch, Lead, Brown, Acoustic</li><li>60+ Boss effects, editable via software</li><li>0.5W/25W/50W power control for home or stage</li><li>USB recording and stereo expand support</li></ul>',
            ],
            [
                'category' => 'guitar-strings', 'brand' => 'daddario',
                'name' => 'D’Addario EJ16 Phosphor Bronze Acoustic Strings',
                'sku' => 'RYM-STR-001', 'price' => 749, 'compare_at' => 899,
                'stock' => 40, 'featured' => false,
                'short' => 'The reference acoustic string — bright phosphor bronze with rich harmonics.',
                'desc' => '<p>EJ16 has been the benchmark acoustic string for decades. The 80/20-free phosphor bronze alloy delivers warm, projecting tone with excellent intonation, and the corrosion-resistant wrap keeps them sounding fresh.</p><ul><li>Phosphor bronze wrap for warm, balanced tone</li><li>Light gauge (12-53) — easy to fret and bend</li><li>Corrosion-resistant for long life</li><li>Made in the USA</li></ul>',
            ],
            [
                'category' => 'guitar-strings', 'brand' => 'ernie-ball',
                'name' => 'Ernie Ball Super Slinky 2215 Electric Strings',
                'sku' => 'RYM-STR-002', 'price' => 649, 'compare_at' => 799,
                'stock' => 45, 'featured' => false,
                'short' => 'The string on more rock records than any other — bright, balanced, unmistakable.',
                'desc' => '<p>Super Slinky 2215 has powered generations of guitar heroes. Its bright, balanced tone and famously comfortable 9-42 gauge make it the default choice for rock, blues and pop players alike.</p><ul><li>Nickel-plated steel wrap with steel core</li><li>Light 9-42 gauge for effortless bending</li><li>Bright, punchy tone with long sustain</li><li>Environmentally friendly packaging</li></ul>',
            ],
            [
                'category' => 'guitar-strings', 'brand' => 'elixir',
                'name' => 'Elixir Nanoweb 80/20 Acoustic Strings',
                'sku' => 'RYM-STR-003', 'price' => 2199, 'compare_at' => 2599,
                'stock' => 25, 'featured' => false,
                'short' => 'Coated strings that keep their tone three to five times longer than uncoated.',
                'desc' => '<p>Elixir strings are famous for one thing: longevity. The ultra-thin Nanoweb coating blocks the gunk that kills tone, so your guitar sounds like new for months — a favourite of touring professionals.</p><ul><li>Nanoweb coating for 3–5x longer tone life</li><li>Bright 80/20 bronze tone, smooth feel</li><li>Light gauge (12-53)</li><li>Anti-rust construction</li></ul>',
            ],

            // ───────────────────── KEYBOARDS & PIANOS ─────────────────────
            [
                'category' => 'portable-keyboards', 'brand' => 'yamaha',
                'name' => 'Yamaha PSR-E373 Portable Keyboard',
                'sku' => 'RYM-KEY-001', 'price' => 18999, 'compare_at' => 22999,
                'stock' => 8, 'featured' => false,
                'short' => '622 voices, touch response and a dedicated lesson suite — the complete starter keyboard.',
                'desc' => '<p>The PSR-E373 is the keyboard Yamaha built for learning to stick. Touch-sensitive keys respond to your dynamics, 622 voices cover every genre, and the built-in lesson functions turn practice into progress.</p><ul><li>622 voices and 205 accompaniment styles</li><li>Touch-responsive keys with 48-note polyphony</li><li>Portable grand piano voice with damper resonance</li><li>USB-to-host for computer music lessons</li></ul>',
            ],
            [
                'category' => 'portable-keyboards', 'brand' => 'casio',
                'name' => 'Casio CT-S300 Portable Keyboard',
                'sku' => 'RYM-KEY-002', 'price' => 12999, 'compare_at' => 15999,
                'stock' => 10, 'featured' => false,
                'short' => 'A featherweight 61-key keyboard with big piano sound and dance-ready rhythms.',
                'desc' => '<p>The CT-S300 weighs barely 3.4kg yet sounds anything but small. Its dance music mode, 400 tones and crisp speaker system make it equally fun for beginners and producers sketching ideas on the sofa.</p><ul><li>400 tones and 77 rhythms including dance mode</li><li>Ultra-light 3.4kg body with battery option</li><li>61 touch-sensitive keys</li><li>USB MIDI and audio input</li></ul>',
            ],
            [
                'category' => 'digital-pianos', 'brand' => 'yamaha',
                'name' => 'Yamaha P-145 Digital Piano',
                'sku' => 'RYM-DPI-001', 'price' => 57999, 'compare_at' => 68999,
                'stock' => 4, 'featured' => false,
                'short' => 'Graded hammer action and a superb grand piano sample — the serious beginner’s piano.',
                'desc' => '<p>The P-145 delivers the feel of a concert grand in a slim, portable body. Yamaha’s Graded Hammer Compact action gives heavier low keys and lighter highs, while the sampled CFX concert grand voice responds to every nuance of your touch.</p><ul><li>GHC key action with graded hammer weight</li><li>CFX concert grand piano sample</li><li>20 voices with split and duo modes</li><li>Headphone output with enhanced spatial sound</li></ul>',
                'variants' => [
                    ['Black', 'RYM-DPI-001-BLK', null, 2],
                    ['White', 'RYM-DPI-001-WHT', null, 2],
                ],
            ],
            [
                'category' => 'digital-pianos', 'brand' => 'roland',
                'name' => 'Roland FP-30X Digital Piano',
                'sku' => 'RYM-DPI-002', 'price' => 61999, 'compare_at' => 72999,
                'stock' => 5, 'featured' => true,
                'short' => 'The favourite home piano of teachers worldwide — SuperNATURAL sound, PHA-4 keys.',
                'desc' => '<p>The FP-30X is the piano most teachers recommend because it feels right from day one. Its PHA-4 Standard keyboard with escapement replicates an acoustic grand’s response, and the SuperNATURAL engine delivers piano tones with real character.</p><ul><li>PHA-4 Standard keys with escapement</li><li>SuperNATURAL piano sound engine</li><li>Bluetooth audio and MIDI built in</li><li>Twin piano mode for lessons</li></ul>',
                'variants' => [
                    ['Black', 'RYM-DPI-002-BLK', null, 3],
                    ['White', 'RYM-DPI-002-WHT', null, 2],
                ],
            ],
            [
                'category' => 'synthesizers', 'brand' => 'korg',
                'name' => 'Korg Volca Keys Analog Synthesizer',
                'sku' => 'RYM-SYN-001', 'price' => 9999, 'compare_at' => 11999,
                'stock' => 11, 'featured' => false,
                'short' => 'A three-voice analog synth that fits in your palm — the gateway to synthesis.',
                'desc' => '<p>The Volca Keys packs genuine analog oscillators, a 16-step sequencer and a delay section into a box smaller than a paperback. It is the easiest and most fun way to learn subtractive synthesis.</p><ul><li>Three-note polyphony with true analog oscillators</li><li>16-step sequencer with motion sequencing</li><li>Built-in delay effect</li><li>Syncs with other Volca gear and DAWs</li></ul>',
            ],
            [
                'category' => 'midi-controllers', 'brand' => 'm-audio',
                'name' => 'M-Audio Oxygen 49 MKIV MIDI Keyboard',
                'sku' => 'RYM-MID-001', 'price' => 17999, 'compare_at' => 21999,
                'stock' => 9, 'featured' => false,
                'short' => '49 velocity-sensitive keys with faders, pads and full DAW control.',
                'desc' => '<p>The Oxygen 49 is the all-rounder of MIDI keyboards: 49 keys, eight backlit pads, eight assignable knobs and transport controls that map instantly to every major DAW. It ships with a bundle of software worth more than the keyboard itself.</p><ul><li>49 velocity-sensitive synth-action keys</li><li>8 backlit MPC-style pads</li><li>8 knobs, 9 faders and transport buttons</li><li>Includes MPC Beats, Ableton Live Lite and more</li></ul>',
            ],

            // ─────────────────────── DRUMS & PERCUSSION ───────────────────────
            [
                'category' => 'electronic-drum-kits', 'brand' => 'alesis',
                'name' => 'Alesis Nitro Mesh Kit',
                'sku' => 'RYM-DRM-001', 'price' => 31999, 'compare_at' => 38999,
                'stock' => 6, 'featured' => true,
                'short' => 'Mesh-head electronic drums with 40 kits — practice quietly, play loudly.',
                'desc' => '<p>The Nitro Mesh is the electronic kit that made home drumming genuinely feel like drumming. Dual-zone mesh heads respond to real stick technique, while 40 preset kits and a 60-track sequencer keep practice inspiring.</p><ul><li>8\u2033 dual-zone mesh snare and 8\u2033 mesh toms</li><li>40 drum kits and 60 practice tracks</li><li>USB-MIDI for recording in your DAW</li><li>Includes drumsticks, cable and kick pedal</li></ul>',
            ],
            [
                'category' => 'electronic-drum-kits', 'brand' => 'roland',
                'name' => 'Roland TD-1DMK V-Drums Kit',
                'sku' => 'RYM-DRM-002', 'price' => 54999, 'compare_at' => 64999,
                'stock' => 3, 'featured' => false,
                'short' => 'Roland’s entry V-Drums with mesh pads and the best module sounds in the class.',
                'desc' => '<p>The TD-1DMK brings Roland’s pro V-Drums DNA to a compact home kit. Mesh pads feel close to acoustic drums, and the module’s sounds — including the legendary TD-50-derived kits — set the benchmark for the price.</p><ul><li>12\u2033 mesh snare and mesh toms</li><li>SuperNATURAL drum sounds from Roland’s pro modules</li><li>Bluetooth audio streaming for playing along</li><li>Coach functions for daily practice</li></ul>',
            ],
            [
                'category' => 'cymbals', 'brand' => 'zildjian',
                'name' => 'Zildjian Planet Z 5-Piece Cymbal Pack',
                'sku' => 'RYM-CYM-001', 'price' => 27999, 'compare_at' => 32999,
                'stock' => 4, 'featured' => false,
                'short' => 'The complete starter cymbal setup from the house that made cymbals famous.',
                'desc' => '<p>Planet Z gives you a full cymbal rig — 14\u2033 hi-hats, 16\u2033 crash and 20\u2033 ride — from the brand that has defined cymbal sound since 1623. Bright, durable and cut-through-the-mix loud, they are ideal for learners and gigging beginners.</p><ul><li>14\u2033 hi-hats, 16\u2033 crash, 20\u2033 ride</li><li>Brilliant finish with bright, cutting tone</li><li>Made from durable Zildjian B8 bronze</li><li>Perfect first upgrade from stock cymbals</li></ul>',
            ],

            // ───────────────────────── PRO AUDIO ─────────────────────────
            [
                'category' => 'audio-interfaces', 'brand' => 'focusrite',
                'name' => 'Focusrite Scarlett Solo 3rd Gen',
                'sku' => 'RYM-AUD-001', 'price' => 12999, 'compare_at' => 15999,
                'stock' => 10, 'featured' => true,
                'short' => 'The world’s favourite USB interface — studio-grade preamp, one simple box.',
                'desc' => '<p>The Scarlett Solo is the interface that started a million home studios. Its high-headroom instrument input and award-winning preamp capture vocals and guitars with clarity, while Air mode adds instant studio polish.</p><ul><li>High-headroom instrument input for guitar and bass</li><li>Air mode for brighter, open recordings</li><li>USB-C connectivity with low latency</li><li>Includes Ableton Live Lite and Pro Tools First</li></ul>',
            ],
            [
                'category' => 'microphones', 'brand' => 'shure',
                'name' => 'Shure SM58 Vocal Microphone',
                'sku' => 'RYM-MIC-001', 'price' => 10999, 'compare_at' => 13499,
                'stock' => 15, 'featured' => true,
                'short' => 'The most used microphone in the world — built to be dropped, drowned and adored.',
                'desc' => '<p>For six decades the SM58 has been the first mic on every stage. Its cardioid pickup rejects feedback, the legendary capsule flatters vocals, and its build quality means one purchase can last a lifetime of gigs.</p><ul><li>Cardioid pickup pattern rejects feedback</li><li>Tailored frequency response for vocals</li><li>Rugged steel mesh grille with built-in wind protection</li><li>Included stand adapter and storage bag</li></ul>',
            ],
            [
                'category' => 'headphones', 'brand' => 'akg',
                'name' => 'AKG K240 Studio Headphones',
                'sku' => 'RYM-HPH-001', 'price' => 7499, 'compare_at' => 8999,
                'stock' => 12, 'featured' => false,
                'short' => 'The studio staple since 1984 — open-back monitoring you can trust.',
                'desc' => '<p>The K240 Studio has monitored more records than any headphone in history. Its open-back design gives a natural, uncoloured soundstage, and the self-adjusting headband makes marathon sessions painless.</p><ul><li>Open-back design for natural, honest sound</li><li>55-ohm impedance works with any device</li><li>Self-adjusting leather headband</li><li>Detachable mini-XLR cable</li></ul>',
            ],
            [
                'category' => 'studio-monitors', 'brand' => 'krk',
                'name' => 'KRK Rokit 5 G4 Studio Monitor (Single)',
                'sku' => 'RYM-MON-001', 'price' => 26999, 'compare_at' => 31999,
                'stock' => 8, 'featured' => true,
                'short' => 'The most recognisable monitor in home studios — punchy, loud and mix-ready.',
                'desc' => '<p>Rokit G4 monitors are everywhere for a reason: they make mixes translate. The 5\u2033 woofer delivers tight, musical bass, the Kevlar tweeter handles highs without fatigue, and built-in DSP tuning adapts the sound to your room.</p><ul><li>5\u2033 Kevlar woofer with 1\u2033 tweeter</li><li>Built-in DSP with room EQ (via KRK app)</li><li>Bi-amped 85W Class-D power</li><li>High-frequency waveguide for wide sweet spot</li></ul>',
            ],
            [
                'category' => 'mixers', 'brand' => 'behringer',
                'name' => 'Behringer Xenyx 802 Mixer',
                'sku' => 'RYM-MIX-001', 'price' => 6499, 'compare_at' => 7999,
                'stock' => 10, 'featured' => false,
                'short' => 'Eight inputs, pristine preamps and a British-style EQ — the compact band workhorse.',
                'desc' => '<p>The Xenyx 802 squeezes pro features into a lunchbox-sized mixer: two studio-grade Xenyx preamps, British-style 3-band EQ, and a built-in effects send that keeps your signal chain simple.</p><ul><li>2 Xenyx mic preamps with +48V phantom power</li><li>British-style 3-band EQ for tone shaping</li><li>FX send for external effects</li><li>2-track input for backing tracks</li></ul>',
            ],

            // ───────────────────────── DJ & STAGE ─────────────────────────
            [
                'category' => 'dj-controllers', 'brand' => 'pioneer-dj',
                'name' => 'Pioneer DJ DDJ-FLX4 Controller',
                'sku' => 'RYM-DJC-001', 'price' => 41999, 'compare_at' => 48999,
                'stock' => 5, 'featured' => true,
                'short' => 'The controller that matches the club standard — Smart CFX and pro feel in one box.',
                'desc' => '<p>The DDJ-FLX4 puts the club-standard layout of Pioneer’s pro players into an entry-level controller. Smart CFX adds effects with a single touch, and the included rekordbox and Serato Lite software get you mixing within minutes.</p><ul><li>Same layout as pro CDJ/DJM setups</li><li>Smart CFX one-touch FX</li><li>Includes rekordbox and Serato DJ Lite</li><li>USB-C powered — no power supply needed</li></ul>',
            ],
            [
                'category' => 'dj-controllers', 'brand' => 'numark',
                'name' => 'Numark Mixtrack Pro FX',
                'sku' => 'RYM-DJC-002', 'price' => 29999, 'compare_at' => 34999,
                'stock' => 6, 'featured' => false,
                'short' => 'Two decks, four pads each and pro FX — the complete beginner DJ package.',
                'desc' => '<p>The Mixtrack Pro FX gives beginners everything they need to learn: two 2-deck controllers with 16 velocity-sensitive pads, dedicated FX controls and a built-in sound card for headphone cueing.</p><ul><li>16 backlit performance pads</li><li>Dedicated filter and FX controls</li><li>Built-in audio interface with cue output</li><li>Serato DJ Lite included</li></ul>',
            ],

            // ───────────────────────── ACCESSORIES ─────────────────────────
            [
                'category' => 'cables-connectors', 'brand' => 'daddario',
                'name' => 'Planet Waves Classic Series Instrument Cable (10ft)',
                'sku' => 'RYM-CAB-001', 'price' => 999, 'compare_at' => 1199,
                'stock' => 30, 'featured' => false,
                'short' => 'A quiet, reliable 10-foot instrument cable with strain-relief engineered connectors.',
                'desc' => '<p>Planet Waves cables are built around one idea: signal that never drops out. The Classic Series 10ft cable uses oxygen-free copper and a sturdy strain-relief boot that shrugs off stage abuse.</p><ul><li>10ft length — ideal for stage and studio</li><li>Oxygen-free copper conductors</li><li>Molded strain relief at both ends</li><li>Lifetime warranty against defects</li></ul>',
            ],
            [
                'category' => 'stands-bags', 'brand' => 'hercules',
                'name' => 'Hercules GS412B Guitar Stand',
                'sku' => 'RYM-STD-001', 'price' => 3499, 'compare_at' => 4199,
                'stock' => 12, 'featured' => false,
                'short' => 'Auto-grip yoke that locks your guitar safely — the gig-ready standard.',
                'desc' => '<p>The GS412B’s patented auto-grip yoke opens on contact and locks the neck securely, so guitars never roll off mid-song. Foldable to pocket size, it fits in any gig bag and works for acoustic and electric alike.</p><ul><li>Auto-grip yoke secures the neck instantly</li><li>Folds compactly for transport</li><li>Fits acoustic and electric guitars</li><li>Solid, roadworthy construction</li></ul>',
            ],
            [
                'category' => 'picks-capos', 'brand' => 'fender',
                'name' => 'Fender 351 Shape Picks 12-Pack (Medium)',
                'sku' => 'RYM-PCK-001', 'price' => 399, 'compare_at' => 499,
                'stock' => 50, 'featured' => false,
                'short' => 'The classic 351 pick shape in a 12-pack of dependable mediums.',
                'desc' => '<p>The 351 shape has been the default pick for generations. This 12-pack of medium-gauge celluloid picks gives you a supply of the same reliable feel Fender has shipped since 1952.</p><ul><li>Classic 351 shape, medium gauge</li><li>Celluloid construction with smooth edge</li><li>12 picks per pack — always one in reach</li><li>Made in the USA</li></ul>',
            ],
        ];

        foreach ($products as $data) {
            $category = Category::where('slug', $data['category'])->firstOrFail();
            $brand = Brand::where('slug', $data['brand'])->firstOrFail();

            $product = Product::firstOrCreate(
                ['sku' => $data['sku']],
                [
                    'category_id' => $category->id,
                    'brand_id' => $brand->id,
                    'name' => $data['name'],
                    'slug' => Str::slug($data['name']),
                    'short_description' => $data['short'],
                    'description' => $data['desc'],
                    'price' => $data['price'],
                    'compare_at_price' => $data['compare_at'] ?? null,
                    'stock' => $data['stock'],
                    'low_stock_threshold' => 5,
                    'is_active' => true,
                    'is_featured' => $data['featured'] ?? false,
                    'meta_title' => "Buy {$data['name']} Online in India | Rythme Music Store",
                    'meta_description' => $data['short'],
                ],
            );

            foreach ($data['variants'] ?? [] as [$name, $sku, $priceOverride, $stock]) {
                ProductVariant::firstOrCreate(
                    ['product_id' => $product->id, 'name' => $name],
                    [
                        'sku' => $sku,
                        'options' => ['finish' => $name],
                        'price_override' => $priceOverride,
                        'stock' => $stock,
                        'is_active' => true,
                    ],
                );
            }
        }
    }
}
