<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\BrandResource\Pages\ManageBrands;
use App\Filament\Resources\CategoryResource\Pages\ManageCategories;
use App\Filament\Resources\HeroSlideResource\Pages\ManageHeroSlides;
use App\Filament\Resources\HomepageBlockResource\Pages\ManageHomepageBlocks;
use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Models\Brand;
use App\Models\Category;
use App\Models\HeroSlide;
use App\Models\HomepageBlock;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use ReflectionMethod;
use Spatie\MediaLibrary\HasMedia;
use Tests\Concerns\IsolatesMediaDisks;
use Tests\TestCase;

/**
 * Regression suite for "images do not preview after saving / do not show on the
 * website" (see docs/media-architecture.md).
 *
 * Root causes covered:
 *  1. Filament panel uploads followed FILESYSTEM_DISK (default: the PRIVATE
 *     `local` disk) while the storefront links to the public disk → 403/404.
 *  2. Media URLs were absolute (APP_URL / request-host based, signed for private
 *     disks). When that origin differs from the one the browser uses,
 *     FilePond's fetch() rejects and the preview spins forever.
 *
 * The suite runs with FILESYSTEM_DISK=local (phpunit.xml) — the .env.example
 * default — which is precisely the configuration that used to break.
 *
 * Scope: the resources that still use the media library (variant, brand,
 * category, hero slide, homepage block). Product images are plain uploads and
 * are covered by tests/Feature/ProductImageUploadTest.php.
 */
