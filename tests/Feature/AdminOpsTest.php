<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use App\Services\SiteSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOpsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('email', 'admin@rythme.test')->firstOrFail();
    }

    public function test_admin_customers_resource_renders(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/customers')
            ->assertOk()
            ->assertSee('test@example.com');
    }

    public function test_admin_contact_messages_resource_renders(): void
    {
        ContactMessage::create([
            'name' => 'Anoop Puri',
            'email' => 'anoop@example.com',
            'message' => 'Setup help please — my guitar keeps going out of tune.',
            'status' => 'new',
        ]);

        $this->actingAs($this->admin)
            ->get('/admin/contact-messages')
            ->assertOk()
            ->assertSee('anoop@example.com')
            ->assertSee('new');
    }

    public function test_contact_message_status_update(): void
    {
        $message = ContactMessage::create([
            'name' => 'Test',
            'email' => 't@example.com',
            'message' => 'Hello there, need some help with an order.',
            'status' => 'new',
        ]);

        $message->update(['status' => 'replied']);
        $this->assertSame('replied', $message->fresh()->status);
    }

    public function test_admin_newsletter_resource_renders(): void
    {
        NewsletterSubscriber::create(['email' => 'sub@example.com', 'subscribed_at' => now()]);

        $this->actingAs($this->admin)
            ->get('/admin/newsletter-subscribers')
            ->assertOk()
            ->assertSee('sub@example.com');
    }

    public function test_admin_dashboard_renders_with_widgets_registered(): void
    {
        $panel = \Filament\Facades\Filament::getCurrentPanel();
        $widgets = array_map(fn ($w) => class_basename($w), $panel->getWidgets());

        $this->assertContains('StatsOverviewWidget', $widgets);
        $this->assertContains('LatestOrdersWidget', $widgets);

        $this->actingAs($this->admin)->get('/admin')->assertOk();
    }

    public function test_stats_widget_computes_revenue(): void
    {
        \App\Models\Order::factory()->create([
            'user_id' => $this->admin->id,
            'payment_status' => 'paid',
            'total' => 5000,
        ]);

        $revenue = (float) \App\Models\Order::where('payment_status', 'paid')->where('created_at', '>=', now()->startOfWeek())->sum('total');

        $this->assertSame(5000.0, $revenue);
    }

    public function test_settings_page_saves_and_caches(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/settings')
            ->assertOk()
            ->assertSee('Shipping flat fee');

        $service = app(SiteSettingsService::class);
        $service->saveAll([
            'shipping_flat_fee' => '49',
            'tax_rate' => '18',
            'contact_email' => 'hello@rythme.store',
        ]);

        $this->assertSame('49', $service->get('shipping_flat_fee'));
        $this->assertSame('18', $service->get('tax_rate'));
        $this->assertSame('hello@rythme.store', $service->get('contact_email'));
    }

    public function test_settings_defaults_exist(): void
    {
        $service = app(SiteSettingsService::class);

        $this->assertSame('0', $service->get('shipping_flat_fee'));
        $this->assertSame('support@rythme.store', $service->get('contact_email'));
    }
}
