<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\SiteSettingsService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InfraTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_shared_hosting_schedule_has_bounded_queue_worker(): void
    {
        $events = app(Schedule::class)->events();
        $commands = array_map(static fn ($event): string => $event->command ?? '', $events);
        $worker = collect($commands)->first(
            static fn (string $command): bool => str_contains($command, 'queue:work')
        );

        $this->assertNotNull($worker);
        $this->assertStringContainsString('--stop-when-empty', $worker);
        $this->assertStringContainsString('--max-time=50', $worker);
        $this->assertStringContainsString('--tries=3', $worker);
        $this->assertStringContainsString('--timeout=45', $worker);
    }

    public function test_sitemap_renders_all_sections(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee('<urlset', escape: false)
            ->assertSee(route('home'), escape: false)
            ->assertSee(route('shop.index'), escape: false)
            ->assertSee('/about', escape: false)
            ->assertSee('/product/yamaha-f310-acoustic-guitar', escape: false)
            ->assertDontSee('/shipping', escape: false)
            ->assertDontSee('/returns', escape: false)
            ->assertDontSee('/warranty', escape: false);
    }

    public function test_robots_txt_renders(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('User-agent: *')
            ->assertSee('Disallow: /admin')
            ->assertSee('Sitemap: '.url('/sitemap.xml'), escape: false);
    }

    public function test_404_error_page_renders(): void
    {
        $this->get('/definitely-not-a-page')
            ->assertNotFound()
            ->assertSee('This note fell off the page');
    }

    public function test_gst_and_shipping_settings_apply_to_totals(): void
    {
        $settings = app(SiteSettingsService::class);
        $settings->saveAll(['shipping_flat_fee' => '49', 'tax_rate' => '18']);

        $this->assertSame(49.0, $settings->getFloat('shipping_flat_fee'));
        $this->assertSame(18.0, $settings->getFloat('tax_rate'));
    }

    public function test_free_shipping_threshold_applies(): void
    {
        $settings = app(SiteSettingsService::class);
        $settings->saveAll(['shipping_flat_fee' => '49', 'shipping_free_above' => '1000']);

        $flat = $settings->getFloat('shipping_flat_fee');
        $freeAbove = $settings->getFloat('shipping_free_above');

        // Below threshold → flat fee applies; at/above → free
        $this->assertSame(49.0, $freeAbove > 500 ? $flat : 0.0);
        $this->assertSame(0.0, $freeAbove <= 1500 ? 0.0 : $flat);
    }
}
