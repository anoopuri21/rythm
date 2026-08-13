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
        $this->get('/about')
            ->assertOk()
            ->assertViewIs('about.index')
            ->assertSee('Music first')
            ->assertSee('Why Rythme')
            ->assertSee('Our promise');
    }

    public function test_contact_page_renders(): void
    {
        $this->get('/contact')
            ->assertOk()
            ->assertViewIs('contact.index')
            ->assertSee('Talk to a real musician')
            ->assertSee('Send message');
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
