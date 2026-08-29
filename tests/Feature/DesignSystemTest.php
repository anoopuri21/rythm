<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Design-system guard rails (v3 RYTHME RED MARKETPLACE): approved deep-red
 * accent, neutral surfaces, Inter-only typography and contained catalog media.
 */
class DesignSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_no_legacy_gold_colors_in_css_tailwind_or_views(): void
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

    public function test_tailwind_theme_uses_approved_rythme_red_and_neutrals(): void
    {
        $config = file_get_contents(base_path('tailwind.config.js'));

        $this->assertStringContainsString("DEFAULT: '#B20202'", $config);
        $this->assertStringContainsString("dark: '#930303'", $config);
        $this->assertStringContainsString("light: '#E7F4F1'", $config);
        $this->assertStringContainsString("black: '#222222'", $config);
        $this->assertStringContainsString("cream: '#FFFFFF'", $config);
        $this->assertStringContainsString("'cream-dark': '#F7F7F8'", $config);
    }

    public function test_css_theme_exposes_approved_semantic_tokens(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString('--color-brand: #B20202;', $css);
        $this->assertStringContainsString('--color-brand-dark: #930303;', $css);
        $this->assertStringContainsString('--color-brand-soft: #E7F4F1;', $css);
        $this->assertStringContainsString('--color-ink: #222222;', $css);
        $this->assertStringContainsString('--color-paper: #FFFFFF;', $css);
        $this->assertStringContainsString('--color-paper-dark: #F7F7F8;', $css);
        $this->assertStringContainsString('--color-border: #E5E7EB;', $css);
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

    public function test_legacy_button_classes_render_with_token_backed_system(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('btn-gold btn-shine', escape: false)
            ->assertSee('btn-ghost-light', escape: false);
    }
}
