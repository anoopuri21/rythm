<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * C5 / W4: no fabricated contact details or recent-purchase social proof on storefront.
 */
class DemoContentPurgeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_contact_seed_has_no_fake_phone_or_email_cards(): void
    {
        $page = Page::query()->where('slug', 'contact')->firstOrFail();
        $settings = $page->settings ?? [];

        $this->assertSame([], $settings['cards'] ?? []);
        $this->assertFalse((bool) ($settings['whatsapp_enabled'] ?? false));
        $this->assertSame('', (string) ($settings['whatsapp_number'] ?? ''));
    }

    public function test_contact_page_does_not_render_demo_phone_or_showroom(): void
    {
        $this->get('/contact')
            ->assertOk()
            ->assertDontSee('98765 43210', false)
            ->assertDontSee('support@rythme.store', false)
            ->assertDontSee('Karol Bagh', false)
            ->assertDontSee('partners@rythme.store', false)
            ->assertSee('Contact details coming soon', false)
            ->assertSee('Send message', false);
    }

    public function test_layout_does_not_include_recent_purchase_demo(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('data-recent-purchase-demo', false)
            ->assertDontSee('Fender Player Stratocaster', false)
            ->assertDontSee('Demo user:', false);
    }

    public function test_database_seeder_source_guards_production(): void
    {
        $source = file_get_contents(base_path('database/seeders/DatabaseSeeder.php'));

        $this->assertNotFalse($source);
        $this->assertStringContainsString("environment('production')", $source);
        $this->assertStringContainsString('must never be seeded in production', $source);
    }
}
