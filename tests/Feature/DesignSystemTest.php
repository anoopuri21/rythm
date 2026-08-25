<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Design system guard rails (v2 MINIMAL-TECH): MONOCHROME (black/white/grays)
 * only — no red/gold/yellow anywhere; Inter font only; product slider images
 * use object-fit: contain.
 */
class DesignSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_no_gold_colors_in_css_tailwind_or_views(): void
    {
        $haystacks = [
            file_get_contents(resource_path('css/app.css')),
            file_get_contents(base_path('tailwind.config.js')),
            implode("\n", array_map('file_get_contents', glob(resource_path('views/**/*.blade.php')))),
        ];

        foreach (['#D4A843', '#F5D061', '#B8860B', '212,168,67', '245,208,97'] as $gold) {
            foreach ($haystacks as $i => $haystack) {
                $this->assertStringNotContainsString($gold, $haystack, "gold token $gold found in source #$i");
            }
        }
    }

    public function test_tailwind_theme_is_monochrome_black_white(): void
    {
        $config = file_get_contents(base_path('tailwind.config.js'));

        $this->assertStringContainsString("DEFAULT: '#111111'", $config);
        $this->assertStringContainsString("black: '#111111'", $config);
        $this->assertStringContainsString("cream: '#ffffff'", $config);
        // No red/gold anywhere in the theme
        $this->assertStringNotContainsString('d50808', $config);
        $this->assertStringNotContainsString('FF5252', $config);
        $this->assertStringNotContainsString('a30404', $config);
    }

    public function test_no_red_or_gold_colors_in_css_or_views(): void
    {
        $haystacks = [
            file_get_contents(resource_path('css/app.css')),
            implode("\n", array_map('file_get_contents', glob(resource_path('views/**/*.blade.php')))),
        ];

        foreach (['#D50808', '#FF5252', '#A30404', '#D4A843', '#F5D061', '#B8860B', 'rgba(213, 8, 8', 'rgba(212, 168, 67'] as $red) {
            foreach ($haystacks as $i => $haystack) {
                $this->assertStringNotContainsString($red, $haystack, "red token $red found in source #$i");
            }
        }
    }

    public function test_inter_is_the_only_loaded_font(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('family=Inter', escape: false)
            ->assertDontSee('family=Poppins', escape: false)
            ->assertDontSee('family=Playfair', escape: false)
            ->assertDontSee('family=Bebas', escape: false);
    }

    public function test_category_images_render_with_fit(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('cat-card__img', escape: false)
            ->assertSee('images/categories/guitars.jpg', escape: false)
            ->assertSee('images/categories/electric-guitars.jpg', escape: false);
    }

    public function test_monochrome_buttons_and_cards_render(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('btn-gold btn-shine', escape: false)
            ->assertSee('btn-ghost-light', escape: false)
            ->assertDontSee('hover:bg-rythme-red-dark', escape: false)
            ->assertDontSee('bg-rythme-red', escape: false);
    }
}
