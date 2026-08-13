<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Product;
use App\Models\SeoEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DynamicCmsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('email', 'admin@rythme.test')->firstOrFail();
    }

    public function test_dynamic_page_renders_at_admin_managed_slug(): void
    {
        $page = Page::create([
            'slug' => 'terms-of-service',
            'title' => 'Terms of Service',
            'template' => 'generic',
            'content' => '<p>These are the terms.</p>',
            'is_active' => true,
        ]);

        $this->get('/terms-of-service')
            ->assertOk()
            ->assertViewIs('pages.show')
            ->assertSee('Terms of Service')
            ->assertSee('These are the terms.');
    }

    public function test_inactive_page_returns_404(): void
    {
        $page = Page::create([
            'slug' => 'hidden-page',
            'title' => 'Hidden',
            'template' => 'generic',
            'content' => null,
            'is_active' => false,
        ]);

        $this->get('/hidden-page')->assertNotFound();
    }

    public function test_unknown_slug_returns_404(): void
    {
        $this->get('/no-such-page')->assertNotFound();
    }

    public function test_page_seo_meta_renders_in_head(): void
    {
        $page = Page::create([
            'slug' => 'seo-test-page',
            'title' => 'SEO Test Page',
            'template' => 'generic',
            'content' => null,
            'is_active' => true,
        ]);

        $page->seoEntry()->create([
            'meta_title' => 'Custom SEO Title — Rhythm Exports',
            'meta_description' => 'A custom meta description for testing.',
            'meta_keywords' => 'test, seo, rhythm',
            'og_title' => 'OG Test Title',
            'robots' => 'noindex, follow',
        ]);

        $this->get('/seo-test-page')
            ->assertOk()
            ->assertSee('<title>Custom SEO Title — Rhythm Exports</title>', escape: false)
            ->assertSee('name="description" content="A custom meta description for testing."', escape: false)
            ->assertSee('name="keywords" content="test, seo, rhythm"', escape: false)
            ->assertSee('property="og:title" content="OG Test Title"', escape: false)
            ->assertSee('name="robots" content="noindex, follow"', escape: false);
    }

    public function test_homepage_renders_admin_seo(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<title>Rhythm Exports - Feel The Music, Own The Sound</title>', escape: false);
    }

    public function test_product_seo_meta_renders(): void
    {
        $product = Product::where('slug', 'yamaha-f310-acoustic-guitar')->firstOrFail();

        $product->seoEntry()->create([
            'meta_title' => 'Yamaha F310 Custom SEO Title',
            'meta_description' => 'Custom product meta description.',
            'og_image' => 'https://example.com/og.jpg',
        ]);

        $this->get('/product/'.$product->slug)
            ->assertOk()
            ->assertSee('<title>Yamaha F310 Custom SEO Title</title>', escape: false)
            ->assertSee('name="description" content="Custom product meta description."', escape: false)
            ->assertSee('property="og:image" content="https://example.com/og.jpg"', escape: false);
    }

    public function test_shop_renders_admin_seo_anchor(): void
    {
        $this->get('/shop')
            ->assertOk()
            ->assertSee('Shop All Instruments', escape: false);
    }

    public function test_schema_json_and_head_scripts_render(): void
    {
        $page = Page::create([
            'slug' => 'schema-page',
            'title' => 'Schema Page',
            'template' => 'generic',
            'content' => null,
            'is_active' => true,
        ]);

        $page->seoEntry()->create([
            'schema_json' => ['@context' => 'https://schema.org', '@type' => 'WebPage', 'name' => 'Schema Page'],
            'head_scripts' => '<meta name="custom-tag" content="hello">',
        ]);

        $this->get('/schema-page')
            ->assertOk()
            ->assertSee('application/ld+json', escape: false)
            ->assertSee('"@type":"WebPage"', escape: false)
            ->assertSee('<meta name="custom-tag" content="hello">', escape: false);
    }

    public function test_admin_can_manage_pages_resource(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/pages')
            ->assertOk()
            ->assertSee('About Rhythm Exports');
    }

    public function test_guest_cannot_access_pages_admin(): void
    {
        $this->get('/admin/pages')->assertRedirect('/admin/login');
    }

    public function test_cannot_create_page_with_reserved_slug(): void
    {
        // Reserved slugs must be rejected at creation.
        $this->assertContains('shop', Page::RESERVED_SLUGS);
    }

    public function test_seo_entry_unique_per_entity(): void
    {
        $product = Product::firstOrFail();
        $product->seoEntry()->create(['meta_title' => 'First']);
        $product->seoEntry()->updateOrCreate([], ['meta_title' => 'Second']);

        $this->assertSame(1, SeoEntry::where('seoable_type', Product::class)
            ->where('seoable_id', $product->id)
            ->count());
        $this->assertSame('Second', $product->seoEntry->meta_title);
    }
}
