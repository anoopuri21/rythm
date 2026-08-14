<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            'Fender' => 'Iconic American electric and acoustic guitars, amps and accessories.',
            'Squier' => 'Fender’s accessible line of classic designs for every player.',
            'Yamaha' => 'Guitars, keyboards and pro audio trusted by musicians worldwide.',
            'Epiphone' => 'Affordable interpretations of legendary Gibson designs.',
            'Ibanez' => 'Fast-playing guitars built for modern genres and shredders.',
            'Roland' => 'Digital pianos, drum machines and electronic instruments.',
            'Boss' => 'Effects pedals and amplifiers that defined generations of tone.',
            'Korg' => 'Synthesizers, keyboards and tuners for stage and studio.',
            'Casio' => 'Portable keyboards and digital pianos with incredible value.',
            'Alesis' => 'Electronic drum kits and studio gear for home creators.',
            'Zildjian' => 'The world’s most famous cymbal maker since 1623.',
            'Shure' => 'Microphones trusted on the biggest stages and studios.',
            'AKG' => 'Studio headphones and microphones with Austrian engineering.',
            'Focusrite' => 'Audio interfaces that put pro recording within reach.',
            'KRK' => 'Studio monitors that are the reference in home studios.',
            'Behringer' => 'Mixers and audio gear with unbeatable price-to-performance.',
            'Pioneer DJ' => 'The industry-standard DJ controllers and players.',
            'Numark' => 'Entry-level DJ controllers with serious features.',
            'M-Audio' => 'MIDI keyboards and interfaces for music production.',
            'D’Addario' => 'Strings and accessories the world’s best players rely on.',
            'Ernie Ball' => 'The string brand of rock, metal and beyond.',
            'Elixir' => 'Coated strings that sound great for longer.',
            'Kala' => 'Premium ukuleles that made the instrument famous again.',
            'Hercules' => 'Rock-solid stands and hardware for gigging musicians.',
        ];

        $sort = 0;

        foreach ($brands as $name => $desc) {
            Brand::firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => $desc,
                    'sort_order' => $sort++,
                    'is_active' => true,
                ],
            );
        }
    }
}
