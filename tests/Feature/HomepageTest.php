<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_homepage_renders_all_sections(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertViewIs('home.index')
            ->assertViewHas('heroMode', 'slider')
            // Main (arena) section order + classes
            ->assertSeeInOrder([
                'id="hero"',
                'class="usp-strip"',
                'id="categories"',
                'id="new-arrivals"',
                'class="promo-mm"',
                'class="adv-mm"',
                'class="deal-mm"',
                'class="catban-mm"',
                'class="launch-mm"',
                'class="brand-mm"',
                'id="footer"',
            ], escape: false)
            // Key section headings
            ->assertSee('Popular Categories')
            ->assertSee('New Arrival Products')
            ->assertSee('Just landed');
    }

    public function test_homepage_brand_is_rhythm_exports_everywhere(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Rhythm Exports')
            ->assertSee('RHYTHM')
            ->assertDontSee('Rythme Music Store');
    }

    public function test_homepage_contains_primary_navigation_and_calls_to_action(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Explore instruments')
            ->assertSee('Shop now')
            ->assertSee('View all products')
            ->assertSee('Just landed');
    }
}
