<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Per-section UI test cases — each homepage section is tested for its
 * structure, key content, real product imagery and responsive classes.
 */
class HomepageSectionsTest extends TestCase
{
    public function test_navbar_has_logo_search_icons_and_two_rows(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('rhythmexports.com/wp-content/uploads/2023/10/Rhythm.png', escape: false)
            // Row 1: logo + big search + icons
            ->assertSee('id="nav-search"', escape: false)
            ->assertSee('aria-label="Wishlist, 0 items"', escape: false)
            ->assertSee('aria-label="Cart, 0 items"', escape: false)
            ->assertSee('aria-label="Account"', escape: false)
            // Row 2: main categories (user-specified taxonomy)
            ->assertSee('>Guitars<', escape: false)
            ->assertSee('>Ukuleles &amp; Violins<', escape: false)
            ->assertSee('>Keyboards &amp; Pianos<', escape: false)
            ->assertSee('>Studio &amp; Recording<', escape: false)
            ->assertSee('>Drums &amp; Percussion<', escape: false)
            ->assertSee('>Software &amp; Plugins<', escape: false)
            ->assertSee('>Other<', escape: false)
            ->assertSee('>Deals<', escape: false)
            ->assertSee('>More<', escape: false)
            // sub-category dropdowns + mobile drawer
            ->assertSee('aria-haspopup="true"', escape: false)
            ->assertSee('>Violins<', escape: false)
            ->assertSee('>MIDI Controllers<', escape: false)
            ->assertSee('>Plugins &amp; Effects<', escape: false)
            ->assertSee('id="mobile-menu"', escape: false)
            ->assertSee('id="nav-search-mobile"', escape: false);
    }

    public function test_hero_slider_mode_renders_swiper_with_animated_buttons(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('hero-swiper swiper', escape: false)
            ->assertSee('hero-slide-image', escape: false)
            ->assertSee('class="btn-gold btn-shine"', escape: false)
            ->assertSee('hero-pagination', escape: false);
    }

    public function test_hero_video_mode_renders_video_banner_instead_of_slider(): void
    {
        config(['rythme.hero_mode' => 'video']);

        $this->get('/')
            ->assertOk()
            ->assertSee('<video', escape: false)
            ->assertSee('autoplay muted loop playsinline', escape: false)
            ->assertSee(config('rythme.hero_video_url'), escape: false)
            ->assertDontSee('hero-swiper swiper', escape: false)
            ->assertSee('class="btn-gold btn-shine"', escape: false);
    }

    public function test_categories_section_is_explore_by_category_with_bajaao_images(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Explore by category')
            ->assertSee('Find your')
            ->assertSee('aria-label="Guitars — 480+ instruments"', escape: false)
            ->assertSee('bajaao.com/cdn/shop/files', escape: false)
            ->assertSee('cat-card', escape: false);
    }

    public function test_categories_section_has_products_slider_with_bajaao_products(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('products-swiper swiper', escape: false)
            ->assertSee('products-prev', escape: false)
            ->assertSee('products-next', escape: false)
            ->assertSee('products-pagination', escape: false)
            ->assertSee('Popular right')
            // Real Bajaao products (same-to-same)
            ->assertSee('Squier Sonic Stratocaster Electric Guitar')
            ->assertSee('Yamaha F310 Dreadnought Acoustic Guitar')
            ->assertSee('Kala Makala MK-S Soprano Ukulele')
            ->assertSee('Focusrite Scarlett 2i2')
            ->assertSee('KRK Classic 7')
            ->assertSee('Hohner M254001S Ocean Star', escape: false)
            ->assertSee('bajaao.com/cdn/shop/files', escape: false);
    }

    public function test_bestsellers_use_real_products_with_tabs(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Squier Sonic Stratocaster')
            ->assertSee('Roland FP-30X')
            ->assertSee('Alesis Nitro Max')
            ->assertSee('Focusrite Scarlett 2i2')
            ->assertSee('role="tablist"', escape: false)
            ->assertSee('All hits')
            ->assertSee('Pro Audio')
            ->assertSee('Shop all best sellers');
    }

    public function test_why_section_uses_css_sticky_not_js_pin(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('lg:sticky lg:top-36', escape: false)
            ->assertSee('The Rhythm Exports standard')
            ->assertSee('Expertly inspected')
            ->assertSee('Complimentary setup')
            ->assertDontSee('why-media', escape: false);
    }

    public function test_scroll_to_top_button_is_present(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('id="scroll-top"', escape: false)
            ->assertSee('class="scroll-top"', escape: false)
            ->assertSee('Scroll back to top', escape: false);
    }

    public function test_footer_has_fancy_cta_no_newsletter_and_new_brand(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Ready to find')
            ->assertSee('your sound?')
            ->assertSee('Talk to an expert')
            ->assertSee('WhatsApp us')
            ->assertSee('About Rhythm Exports')
            ->assertDontSee('newsletter-email', escape: false)
            ->assertDontSee('Join the list')
            ->assertDontSee('newsletter-form', escape: false);
    }

    public function test_animated_button_system_is_used_across_sections(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('btn-gold btn-shine')
            ->assertSee('btn-ghost-light')
            ->assertSee('btn-shine');
    }

    public function test_responsive_classes_present_for_every_section_grid(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('sm:grid-cols-2 lg:grid-cols-4', escape: false)   // categories + bestsellers
            ->assertSee('grid-cols-2 gap-4 sm:gap-5 lg:grid-cols-4', escape: false) // categories
            ->assertSee('lg:grid-cols-2', escape: false)                   // new arrivals
            ->assertSee('lg:grid-cols-[0.9fr_1.1fr]', escape: false)      // why section
            ->assertSee('lg:grid-cols-[1.25fr_repeat(3,1fr)]', escape: false) // footer
            ->assertSee('supports-[height:100svh]:h-svh', escape: false); // hero mobile height
    }
}
