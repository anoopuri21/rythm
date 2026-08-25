<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Per-section UI test cases — matches the MAIN (arena) homepage design:
 * mega-market layout (hero-mm, cat-mm, prod-mm, promo-mm, adv-mm, deal-mm,
 * catban-mm, launch-mm, brand-mm) with token-backed Rythme styling.
 */
class HomepageSectionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_navbar_has_logo_search_icons_and_menu(): void
    {
        $this->get('/')
            ->assertOk()
            // Sticky white navbar with menu + icons
            ->assertSee('id="navbar"', escape: false)
            ->assertSee('nav__menu', escape: false)
            ->assertSee('nav__burger', escape: false)
            ->assertSee('id="mobile-menu"', escape: false)
            // Logo + search
            ->assertSee('rhythmexports.com/wp-content/uploads/2023/10/Rhythm.png', escape: false)
            ->assertSee('id="nav-search"', escape: false)
            // Nav links
            ->assertSee('>Home<', escape: false)
            ->assertSee('>Shop<', escape: false)
            ->assertSee('>Deals<', escape: false)
            ->assertSee('>Our Story<', escape: false)
            ->assertSee('>Contact<', escape: false)
            // Icons
            ->assertSee('aria-label="Wishlist"', escape: false)
            ->assertSee('aria-label="Open cart"', escape: false)
            ->assertSee('aria-label="Account"', escape: false);
    }

    public function test_hero_slider_renders_with_pagination_and_cta(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('hero-mm', escape: false)
            ->assertSee('hero-swiper swiper', escape: false)
            ->assertSee('hero-slide-image', escape: false)
            ->assertSee('hero-pagination', escape: false)
            ->assertSee('hero-mm__cta', escape: false)
            ->assertSee('aria-label="Pause featured collections"', escape: false);
    }

    public function test_hero_has_admin_driven_slides(): void
    {
        $response = $this->get('/');
        $response->assertOk();

        $html = $response->getContent();

        // DB-seeded slides (hero_slides)
        $this->assertStringContainsString('Premium gear.', $html);
        $this->assertStringContainsString('Play the piano.', $html);
        $this->assertStringContainsString('Feel the music.', $html);
    }

    public function test_usp_strip_renders_without_unapproved_commerce_claims(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('class="usp-strip"', escape: false)
            ->assertSee('Instrument-first', escape: false)
            ->assertDontSee('Free express', escape: false)
            ->assertDontSee('7-day returns', escape: false)
            ->assertDontSee('Easy EMI', escape: false);
    }

    public function test_categories_carousel_renders_with_db_categories(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Popular Categories')
            ->assertSee('cat-swiper swiper', escape: false)
            ->assertSee('cat-card', escape: false)
            ->assertSee('Guitars', escape: false)
            ->assertSee('images/categories/guitars.jpg', escape: false)
            ->assertSee('/shop?category=guitars', escape: false);
    }

    public function test_new_arrivals_grid_renders(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('id="new-arrivals"', escape: false)
            ->assertSee('prod-mm', escape: false)
            ->assertSee('New Arrival Products');
    }

    public function test_promo_banners_section_renders(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('promo-mm', escape: false)
            ->assertSee('Shop now', escape: false);
    }

    public function test_advantages_section_renders(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('adv-mm', escape: false);
    }

    public function test_deals_section_uses_real_stock_without_synthetic_sales_or_deadline(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('deal-mm', escape: false)
            ->assertSee('Available now', escape: false)
            ->assertDontSee('Sold:', escape: false)
            ->assertDontSee('data-deal-timer', escape: false)
            ->assertSee('View product', escape: false);
    }

    public function test_category_banners_section_renders(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('catban-mm', escape: false)
            ->assertSee('Shop now', escape: false);
    }

    public function test_recently_launched_section_renders(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('launch-mm', escape: false)
            ->assertSee('Just landed')
            ->assertSee('View all products');
    }

    public function test_brands_section_renders(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('brand-mm', escape: false);
    }

    public function test_homepage_has_self_canonical_and_index_policy(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('home').'">', escape: false)
            ->assertSee('<meta name="robots" content="index, follow">', escape: false);
    }

    public function test_footer_renders(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('id="footer"', escape: false)
            ->assertSee('footer-shop', escape: false)
            ->assertSee('footer-brands', escape: false);
    }

    public function test_scroll_to_top_button_is_present(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('id="scroll-top"', escape: false)
            ->assertSee('Scroll back to top', escape: false);
    }

    public function test_responsive_classes_present(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('hero-mm', escape: false)
            ->assertSee('cat-swiper', escape: false)
            ->assertSee('prod-mm__grid', escape: false)
            ->assertSee('deal-mm__grid', escape: false);
    }
}
