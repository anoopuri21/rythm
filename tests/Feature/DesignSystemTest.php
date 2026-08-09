<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Design system guard rails: RED (#d50808) / BLACK / WHITE only — no gold/yellow,
 * Poppins font only, product slider images use object-fit: contain.
 */
class DesignSystemTest extends TestCase
{
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

    public function test_tailwind_theme_is_red_black_white(): void
    {
        $config = file_get_contents(base_path('tailwind.config.js'));

        $this->assertStringContainsString("DEFAULT: '#d50808'", $config);
        $this->assertStringContainsString("black: '#000000'", $config);
        $this->assertStringContainsString("cream: '#ffffff'", $config);
        $this->assertStringNotContainsString('#C41E3A', $config); // old red gone
    }

    public function test_poppins_is_the_only_loaded_font(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('family=Poppins', escape: false)
            ->assertDontSee('family=Playfair', escape: false)
            ->assertDontSee('family=Inter', escape: false)
            ->assertDontSee('family=Bebas', escape: false);
    }

    public function test_explore_slider_images_use_object_contain(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('products-swiper swiper', escape: false)
            ->assertSee('object-contain p-5', escape: false)
            ->assertSee('Bestseller', escape: false)
            ->assertSee('object-contain p-6', escape: false); // product-card too
    }

    public function test_red_buttons_render(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('bg-rythme-red', escape: false)
            ->assertSee('hover:bg-rythme-red-dark', escape: false);
    }
}
