<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AboutContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_page_renders(): void
    {
        $this->seed();

        $this->get('/about')
            ->assertOk()
            ->assertViewIs('pages.show')
            ->assertSee('About Rhythm Exports')
            ->assertSee('Our promise');
    }

    public function test_contact_page_renders(): void
    {
        $this->seed();

        $this->get('/contact')
            ->assertOk()
            ->assertViewIs('pages.show')
            ->assertSee('Contact Rhythm Exports')
            ->assertSee('Send message');
    }

    public function test_about_page_renders_admin_managed_design_settings(): void
    {
        $this->seed();

        \App\Models\Page::query()->where('slug', 'about')->firstOrFail()->update([
            'settings' => [
                'hero_kicker' => 'Hamari kahani',
                'stats' => [
                    ['value' => '25+', 'label' => 'Years of music'],
                ],
                'promise_kicker' => 'Custom promise',
                'promise_heading' => 'Custom promise heading',
                'promise_points' => ['Only custom point'],
                'cta_label' => 'Browse gear',
                'values_heading' => 'Custom values heading',
                'values' => [
                    ['icon' => '🎸', 'title' => 'Custom value card', 'text' => 'Value card text.'],
                ],
            ],
        ]);

        $this->get('/about')
            ->assertOk()
            ->assertSee('Hamari kahani')
            ->assertSee('25+')
            ->assertSee('Years of music')
            ->assertSee('Custom promise heading')
            ->assertSee('Only custom point')
            ->assertSee('Browse gear')
            ->assertSee('Custom values heading')
            ->assertSee('Custom value card');
    }

    public function test_contact_page_renders_admin_managed_design_settings(): void
    {
        $this->seed();

        \App\Models\Page::query()->where('slug', 'contact')->firstOrFail()->update([
            'settings' => [
                'contact_kicker' => 'Baat karte hain',
                'cards' => [
                    ['icon' => '📞', 'title' => 'Custom desk', 'line1' => 'desk@example.com', 'line2' => 'Line two', 'line3' => 'Line three'],
                ],
                'whatsapp_enabled' => true,
                'whatsapp_number' => '+91 91234 56789',
                'whatsapp_title' => 'Custom WhatsApp title',
            ],
        ]);

        $this->get('/contact')
            ->assertOk()
            ->assertSee('Baat karte hain')
            ->assertSee('Custom desk')
            ->assertSee('desk@example.com')
            ->assertSee('Custom WhatsApp title')
            ->assertSee('https://wa.me/919123456789');
    }

    public function test_contact_page_hides_whatsapp_block_when_disabled_and_ignores_untrusted_map_embed(): void
    {
        $this->seed();

        \App\Models\Page::query()->where('slug', 'contact')->firstOrFail()->update([
            'settings' => [
                'whatsapp_enabled' => false,
                'map_embed_url' => 'https://evil.example.com/iframe',
            ],
        ]);

        $this->get('/contact')
            ->assertOk()
            ->assertDontSee('wa.me')
            ->assertDontSee('evil.example.com');
    }

    public function test_contact_page_renders_trusted_google_maps_embed(): void
    {
        $this->seed();

        \App\Models\Page::query()->where('slug', 'contact')->firstOrFail()->update([
            'settings' => [
                'map_embed_url' => 'https://www.google.com/maps/embed?pb=example',
            ],
        ]);

        $this->get('/contact')
            ->assertOk()
            ->assertSee('https://www.google.com/maps/embed?pb=example');
    }

    public function test_contact_form_stores_message(): void
    {
        $this->post('/contact', [
            'name' => 'Anoop Puri',
            'email' => 'anoop@example.com',
            'phone' => '9876543210',
            'subject' => 'Setup advice',
            'message' => 'I just bought my first acoustic guitar and need help with tuning stability.',
        ])->assertRedirect()
            ->assertSessionHas('contact_success');

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Anoop Puri',
            'email' => 'anoop@example.com',
            'status' => 'new',
        ]);
    }

    public function test_contact_form_validates_required_fields(): void
    {
        $this->post('/contact', [
            'name' => '',
            'email' => 'not-an-email',
            'message' => 'short',
        ])->assertSessionHasErrors(['name', 'email', 'message']);

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_contact_form_rejects_honeypot(): void
    {
        $this->post('/contact', [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'message' => 'This is a bot message that is long enough to pass.',
            'company' => 'spam',
        ])->assertSessionHasErrors('company');

        $this->assertDatabaseCount('contact_messages', 0);
    }
}
