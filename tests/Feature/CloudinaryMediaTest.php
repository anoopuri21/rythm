<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\CategoryResource\Pages\ManageCategories;
use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\MediaRelocationService;
use App\Services\MediaReuseService;
use App\Support\MediaDisk;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Livewire\Livewire;
use RuntimeException;
use Tests\Concerns\IsolatesMediaDisks;
use Tests\TestCase;

/**
 * docs/cloudinary-media.md → M-9 — phase 1: product (gallery/og/variants) and
 * category icons are stored on and served from Cloudinary.
 *
 * The rollout is enabled per test with a fake disk, so no credentials and no
 * network are needed; the delivery URLs are still the real derived
 * `https://res.cloudinary.com/<cloud>/...` shapes (that is the code under test).
 *
 * `phpunit.xml` pins MEDIA_CLOUDINARY=false: every other suite keeps running in
 * the legacy mode (MEDIA_DISK + /storage URLs), which is what these tests
 * compare against.
 */
class CloudinaryMediaTest extends TestCase
{
    use IsolatesMediaDisks;
    use RefreshDatabase;

    private const BASE_URL = 'https://res.cloudinary.com/demo/image/upload/';

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->isolateMediaDisks();

        Storage::fake(MediaDisk::CLOUDINARY);

        // Storage::fake() swaps in a local driver — restore the configured
        // driver so media:doctor's config contract sees the real shape, and
        // name the cloud the delivery URLs must be derived from.
        config([
            'filesystems.disks.cloudinary.driver' => 'cloudinary',
            'filesystems.disks.cloudinary.cloud' => 'demo',
            'media-library.cloudinary.enabled' => true,
        ]);

