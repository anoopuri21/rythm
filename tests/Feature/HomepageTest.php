<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomepageTest extends TestCase
{
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
            ->assertSee('#RythmeFamily');
    }

    public function test_homepage_contains_primary_navigation_and_calls_to_action(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('RYTHME')
            ->assertSee('Explore instruments')
            ->assertSee('Shop the sale')
            ->assertSee('Read all stories')
            ->assertSee('Made for musicians.')
            ->assertSee('Join the list');
    }
}
