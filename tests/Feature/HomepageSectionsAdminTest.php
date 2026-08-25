<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\HomepageSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageSectionsAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('email', 'admin@rythme.test')->firstOrFail();
    }

    public function test_seeder_creates_all_13_sections(): void
    {
        $this->assertSame(13, HomepageSection::count());

        foreach (['categories', 'bestsellers', 'why-rythme', 'brands', 'numbers', 'new-arrivals', 'deals', 'video-showcase', 'stories', 'testimonials', 'comparison', 'ugc', 'faq'] as $key) {
            $this->assertDatabaseHas('homepage_sections', ['section_key' => $key]);
        }
    }

    public function test_admin_can_access_homepage_sections_resource(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/homepage-sections')
            ->assertOk()
            ->assertSee('deals');
    }

    public function test_guest_cannot_access_admin(): void
    {
        $this->get('/admin/homepage-sections')->assertRedirect('/admin/login');
    }

    public function test_homepage_renders_db_driven_title(): void
    {
        // Main homepage: _deals renders $sec->title (admin-driven).
        $deals = HomepageSection::where('section_key', 'deals')->firstOrFail();
        $deals->update([
            'title' => 'Custom sale',
            'title_accent' => 'heading.',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Custom sale')
            ->assertSee('heading.');
    }

    public function test_inactive_section_falls_back_to_default_copy(): void
    {
        $deals = HomepageSection::where('section_key', 'deals')->firstOrFail();
        $deals->update(['is_active' => false]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Deals Of The Day');
    }

    public function test_cache_flushes_when_section_updated(): void
    {
        $this->get('/')->assertOk();

        // categories section is rendered on main homepage — update + verify flush.
        $categories = HomepageSection::where('section_key', 'categories')->firstOrFail();
        $categories->update(['title' => 'Fresh Categories Title']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Fresh Categories Title');
    }
}