        $this->admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    }

    // ── Which collections follow Cloudinary ─────────────────────────────────

    public function test_only_the_phase_one_collections_follow_cloudinary(): void
    {
        $this->assertTrue(MediaDisk::enabled());

        foreach (['gallery', 'og', 'variant_gallery', 'icon'] as $collection) {
            $this->assertSame('cloudinary', MediaDisk::forCollection($collection), $collection);
        }

        // Everything else keeps the media disk until the owner widens the list.
        foreach (['logo', 'image', 'desktop_image', 'mobile_image'] as $collection) {
            $this->assertSame('public', MediaDisk::forCollection($collection), $collection);
        }
    }

    public function test_the_rollout_stays_off_without_credentials(): void
    {
        config([
            'media-library.cloudinary.enabled' => true,
            'filesystems.disks.cloudinary.cloud' => null,
            'filesystems.disks.cloudinary.url' => null,
        ]);

        $this->assertFalse(MediaDisk::enabled(), 'A half-configured rollout must never route uploads to a disk it cannot serve.');
        $this->assertSame('public', MediaDisk::forCollection('gallery'));

        $product = Product::factory()->create();
        $media = $product->addMedia(UploadedFile::fake()->image('local.jpg', 80, 80))->toMediaCollection('gallery');

        $this->assertSame('public', $media->disk);
        $this->assertStringStartsWith('/storage/', (string) $product->fresh()->thumbnail_url);
    }

    // ── Product uploads (admin panel) ───────────────────────────────────────

    public function test_product_images_uploaded_in_the_panel_are_stored_on_cloudinary_and_served_from_it(): void
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        Livewire::actingAs($this->admin, 'admin')
            ->test(CreateProduct::class)
            ->fillForm([
                'name' => 'Cloudinary Test Guitar',
                'slug' => 'cloudinary-test-guitar',
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'price' => 1000,
                'stock' => 5,
                'is_active' => true,
                'gallery' => [UploadedFile::fake()->image('front.jpg', 600, 600)],
                'og' => [UploadedFile::fake()->image('share.jpg', 1200, 630)],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::query()->where('slug', 'cloudinary-test-guitar')->firstOrFail();
        $media = $product->getFirstMedia('gallery');

        $this->assertNotNull($media);
        $this->assertSame('cloudinary', $media->disk);
        $this->assertSame('cloudinary', $media->conversions_disk ?: $media->disk);
        $this->assertTrue(Storage::disk('cloudinary')->exists($media->getPathRelativeToRoot()));

        // Nothing was written to the local media disk for this upload.
        $this->assertFalse(
            Storage::disk('public')->exists($media->getPathRelativeToRoot()),
            'A Cloudinary upload must not touch storage/app/public.',
        );

        $fresh = $product->fresh();

        // Small image = the thumb-webp conversion name, now a delivery transformation.
        $this->assertStringStartsWith(self::BASE_URL, (string) $fresh->thumbnail_url);
        $this->assertStringContainsString('c_fit,w_480,h_480,f_auto,q_auto:good', (string) $fresh->thumbnail_url);

        // Large image = the gallery-webp conversion name.
        $this->assertStringStartsWith(self::BASE_URL.'c_fit,w_1200,h_1200,f_auto,q_auto:good/', (string) ($fresh->gallery_urls[0] ?? null));
        $this->assertSame($fresh->gallery_urls[0], $fresh->heroImage());

        $og = $product->getFirstMedia('og');
        $this->assertNotNull($og);
        $this->assertSame('cloudinary', $og->disk);
        $this->assertSame(self::BASE_URL.'f_auto,q_auto:good/'.$og->getPathRelativeToRoot(), $fresh->og_image_url);

        // The storefront renders the CDN URL straight from the stored column.
        $this->storefront('/shop')->assertOk()->assertSee('src="'.$fresh->thumbnailImage().'"', false);
    }

    // ── Category icons (admin panel) ────────────────────────────────────────

    public function test_category_icons_uploaded_in_the_panel_are_stored_on_cloudinary(): void
    {
        Livewire::actingAs($this->admin, 'admin')->test(ManageCategories::class)
            ->callAction(TestAction::make('create'), data: [
                'name' => 'Guitars',
                'slug' => 'guitars-cloudinary',
                'icon' => [UploadedFile::fake()->image('icon.png', 240, 240)],
            ])
            ->assertHasNoFormErrors();

        $category = Category::query()->where('slug', 'guitars-cloudinary')->firstOrFail();
        $media = $category->getFirstMedia('icon');

        $this->assertNotNull($media);
        $this->assertSame('cloudinary', $media->disk);
        $this->assertTrue(Storage::disk('cloudinary')->exists($media->getPathRelativeToRoot()));
        $this->assertFalse(Storage::disk('public')->exists($media->getPathRelativeToRoot()));

        $this->assertSame(
            self::BASE_URL.'f_auto,q_auto:good/'.$media->getPathRelativeToRoot(),
            $category->fresh()->icon_url,
        );
    }

    // ── Legacy rows and other collections are untouched ─────────────────────

    public function test_existing_storage_urls_and_other_collections_keep_the_media_disk(): void
    {
        // Queued WebP conversions must not fire: the point of this test is the
        // exact original-URL path every pre-Cloudinary row already renders.
        Queue::fake();

        $product = Product::factory()->create();

        // Exactly how every pre-Cloudinary row looks: stored on the media disk.
        $legacy = $product->addMedia(UploadedFile::fake()->image('legacy.jpg', 80, 80))
            ->toMediaCollection('gallery', 'public');

        $this->assertSame('public', $legacy->disk);
        $this->assertTrue(Storage::disk('public')->exists($legacy->getPathRelativeToRoot()));

        $fresh = $product->fresh();
        $this->assertSame('/storage/'.$legacy->getPathRelativeToRoot(), $fresh->thumbnail_url);
        $this->assertSame('/storage/'.$legacy->getPathRelativeToRoot(), $fresh->gallery_urls[0]);

        // Mixed catalogue: a new cloud upload next to the legacy row must not
        // disturb the legacy URL (M-2/M-7).
        $cloud = $product->addMedia(UploadedFile::fake()->image('cloud.jpg', 80, 80))
            ->toMediaCollection('gallery');

        $this->assertSame('cloudinary', $cloud->disk);
        $this->assertSame('/storage/'.$legacy->getPathRelativeToRoot(), $product->fresh()->thumbnail_url);

        // Collections outside the phase-1 list stay on MEDIA_DISK.
        $brand = Brand::factory()->create();
        $logo = $brand->addMedia(UploadedFile::fake()->image('logo.png', 60, 60))->toMediaCollection('logo');

        $this->assertSame('public', $logo->disk);
        $this->assertSame('/storage/'.$logo->getPathRelativeToRoot(), $brand->fresh()->logo_url);
    }

    // ── Repair tooling must never fight Cloudinary rows ─────────────────────

    public function test_cloud_rows_are_never_reported_or_relocated_as_misplaced(): void
    {
        $product = Product::factory()->create();
        $cloud = $product->addMedia(UploadedFile::fake()->image('cloud.jpg', 80, 80))->toMediaCollection('gallery');

        $this->assertSame('cloudinary', $cloud->disk);

        $service = app(MediaRelocationService::class);

        $this->assertSame(0, $service->misplacedCount(), 'Cloudinary rows are intentionally off MEDIA_DISK — not misplaced.');
        $this->assertSame(1, $service->cloudHostedCount());
        $this->assertSame(0, $service->relocateAll(true)['examined'], 'media:relocate must leave Cloudinary files alone.');

        $this->expectException(RuntimeException::class);
        $service->relocate($cloud);
    }

    public function test_media_doctor_stays_healthy_with_cloud_rows(): void
    {
        $product = Product::factory()->create();
        $product->addMedia(UploadedFile::fake()->image('cloud.jpg', 80, 80))->toMediaCollection('gallery');

        // Exit code 0 = no FAIL rows; the only WARN (missing symlink) is expected in tests.
        $this->artisan('media:doctor')->assertExitCode(0);
    }

    // ── Variant images (product form repeater → variant_gallery) ────────────

    public function test_variant_images_are_stored_on_cloudinary_and_served_from_it(): void
    {
        $variant = ProductVariant::factory()->create();

        $media = $variant->addMedia(UploadedFile::fake()->image('variant.jpg', 300, 300))
            ->toMediaCollection('variant_gallery');

        $this->assertSame('cloudinary', $media->disk);
        $this->assertSame('cloudinary', $media->conversions_disk ?: $media->disk);
        $this->assertTrue(Storage::disk('cloudinary')->exists($media->getPathRelativeToRoot()));
        $this->assertFalse(Storage::disk('public')->exists($media->getPathRelativeToRoot()));

        $fresh = $variant->fresh();

        // Both variant conversion names are delivery transformations (M-9).
        $this->assertStringStartsWith(
            self::BASE_URL.'c_fit,w_240,h_240,f_auto,q_auto:good/',
            (string) $fresh->thumbnail_url,
        );
        $this->assertStringStartsWith(
            self::BASE_URL.'c_fit,w_1200,h_1200,f_auto,q_auto:good/',
            (string) ($fresh->gallery_urls[0] ?? null),
        );
    }

    // ── Reuse + Cloudinary: one image, one asset (M-10 × M-9) ───────────────

    public function test_a_reused_image_stays_one_cloudinary_asset_and_uploads_nothing(): void
    {
        $owner = Product::factory()->create();
        $media = $owner->addMedia(UploadedFile::fake()->image('shared-cloud.jpg', 400, 400))
            ->toMediaCollection('gallery');

        $this->assertSame('cloudinary', $media->disk);
        $assetsBefore = Storage::disk('cloudinary')->allFiles();
        $this->assertCount(1, $assetsBefore);

        $other = Product::factory()->create();
        $reference = app(MediaReuseService::class)->attach($media, $other, 'gallery');

        $this->assertTrue($reference->isShared());
        $this->assertSame('cloudinary', $reference->disk);
        $this->assertSame($media->shared_path ?? null, null, 'The owner row owns the file.');

        // Same public_id → the same single asset, served through the CDN.
        $this->assertSame($media->getUrl(), $reference->getUrl());
        $this->assertSame('cloudinary', $reference->sharedOwner?->disk);
        $this->assertStringStartsWith(self::BASE_URL, $reference->getUrl());

        // The reused product stores the CDN URL, and nothing was uploaded anywhere.
        $this->assertSame($media->getUrl(), $other->fresh()->thumbnail_url);
        $this->assertSame($assetsBefore, Storage::disk('cloudinary')->allFiles(), 'Reuse must not upload a second asset.');
        $this->assertSame([], Storage::disk('public')->allFiles(), 'Reuse must not write to the local media disk either.');
    }

    // ── Duplicate cleanup on Cloudinary (M-10 dedupe) ───────────────────────

    public function test_media_dedupe_merges_duplicate_cloud_uploads_into_one_asset(): void
    {
        $productA = Product::factory()->create();
        $productB = Product::factory()->create();

        // The same bytes uploaded twice — the duplicate this whole change exists
        // for. Both copies land on Cloudinary (phase-1 collections).
        // `preservingOriginal()`: Spatie unlinks the source path after a
        // successful add, so the shared temp file must survive the first add.
        $path = UploadedFile::fake()->image('same-photo.jpg', 70, 70)->getRealPath();

        $keeper = $productA->addMedia($path)->preservingOriginal()->usingFileName('same-photo.jpg')->toMediaCollection('gallery');
        $duplicate = $productB->addMedia($path)->preservingOriginal()->usingFileName('same-photo.jpg')->toMediaCollection('gallery');

        $this->assertSame('cloudinary', $keeper->disk);
        $this->assertSame('cloudinary', $duplicate->disk);
        $this->assertCount(2, Storage::disk('cloudinary')->allFiles());

        $this->artisan('media:dedupe')->assertExitCode(0);

        $merged = $duplicate->fresh();

        $this->assertTrue($merged->isShared());
        $this->assertSame($keeper->getUrl(), $merged->getUrl(), 'Both usages must resolve to the kept asset.');
        $this->assertCount(1, Storage::disk('cloudinary')->allFiles(), 'Cloudinary keeps exactly one asset.');
        $this->assertSame([], Storage::disk('public')->allFiles(), 'Nothing may move onto the local disk.');

        // Both products still render a working image — now from the one asset.
        $this->assertSame($keeper->getUrl(), $productA->fresh()->thumbnailImage());
        $this->assertSame($keeper->getUrl(), $productB->fresh()->thumbnailImage());
    }

    // ── Legacy URLs are byte-identical with the switch on ───────────────────

    public function test_enabling_cloudinary_never_changes_a_legacy_storage_url(): void
    {
        Queue::fake(); // a legacy row on the public disk has queued conversions

        $product = Product::factory()->create();

        // Exactly how every pre-Cloudinary row looks today.
        $legacy = $product->addMedia(UploadedFile::fake()->image('legacy-stay.jpg', 80, 80))
            ->toMediaCollection('gallery', 'public');

        $thumbBefore = $product->fresh()->thumbnail_url;
        $galleryBefore = $product->fresh()->gallery_urls;

        $this->assertStringStartsWith('/storage/', (string) $thumbBefore);
        $this->assertSame($galleryBefore, $product->fresh()->gallery_urls);

        // The deploy's column sync (and the observer) must leave them alone.
        $this->artisan('media:sync-urls')->assertSuccessful();

        $fresh = $product->fresh();

        $this->assertSame($thumbBefore, $fresh->thumbnail_url, 'A legacy row must keep its exact /storage URL.');
        $this->assertSame($galleryBefore, $fresh->gallery_urls);
        $this->assertTrue(Storage::disk('public')->exists($legacy->getPathRelativeToRoot()));
        $this->assertFalse(Storage::disk('cloudinary')->exists($legacy->getPathRelativeToRoot()));

        // …and the storefront still renders that same URL.
        $this->storefront('/shop')->assertOk()->assertSee('src="'.$thumbBefore.'"', false);
    }

    // ── helpers ─────────────────────────────────────────────────────────────

    /**
     * GET a storefront page. Rendering a page right after a Filament
     * CreateRecord Livewire test leaves one empty output buffer open (a
     * harness quirk that exists without any media involved) and PHPUnit then
     * flags the test as risky — close what the request opened.
     */
    private function storefront(string $uri): TestResponse
    {
        $level = ob_get_level();
        $response = $this->get($uri);

        while (ob_get_level() > $level) {
            ob_end_clean();
        }

        return $response;
    }
}
