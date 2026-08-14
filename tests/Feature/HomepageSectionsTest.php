<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Per-section UI test cases — each homepage section is tested for its
 * structure, key content, real product imagery and responsive classes.
 */
class HomepageSectionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_navbar_has_logo_search_icons_and_two_rows(): void
    {
        $this->get('/')
            ->assertOk()
            // White, sticky (in flow — NOT over hero), full width + 30px padding
            ->assertSee('id="navbar" class="sticky top-0 z-50 w-full border-b border-black/5 bg-white', escape: false)
            ->assertSee('px-[30px]', escape: false)
            ->assertDontSee('navbar-transparent', escape: false)
            // Row 1: logo + big search + icons
            ->assertSee('rhythmexports.com/wp-content/uploads/2023/10/Rhythm.png', escape: false)
            ->assertSee('id="nav-search"', escape: false)
            ->assertSee('aria-label="Wishlist"', escape: false)
            ->assertSee('aria-label="Open cart"', escape: false)
            ->assertSee('cart-drawer-toggle', escape: false)
            ->assertSee('aria-label="Account"', escape: false)
            // Row 2: main categories (user-specified taxonomy, Other/Deals removed)
            ->assertSee('>Guitars<', escape: false)
            ->assertSee('>Ukuleles &amp; Violins<', escape: false)
            ->assertSee('>Keyboards &amp; Pianos<', escape: false)
            ->assertSee('>Studio &amp; Recording<', escape: false)
            ->assertSee('>Drums &amp; Percussion<', escape: false)
            ->assertSee('>Software &amp; Plugins<', escape: false)
            ->assertSee('>More<', escape: false)
            ->assertDontSee('/category/other', escape: false)
            ->assertDontSee('/category/deals', escape: false)
            // sub-category dropdowns (no svg in sub-menu lists) + mobile drawer
            ->assertSee('aria-haspopup="true"', escape: false)
            ->assertSee('>Violins<', escape: false)
            ->assertSee('>MIDI Controllers<', escape: false)
            ->assertSee('>Plugins &amp; Effects<', escape: false)
            ->assertSee('class="block rounded-lg px-2.5 py-2 text-sm', escape: false) // sub-menu child (no svg)
            ->assertSee('id="mobile-menu"', escape: false)
            ->assertSee('id="nav-search-mobile"', escape: false);
    }

    public function test_hero_slider_mode_renders_swiper_with_animated_buttons(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('hero-swiper swiper', escape: false)
            ->assertSee('hero-slide-image', escape: false)
            ->assertSee('rounded-full bg-white px-7 py-4', escape: false)
            ->assertSee('hero-pagination', escape: false);
    }

    public function test_hero_slider_has_two_high_quality_product_slides_first(): void
    {
        $response = $this->get('/');

        $response->assertOk();

        // Two new "High quality" slides at the START (Bajaao real product photos)
        $html = $response->getContent();
        $this->assertStringContainsString('High quality · Best sellers', $html);
        $this->assertStringContainsString('Premium gear.', $html);
        $this->assertStringContainsString('Play the piano.', $html);

        // They must appear BEFORE the original first slide (Feel the music.)
        $this->assertGreaterThan(
            strpos($html, 'High quality · Best sellers'),
            strpos($html, 'Feel the music.')
        );

        // Uses Bajaao real product imagery
        $this->assertStringContainsString('FEN-0373152506.jpg', $html);
        $this->assertStringContainsString('ROL-FP30XBK.jpg', $html);
    }

    public function test_hero_video_mode_renders_video_banner_instead_of_slider(): void
    {
        config(['rythme.hero_mode' => 'video']);

        $this->get('/')
            ->assertOk()
            ->assertSee('<video', escape: false)
            ->assertSee('autoplay muted loop playsinline', escape: false)
            ->assertSee('videos/hero-montage.mp4', escape: false)
            ->assertSee(config('rythme.hero_video_url'), escape: false)
            ->assertDontSee('hero-swiper swiper', escape: false)
            ->assertSee('class="btn-gold btn-shine"', escape: false);
    }

    public function test_categories_section_is_pinned_horizontal_scroll_with_bajaao_images(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Explore by category')
            ->assertSee('Find your')
            ->assertSee('aria-label="Guitars — 480+ instruments"', escape: false)
            ->assertSee('bajaao.com/cdn/shop/files', escape: false)
            ->assertSee('class="pin relative', escape: false)
            ->assertSee('id="cat-track"', escape: false)
            ->assertSee('class="gcard"', escape: false)
            ->assertSee('id="pin-progress"', escape: false)
            ->assertSee('class="pin__hint"', escape: false);
    }

    public function test_instrument_decor_background_shapes_present(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('instr-decor', escape: false)
            ->assertSee('instr instr-1', escape: false)
            ->assertSee('instr instr-5', escape: false)
            ->assertSee('instr instr-8', escape: false);
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
            // Real DB trending products
            ->assertSee('Yamaha F310 Acoustic Guitar')
            ->assertSee('Squier Affinity Stratocaster HSS')
            ->assertSee('Roland FP-30X Digital Piano')
            ->assertSee('Shure SM58 Vocal Microphone')
            ->assertSee('bajaao.com/cdn/shop/files', escape: false);
    }

    public function test_bestsellers_use_real_products_with_tabs(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Yamaha F310 Acoustic Guitar')
            ->assertSee('Squier Affinity Stratocaster HSS')
            ->assertSee('Roland FP-30X Digital Piano')
            ->assertSee('Shure SM58 Vocal Microphone')
            ->assertSee('role="tablist"', escape: false)
            ->assertSee('>All<', escape: false)
            ->assertSee('Pro Audio')
            ->assertSee('Shop all best sellers');
    }

    public function test_why_section_uses_css_sticky_not_js_pin(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('lg:sticky lg:top-32', escape: false)
            ->assertSee('The Rythme standard')
            ->assertSee('Free expert setup')
            ->assertSee('Easy returns')
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
            ->assertSee('Top brands')
            ->assertSee('Customer care')
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
            ->assertSee('sm:grid-cols-2 lg:grid-cols-4', escape: false)   // bestsellers
            ->assertSee('class="pin__track"', escape: false)              // categories (pinned horizontal scroll)
            ->assertSee('id="new-arrivals"', escape: false)             // new arrivals
            ->assertSee('lg:grid-cols-[0.9fr_1.1fr]', escape: false)      // why section
            ->assertSee('lg:grid-cols-5', escape: false) // footer 5-column
            ->assertSee('h-[calc(100svh-4rem)]', escape: false)         // hero = 100vh - navbar (mobile)
            ->assertSee('lg:h-[calc(100svh-7.5rem)]', escape: false);  // hero = 100vh - navbar (desktop)
    }
}
