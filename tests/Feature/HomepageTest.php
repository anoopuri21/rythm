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
    public function test_homepage_renders_all_completed_sections(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertViewIs('home.index')
            ->assertViewHas('heroMode', 'slider')
            ->assertSeeInOrder([
                'id="hero"',
                'id="categories"',
                'id="bestsellers"',
                'id="why-rythme"',
                'id="brands"',
                'id="numbers"',
                'id="new-arrivals"',
                'id="deals"',
                'id="video-showcase"',
                'id="stories"',
                'id="testimonials"',
                'id="comparison"',
                'id="ugc"',
                'id="faq"',
                'id="footer"',
            ], escape: false)
            ->assertSee('"@type": "FAQPage"', escape: false)
            ->assertSee('#RhythmExportsFamily');
    }

    public function test_homepage_brand_is_rhythm_exports_everywhere(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Rhythm Exports')
            ->assertSee('RHYTHM')
            ->assertDontSee('Rythme Music Store')
            ->assertDontSee('RYTHME');
    }

    public function test_homepage_contains_primary_navigation_and_calls_to_action(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Explore instruments')
            ->assertSee('Shop the sale')
            ->assertSee('Read all stories')
            ->assertSee('Loved by players,');
    }
}
