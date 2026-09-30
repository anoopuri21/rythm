<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Services\SiteSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Admin-controlled brand marks (Admin → Settings → Brand logo & marks).
 *
 * Contract: every mark falls back to the shipped asset when the admin has not
 * uploaded one, so the storefront never renders a missing image and nothing
 * about the current look changes until a mark is deliberately replaced.
 */
class BrandLogoSettingsTest extends TestCase
{
    use RefreshDatabase;

    private SiteSettingsService $settings;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->settings = app(SiteSettingsService::class);
    }

    public function test_storefront_keeps_the_shipped_marks_when_nothing_is_uploaded(): void
    {
        $this->get('/')
            ->assertOk()
            // Navbar + drawer still point at the configured default…
            ->assertSee('rhythmexports.com/wp-content/uploads/2023/10/Rhythm.png', escape: false)
            // …the footer keeps whitening it with the CSS tint…
            ->assertSee('brightness-0 invert', escape: false)
            // …and the favicon is deliberately NOT admin-controlled, so the
            // bundled <link rel="icon"> must stay exactly as it was.
            ->assertSee('type="image/png" sizes="128x128"', escape: false)
            ->assertSee('/favicon.png', escape: false);
    }

    public function test_uploaded_regular_logo_replaces_the_navbar_mark(): void
    {
        $this->settings->saveAll(['logo_regular' => 'branding/acme.png']);

        $this->get('/')
            ->assertOk()
            ->assertSee('/storage/branding/acme.png', escape: false)
            ->assertDontSee('rhythmexports.com/wp-content/uploads', escape: false);
    }

    public function test_uploaded_white_logo_is_used_in_the_footer_without_the_css_tint(): void
    {
        $this->settings->saveAll(['logo_white' => 'branding/acme-white.png']);

        $this->get('/')
            ->assertOk()
            ->assertSee('/storage/branding/acme-white.png', escape: false)
            // A real white mark must not also be inverted back to black.
            ->assertDontSee('brightness-0 invert', escape: false);
    }

    public function test_footer_keeps_the_tint_when_only_the_regular_logo_is_uploaded(): void
    {
        $this->settings->saveAll(['logo_regular' => 'branding/acme.png']);

        $this->get('/')
            ->assertOk()
            ->assertSee('brightness-0 invert', escape: false);
    }

    public function test_default_og_image_comes_from_settings(): void
    {
        $this->settings->saveAll(['logo_og' => 'branding/share.png']);

        $this->get('/')
            ->assertOk()
            ->assertSee('property="og:image"', escape: false)
            ->assertSee('/storage/branding/share.png', escape: false);
    }

    public function test_marks_resolve_to_absolute_urls_and_fall_back_when_unset(): void
    {
        $this->assertNull($this->settings->logoWhiteUrl());
        $this->assertNull($this->settings->ogImageUrl());
        $this->assertSame((string) config('rythme.logo_url'), $this->settings->logoUrl());

        $this->settings->saveAll(['logo_og' => 'branding/share.png']);

        $this->assertStringEndsWith('/storage/branding/share.png', (string) $this->settings->ogImageUrl());
    }

    public function test_an_absolute_url_is_passed_through_untouched(): void
    {
        $this->settings->saveAll(['logo_regular' => 'https://cdn.example.com/logo.png']);

        $this->assertSame('https://cdn.example.com/logo.png', $this->settings->logoUrl());
    }

    public function test_clearing_a_mark_restores_the_fallback(): void
    {
        $this->settings->saveAll(['logo_regular' => 'branding/acme.png']);
        $this->assertStringEndsWith('/storage/branding/acme.png', $this->settings->logoUrl());

        // saveAll() deletes empty values, which must fall back to config.
        $this->settings->saveAll(['logo_regular' => '']);

        $this->assertSame((string) config('rythme.logo_url'), $this->settings->logoUrl());
    }

    public function test_admin_settings_page_offers_the_brand_uploads(): void
    {
        $admin = User::where('email', 'admin@rythme.test')->firstOrFail();

        $this->actingAsAdmin($admin)
            ->get('/admin/settings')
            ->assertOk()
            ->assertSee('Brand logo')
            ->assertSee('White / inverse logo');
    }
}
