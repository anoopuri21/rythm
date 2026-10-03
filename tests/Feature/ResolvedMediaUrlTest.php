<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Models\Brand;
use App\Models\Category;
use App\Models\HeroSlide;
use App\Models\HomepageBlock;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Observers\HomepageDataObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\Concerns\IsolatesMediaDisks;
use Tests\TestCase;

/**
 * The persisted media-URL columns (docs/media-architecture.md → M-7).
 *
 * Contract: Media Library stays the writer of truth, the columns are a cache of
 * the resolved URLs, reads are column-first with a Media Library fallback, and
 * App\Observers\MediaUrlObserver keeps them fresh through upload, delete,
 * reorder and conversion completion.
 */
class ResolvedMediaUrlTest extends TestCase
{
    use IsolatesMediaDisks;
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->isolateMediaDisks();
        $this->admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    }

    // ── Upload writes the columns ───────────────────────────────────────────

    public function test_admin_upload_persists_the_resolved_url_columns(): void
    {
        Queue::fake();

        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        Livewire::actingAs($this->admin, 'admin')
            ->test(CreateProduct::class)
            ->fillForm([
                'name' => 'Column Guitar',
                'slug' => 'column-guitar',
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'price' => 1500,
                'stock' => 4,
                'is_active' => true,
                'gallery' => [
                    UploadedFile::fake()->image('front.jpg', 600, 600),
                    UploadedFile::fake()->image('back.jpg', 600, 600),
                ],
                'og' => [UploadedFile::fake()->image('share.jpg', 1200, 630)],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::query()->where('slug', 'column-guitar')->firstOrFail();

        $this->assertCount(2, $product->gallery_urls);
        $this->assertNotNull($product->thumbnail_url);
        $this->assertNotNull($product->og_image_url);

        foreach ([...$product->gallery_urls, $product->thumbnail_url, $product->og_image_url] as $url) {
            $this->assertStringStartsWith('/storage/', (string) $url, 'Stored URLs must stay host-relative (M-2).');
            $this->assertStringNotContainsString('signature=', (string) $url);
        }

        // The stored values are exactly what the collections resolve to.
        $this->assertSame($product->resolvedMediaUrls(), [
            'gallery_urls' => $product->gallery_urls,
            'thumbnail_url' => $product->thumbnail_url,
            'og_image_url' => $product->og_image_url,
        ]);
    }

    // ── Reads come from the columns ─────────────────────────────────────────

    public function test_accessors_read_the_stored_columns_without_touching_media(): void
    {
        $product = Product::factory()->create();

        // A sentinel only present in the column: if any accessor resolved
        // through Media Library it could never return these strings.
        $product->forceFill([
            'gallery_urls' => ['/storage/sentinel/hero.webp', '/storage/sentinel/second.webp'],
            'thumbnail_url' => '/storage/sentinel/thumb.webp',
            'og_image_url' => '/storage/sentinel/og.webp',
        ])->saveQuietly();

        $fresh = Product::query()->findOrFail($product->id);

        $this->assertSame('/storage/sentinel/hero.webp', $fresh->heroImage());
        $this->assertSame('/storage/sentinel/thumb.webp', $fresh->thumbnailImage());
        $this->assertSame('/storage/sentinel/og.webp', $fresh->ogImage());
        $this->assertSame(['/storage/sentinel/hero.webp', '/storage/sentinel/second.webp'], $fresh->galleryImages());
    }

    public function test_rows_without_columns_fall_back_to_media_library(): void
    {
        Queue::fake();

        $product = Product::factory()->create();
        $media = $product->addMedia(UploadedFile::fake()->image('legacy.jpg', 80, 80))->toMediaCollection('gallery');

        // Simulate a pre-migration row: media exists, columns never resolved.
        Product::query()->whereKey($product->id)->update([
            'gallery_urls' => null,
            'thumbnail_url' => null,
            'og_image_url' => null,
        ]);

        $legacy = Product::query()->findOrFail($product->id);

        $this->assertSame($media->getUrl(), $legacy->thumbnailImage());
        $this->assertSame([$media->getUrl()], $legacy->galleryImages());
    }

    // ── Observer keeps them fresh ───────────────────────────────────────────

    public function test_deleting_a_gallery_image_updates_the_columns(): void
    {
        Queue::fake();

        $product = Product::factory()->create();
        $product->addMedia(UploadedFile::fake()->image('one.jpg', 80, 80))->toMediaCollection('gallery');
        $second = $product->addMedia(UploadedFile::fake()->image('two.jpg', 80, 80))->toMediaCollection('gallery');

        $this->assertCount(2, $product->fresh()->gallery_urls);

        $second->delete();

        $fresh = $product->fresh();
        $this->assertCount(1, $fresh->gallery_urls);
        $this->assertSame($fresh->gallery_urls[0], $fresh->heroImage());
    }

    public function test_reordering_gallery_images_changes_the_primary_image(): void
    {
        Queue::fake();

        $product = Product::factory()->create();
        $first = $product->addMedia(UploadedFile::fake()->image('one.jpg', 80, 80))->toMediaCollection('gallery');
        $second = $product->addMedia(UploadedFile::fake()->image('two.jpg', 80, 80))->toMediaCollection('gallery');

        $this->assertSame($first->getUrl(), $product->fresh()->heroImage());

        // Exactly what the panel's drag-to-reorder handler does.
        Media::setNewOrder([$second->id, $first->id]);

        $fresh = $product->fresh();
        $this->assertSame($second->getUrl(), $fresh->heroImage(), 'The first gallery item is the card/hero image.');
        $this->assertSame([$second->getUrl(), $first->getUrl()], $fresh->gallery_urls);
    }

    public function test_conversion_completion_upgrades_the_stored_urls_to_webp(): void
    {
        Queue::fake();

        $product = Product::factory()->create();
        $media = $product->addMedia(UploadedFile::fake()->image('amp.jpg', 80, 80))->toMediaCollection('gallery');

        $this->assertSame($media->getUrl(), $product->fresh()->thumbnail_url, 'Original is stored until the conversion exists.');

        $media->markAsConversionGenerated('thumb-webp');
        $media->markAsConversionGenerated('gallery-webp');
        $media->save();

        $fresh = $product->fresh();
        $this->assertSame($media->getUrl('thumb-webp'), $fresh->thumbnail_url);
        $this->assertSame($media->getUrl('gallery-webp'), $fresh->heroImage());
    }

    public function test_syncing_url_columns_does_not_touch_content_timestamps(): void
    {
        Queue::fake();

        $product = Product::factory()->create();
        $updatedAt = $product->updated_at;

        $this->travel(5)->minutes();
        $product->addMedia(UploadedFile::fake()->image('photo.jpg', 80, 80))->toMediaCollection('gallery');

        $this->assertTrue(
            $product->fresh()->updated_at->equalTo($updatedAt),
            'A resolved-URL refresh must not look like a content edit (Trending order uses updated_at).',
        );
    }

    public function test_media_changes_drop_the_cached_homepage_payload(): void
    {
        Queue::fake();

        $product = Product::factory()->create();

        Cache::put(HomepageDataObserver::CACHE_KEY, ['stale' => true], 3600);
        $product->addMedia(UploadedFile::fake()->image('new.jpg', 80, 80))->toMediaCollection('gallery');

        $this->assertNull(
            Cache::get(HomepageDataObserver::CACHE_KEY),
            'A media change moves a rendered URL, so the cached homepage payload must be dropped.',
        );
    }

    // ── Variants and the other media resources ──────────────────────────────

    public function test_variant_images_are_persisted_in_their_own_columns(): void
    {
        Queue::fake();

        $variant = ProductVariant::factory()->create();
        $media = $variant->addMedia(UploadedFile::fake()->image('sunburst.jpg', 80, 80))
            ->toMediaCollection('variant_gallery');

        $fresh = $variant->fresh();
        $this->assertSame([$media->getUrl()], $fresh->gallery_urls);
        $this->assertSame($media->getUrl(), $fresh->thumbnail_url);
        $this->assertSame([$media->getUrl()], $fresh->galleryUrls());

        $media->markAsConversionGenerated('variant-gallery-webp');
        $media->markAsConversionGenerated('variant-thumb-webp');
        $media->save();

        $fresh = $variant->fresh();
        $this->assertSame([$media->getUrl('variant-gallery-webp')], $fresh->gallery_urls);
        $this->assertSame($media->getUrl('variant-thumb-webp'), $fresh->thumbnail_url);
    }

    public function test_brand_category_hero_and_homepage_block_columns_are_persisted(): void
    {
        Queue::fake();

        $brand = Brand::factory()->create();
        $brand->addMedia(UploadedFile::fake()->image('logo.png', 60, 60))->toMediaCollection('logo');
        $this->assertNotNull($brand->fresh()->logo_url);
        $this->assertSame($brand->fresh()->logo_url, $brand->fresh()->logoUrl());

        $category = Category::factory()->create();
        $category->addMedia(UploadedFile::fake()->image('icon.png', 60, 60))->toMediaCollection('icon');
        $this->assertNotNull($category->fresh()->icon_url);

        $slide = HeroSlide::query()->create(['title' => 'Column hero slide', 'is_active' => true]);
        $slide->addMedia(UploadedFile::fake()->image('desktop.jpg', 120, 80))->toMediaCollection('desktop_image');
        $slide->addMedia(UploadedFile::fake()->image('mobile.jpg', 80, 120))->toMediaCollection('mobile_image');
        $freshSlide = $slide->fresh();
        $this->assertNotNull($freshSlide->desktop_image_url);
        $this->assertNotNull($freshSlide->mobile_image_url);
        $this->assertSame($freshSlide->desktop_image_url, $freshSlide->desktopImageUrl());

        $block = HomepageBlock::query()->create([
            'section_key' => 'promo',
            'title' => 'Column promo',
            'is_active' => true,
        ]);
        $block->addMedia(UploadedFile::fake()->image('promo.jpg', 120, 60))->toMediaCollection('image');
        $this->assertNotNull($block->fresh()->image_url);
    }

    // ── Backfill command ────────────────────────────────────────────────────

    public function test_sync_command_backfills_rows_created_before_the_columns_existed(): void
    {
        Queue::fake();

        $product = Product::factory()->create();
        $media = $product->addMedia(UploadedFile::fake()->image('old.jpg', 80, 80))->toMediaCollection('gallery');

        // Pre-migration shape: media present, columns never resolved.
        Product::query()->whereKey($product->id)->update([
            'gallery_urls' => null,
            'thumbnail_url' => null,
            'og_image_url' => null,
        ]);

        $this->artisan('media:sync-urls --dry-run')->assertSuccessful();
        $this->assertNull($product->fresh()->gallery_urls, 'Dry run must not write.');

        Cache::put(HomepageDataObserver::CACHE_KEY, ['stale' => true], 3600);

        $this->artisan('media:sync-urls')->assertSuccessful();

        $this->assertNull(
            Cache::get(HomepageDataObserver::CACHE_KEY),
            'Backfilling URL columns must flush the cached homepage payload because saveQuietly() bypasses observers.',
        );

        $fresh = $product->fresh();
        $this->assertSame([$media->getUrl()], $fresh->gallery_urls);
        $this->assertSame($media->getUrl(), $fresh->thumbnail_url);

        // Idempotent: a second run is a no-op.
        $this->assertSame([], $fresh->resolvedMediaUrlChanges());
    }

    public function test_sync_command_only_missing_skips_fully_resolved_rows(): void
    {
        Queue::fake();

        $product = Product::factory()->create();
        $product->addMedia(UploadedFile::fake()->image('done.jpg', 80, 80))->toMediaCollection('gallery');
        $product->addMedia(UploadedFile::fake()->image('share.jpg', 400, 400))->toMediaCollection('og');

        // Nothing is NULL anymore → only-missing must not even load it.
        $this->assertSame([], $product->fresh()->resolvedMediaUrlChanges());

        $this->artisan('media:sync-urls --only-missing')->assertSuccessful();
        $this->assertSame([], $product->fresh()->resolvedMediaUrlChanges());
    }

    // ── Admin + storefront read the stored value ────────────────────────────

    public function test_storefront_and_admin_render_the_stored_urls(): void
    {
        Queue::fake();

        $product = Product::factory()->create(['is_active' => true]);
        $product->addMedia(UploadedFile::fake()->image('shown.jpg', 80, 80))->toMediaCollection('gallery');

        $thumb = $product->fresh()->thumbnail_url;
        $this->assertNotNull($thumb);

        $level = ob_get_level();
        $this->get('/shop')->assertOk()->assertSee('src="'.$thumb.'"', false);

        while (ob_get_level() > $level) {
            ob_end_clean();
        }
    }
}