class MediaStorageTest extends TestCase
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

    // ── Configuration contract ──────────────────────────────────────────────

    public function test_media_disk_is_one_public_setting_independent_of_the_default_disk(): void
    {
        // The default disk is private; media must not follow it.
        $this->assertSame('local', config('filesystems.default'));

        $this->assertSame('public', config('media-library.disk_name'));
        $this->assertSame(
            config('media-library.disk_name'),
            config('filament.default_filesystem_disk'),
            'Filament panel uploads must use the same disk as the media library.',
        );
        $this->assertSame('public', config('filesystems.disks.public.visibility'));
    }

    public function test_public_disk_urls_are_host_relative_and_not_derived_from_app_url(): void
    {
        $this->assertStringStartsWith('/', (string) config('filesystems.disks.public.url'));
        $this->assertSame('/storage/7/a.jpg', Storage::disk('public')->url('7/a.jpg'));
    }

    // ── Variant (nested in the product form) ────────────────────────────────
    //
    // Product images no longer live in the media library — they are plain
    // files in public/uploads with their URL on the row. That flow is covered
    // by tests/Feature/ProductImageUploadTest.php.

    public function test_variant_images_uploaded_in_admin_are_stored_on_the_media_disk(): void
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        Livewire::actingAs($this->admin, 'admin')
            ->test(CreateProduct::class)
            ->fillForm([
                'name' => 'Variant Media Guitar',
                'slug' => 'variant-media-guitar',
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'price' => 2000,
                'stock' => 0,
                'is_active' => true,
                'variants' => [[
                    'name' => 'Sunburst',
                    'stock' => 3,
                    'is_active' => true,
                    'variant_images' => [UploadedFile::fake()->image('sunburst.jpg', 600, 600)],
                ]],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $variant = Product::query()->where('slug', 'variant-media-guitar')->firstOrFail()->variants()->firstOrFail();

        $edit = Livewire::actingAs($this->admin, 'admin')
            ->test(EditProduct::class, ['record' => $variant->product_id]);
        $form = $edit->instance()->getSchema('form');

        $this->assertSavedMediaPreviews($variant, 'variant_gallery', $this->uploadedFiles($form, 'variant_images'), expected: 1);
        $this->assertStringStartsWith('/storage/', $variant->fresh()->thumbnailImage());
    }

    // ── Modal "Manage" resources (create action → reopen edit action) ───────

    public function test_brand_logo_previews_after_save(): void
    {
        $page = Livewire::actingAs($this->admin, 'admin')->test(ManageBrands::class)
            ->callAction(TestAction::make('create'), data: [
                'name' => 'Yamaha',
                'slug' => 'yamaha-media',
                'logo' => [UploadedFile::fake()->image('logo.png', 300, 300)],
            ])
            ->assertHasNoFormErrors();

        $brand = Brand::query()->where('slug', 'yamaha-media')->firstOrFail();

        $this->assertSavedMediaPreviews($brand, 'logo', $this->reopenedUploads($page, $brand, 'logo'), expected: 1);
    }

    public function test_category_icon_previews_after_save(): void
    {
        $page = Livewire::actingAs($this->admin, 'admin')->test(ManageCategories::class)
            ->callAction(TestAction::make('create'), data: [
                'name' => 'Guitars',
                'slug' => 'guitars-media',
                'icon' => [UploadedFile::fake()->image('icon.png', 128, 128)],
            ])
            ->assertHasNoFormErrors();

        $category = Category::query()->where('slug', 'guitars-media')->firstOrFail();

        $this->assertSavedMediaPreviews($category, 'icon', $this->reopenedUploads($page, $category, 'icon'), expected: 1);
    }

    public function test_hero_slide_images_preview_after_save_and_render_on_the_storefront(): void
    {
        $page = Livewire::actingAs($this->admin, 'admin')->test(ManageHeroSlides::class)
            ->callAction(TestAction::make('create'), data: [
                'title' => 'Media hero slide',
                'is_active' => true,
                'desktop_image' => [UploadedFile::fake()->image('desktop.jpg', 1500, 800)],
                'mobile_image' => [UploadedFile::fake()->image('mobile.jpg', 900, 1200)],
            ])
            ->assertHasNoFormErrors();

        $slide = HeroSlide::query()->where('title', 'Media hero slide')->firstOrFail();

        $this->assertSavedMediaPreviews($slide, 'desktop_image', $this->reopenedUploads($page, $slide, 'desktop_image'), expected: 1);
        $this->assertSavedMediaPreviews($slide, 'mobile_image', $this->reopenedUploads($page, $slide, 'mobile_image'), expected: 1);

        $desktop = $slide->fresh()->desktopImageUrl();
        $this->assertStringStartsWith('/storage/', (string) $desktop);
        $this->storefront('/')->assertOk()->assertSee('src="'.$desktop.'"', false);
    }

    public function test_homepage_block_image_previews_after_save(): void
    {
        $page = Livewire::actingAs($this->admin, 'admin')->test(ManageHomepageBlocks::class)
            ->callAction(TestAction::make('create'), data: [
                'section_key' => 'promo',
                'title' => 'Media promo',
                'is_active' => true,
                'image' => [UploadedFile::fake()->image('promo.jpg', 800, 400)],
            ])
            ->assertHasNoFormErrors();

        $block = HomepageBlock::query()->where('title', 'Media promo')->firstOrFail();

        $this->assertSavedMediaPreviews($block, 'image', $this->reopenedUploads($page, $block, 'image'), expected: 1);
    }

    // ── Storefront URL resolution ───────────────────────────────────────────

    public function test_storefront_uses_the_webp_conversion_only_once_it_exists(): void
    {
        Queue::fake(); // conversions are queued: none are generated yet

        $variant = ProductVariant::factory()->create();
        $media = $variant->addMedia(UploadedFile::fake()->image('amp.jpg', 80, 80))->toMediaCollection('variant_gallery');

        $variant = $variant->fresh()->load('media');
        $this->assertSame($media->getUrl(), $variant->thumbnailImage(), 'Original is served until the conversion is generated.');

        $media->markAsConversionGenerated('variant-thumb-webp');
        $media->save();

        $variant = $variant->fresh()->load('media');
        $this->assertSame($media->getUrl('variant-thumb-webp'), $variant->thumbnailImage());
    }

    public function test_media_urls_do_not_depend_on_app_url(): void
    {
        config(['app.url' => 'https://wrong-host.invalid']);

        $variant = ProductVariant::factory()->create();
        $media = $variant->addMedia(UploadedFile::fake()->image('amp.jpg', 80, 80))->toMediaCollection('variant_gallery');

        $this->assertStringStartsWith('/storage/', $media->getUrl());
        $this->assertStringNotContainsString('wrong-host.invalid', $media->getUrl());
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

    /**
     * The saved media sits on the public media disk, and what the edit form
     * hands to FilePond is that very file's host-relative, unsigned URL.
     *
     * @param  array<string, array{url:string}>  $uploads  descriptors from getUploadedFiles()
     */
    private function assertSavedMediaPreviews(HasMedia $record, string $collection, array $uploads, int $expected): void
    {
        $media = $record->fresh()->getMedia($collection);

        $this->assertCount($expected, $media, "Expected {$expected} media item(s) in [{$collection}].");
        $this->assertCount($expected, $uploads, "The reopened form must list {$expected} file(s) for [{$collection}].");

        foreach ($media as $item) {
            // Stored where the storefront can serve it — never on the private default disk.
            $this->assertSame('public', $item->disk);
            $this->assertSame('public', $item->conversions_disk);
            Storage::disk('public')->assertExists($item->getPathRelativeToRoot());
            Storage::disk('local')->assertMissing($item->getPathRelativeToRoot());

            // The preview FilePond fetches.
            $this->assertArrayHasKey($item->uuid, $uploads);
            $url = $uploads[$item->uuid]['url'];
            $this->assertSame($item->getUrl(), $url);
            $this->assertStringStartsWith('/storage/', $url, 'Preview URL must be host-relative (no APP_URL / request host).');
            $this->assertStringNotContainsString('signature=', $url, 'Preview URL must not be a host-bound signed URL.');
            $this->assertPublicFileBehindUrl($url);
        }
    }

    /** A /storage/... URL maps 1:1 onto a file of the public disk (what the web server serves through the storage symlink). */
    private function assertPublicFileBehindUrl(string $url): void
    {
        $path = rawurldecode(Str::after((string) parse_url($url, PHP_URL_PATH), '/storage/'));

        $this->assertTrue(Storage::disk('public')->exists($path), "No file on the public disk behind [{$url}].");
    }

    /**
     * What the edit form's file-upload field returns to FilePond (id => descriptor).
     *
     * @return array<string, array{name:?string,size:?int,type:?string,url:string}>
     */
    private function uploadedFiles(Schema $schema, string $field): array
    {
        foreach ($schema->getFlatComponents(withHidden: true) as $component) {
            if ($component instanceof SpatieMediaLibraryFileUpload && $component->getName() === $field) {
                return $component->getUploadedFiles() ?? [];
            }
        }

        $this->fail("No upload field [{$field}] found in the form schema.");
    }

    /**
     * Open the table's edit modal for $record (the real "reopen" path of a
     * Manage* page) and read the upload field's descriptors.
     *
     * @return array<string, array{name:?string,size:?int,type:?string,url:string}>
     */
    private function reopenedUploads(Testable $page, object $record, string $field): array
    {
        $page->mountAction(TestAction::make('edit')->table($record));

        $livewire = $page->instance();
        $method = new ReflectionMethod($livewire, 'getMountedActionSchema');

        return $this->uploadedFiles($method->invoke($livewire), $field);
    }
}
