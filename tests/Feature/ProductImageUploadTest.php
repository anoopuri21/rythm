<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Support\ImageStore;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Livewire\Livewire;
use Tests\Concerns\IsolatesMediaDisks;
use Tests\TestCase;

/**
 * Product images the simple way (docs/media-architecture.md §7):
 * the file lives in public/uploads/products, its URL lives on the product row,
 * and that same URL is what the admin preview and the storefront use.
 */
class ProductImageUploadTest extends TestCase
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

    // ── Admin upload → file on disk → URL on the row → storefront ────────────

    public function test_uploaded_images_are_saved_in_the_uploads_folder_and_their_url_on_the_product(): void
    {
        $this->createProductWithUploads([
            'image' => [UploadedFile::fake()->image('front.jpg', 800, 800)],
            'gallery' => [UploadedFile::fake()->image('side.jpg', 800, 800)],
            'og_image' => [UploadedFile::fake()->image('share.jpg', 1200, 630)],
        ], slug: 'upload-guitar');

        $product = Product::query()->where('slug', 'upload-guitar')->firstOrFail();

        foreach (['image', 'og_image'] as $field) {
            $url = (string) $product->{$field};
            $this->assertStringStartsWith('/uploads/products/', $url);
            $this->assertNull(parse_url($url, PHP_URL_HOST), 'Stored URLs must be host-relative, never APP_URL based.');
            $this->assertTrue(ImageStore::exists($url), "No file on the uploads disk behind [{$url}].");
        }

        $this->assertCount(1, $product->gallery);
        $this->assertStringStartsWith('/uploads/products/', $product->gallery[0]);

        // Never on the private default disk, and no media-library row either.
        Storage::disk('local')->assertMissing(ImageStore::path($product->image));
        $this->assertCount(0, $product->fresh()->getMedia('gallery'));
    }

    public function test_reopening_the_form_previews_the_url_that_is_stored_on_the_row(): void
    {
        $this->createProductWithUploads([
            'image' => [UploadedFile::fake()->image('front.jpg', 800, 800)],
            'gallery' => [UploadedFile::fake()->image('side.jpg', 800, 800)],
        ], slug: 'reopen-guitar');

        $product = Product::query()->where('slug', 'reopen-guitar')->firstOrFail();

        $schema = Livewire::actingAs($this->admin, 'admin')
            ->test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->instance()
            ->getSchema('form');

        $main = $this->uploadedFiles($schema, 'image');
        $gallery = $this->uploadedFiles($schema, 'gallery');

        $this->assertCount(1, $main);
        $this->assertSame($product->image, reset($main)['url']);
        $this->assertCount(1, $gallery);
        $this->assertSame($product->gallery[0], reset($gallery)['url']);
    }

    public function test_the_storefront_renders_the_stored_url_and_makes_it_absolute_for_crawlers(): void
    {
        $this->createProductWithUploads([
            'image' => [UploadedFile::fake()->image('front.jpg', 800, 800)],
        ], slug: 'storefront-guitar');

        $product = Product::query()->where('slug', 'storefront-guitar')->firstOrFail();

        $this->assertSame($product->image, $product->heroImage());
        $this->assertSame($product->image, $product->thumbnailImage());
        $this->assertSame([$product->image], $product->galleryImages());

        $this->storefront('/shop')->assertOk()->assertSee('src="'.$product->thumbnailImage().'"', false);

        $this->storefront(route('product.show', $product))
            ->assertOk()
            ->assertSee('property="og:image" content="'.url($product->ogImage()).'"', false);
    }

    // ── File lifecycle ───────────────────────────────────────────────────────

    public function test_replacing_an_image_deletes_the_previous_file(): void
    {
        $product = Product::factory()->create();
        $first = $this->storeImage('first.jpg');

        $product->update(['image' => $first]);
        $this->assertTrue(ImageStore::exists($first));

        $second = $this->storeImage('second.jpg');
        $product->update(['image' => $second]);

        $this->assertSame($second, $product->fresh()->image);
        $this->assertTrue(ImageStore::exists($second));
        $this->assertFalse(ImageStore::exists($first), 'The replaced file must not stay behind.');
    }

    public function test_removing_a_gallery_image_deletes_only_that_file(): void
    {
        $product = Product::factory()->create();
        $keep = $this->storeImage('keep.jpg');
        $drop = $this->storeImage('drop.jpg');

        $product->update(['gallery' => [$keep, $drop]]);
        $this->assertCount(2, $product->fresh()->gallery);

        $product->update(['gallery' => [$keep]]);

        $this->assertTrue(ImageStore::exists($keep));
        $this->assertFalse(ImageStore::exists($drop));
    }

    public function test_soft_deleting_keeps_the_files_and_force_deleting_removes_them(): void
    {
        $url = $this->storeImage('deletable.jpg');
        $product = Product::factory()->create(['image' => $url]);

        $product->delete();
        $this->assertTrue(ImageStore::exists($url), 'A soft-deleted product must be restorable with its image.');

        $product->forceDelete();
        $this->assertFalse(ImageStore::exists($url));
    }

    // ── Fallbacks and limits ─────────────────────────────────────────────────

    public function test_a_product_without_an_upload_falls_back_to_the_committed_asset(): void
    {
        $slug = 'akg-k240-studio-headphones';
        $this->assertFileExists(public_path('images/products/'.$slug.'.jpg'));

        $product = Product::factory()->create(['slug' => $slug, 'image' => null]);

        $this->assertSame('/images/products/'.$slug.'.jpg', $product->heroImage());
        $this->assertSame(['/images/products/'.$slug.'.jpg'], $product->galleryImages());
        $this->assertNull(Product::factory()->create(['image' => null])->heroImage());
    }

    public function test_svg_uploads_are_rejected(): void
    {
        Livewire::actingAs($this->admin, 'admin')
            ->test(CreateProduct::class)
            ->fillForm($this->formDefaults(slug: 'svg-guitar', overrides: [
                'image' => [UploadedFile::fake()->create('evil.svg', 10, 'image/svg+xml')],
            ]))
            ->call('create')
            ->assertHasFormErrors(['image']);

        $this->assertNull(Product::query()->where('slug', 'svg-guitar')->first());
    }

    // ── Migration off the media library ──────────────────────────────────────

    public function test_the_backfill_command_copies_media_library_images_onto_the_uploads_disk(): void
    {
        $product = Product::factory()->create(['image' => null]);
        $product->addMedia(UploadedFile::fake()->image('front.jpg', 400, 400))->toMediaCollection('gallery');
        $product->addMedia(UploadedFile::fake()->image('side.jpg', 400, 400))->toMediaCollection('gallery');
        $product->addMedia(UploadedFile::fake()->image('share.jpg', 400, 400))->toMediaCollection('og');

        $this->artisan('product-images:migrate', ['--dry-run' => true])
            ->expectsOutputToContain('Would copy')
            ->assertSuccessful();

        $this->assertNull($product->fresh()->image, 'A dry run must not write anything.');

        $this->artisan('product-images:migrate')->assertSuccessful();

        $product = $product->fresh();
        $this->assertStringStartsWith('/uploads/products/', (string) $product->image);
        $this->assertCount(1, $product->gallery);
        $this->assertStringStartsWith('/uploads/products/', (string) $product->og_image);
        $this->assertTrue(ImageStore::exists($product->image));
        $this->assertTrue(ImageStore::exists($product->gallery[0]));
        $this->assertTrue(ImageStore::exists($product->og_image));

        // Idempotent: a second run copies nothing new.
        $this->artisan('product-images:migrate')
            ->expectsOutputToContain('Copied 0 product(s)')
            ->assertSuccessful();
    }

    public function test_the_backfill_command_reports_products_whose_image_link_is_broken(): void
    {
        Product::factory()->create(['image' => '/uploads/products/never-uploaded.jpg']);

        $this->artisan('product-images:migrate')
            ->assertFailed()
            ->expectsOutputToContain('never-uploaded.jpg');
    }

    // ── helpers ──────────────────────────────────────────────────────────────

    /** @param  array<string, mixed>  $media */
    private function createProductWithUploads(array $media, string $slug): void
    {
        Livewire::actingAs($this->admin, 'admin')
            ->test(CreateProduct::class)
            ->fillForm($this->formDefaults(slug: $slug, overrides: $media))
            ->call('create')
            ->assertHasNoFormErrors();
    }

    /** @param  array<string, mixed>  $overrides  @return array<string, mixed> */
    private function formDefaults(string $slug, array $overrides = []): array
    {
        return array_merge([
            'name' => ucfirst(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'category_id' => Category::factory()->create()->id,
            'brand_id' => Brand::factory()->create()->id,
            'price' => 1000,
            'stock' => 5,
            'is_active' => true,
        ], $overrides);
    }

    private function storeImage(string $name): string
    {
        $url = ImageStore::store(UploadedFile::fake()->image($name, 400, 400), 'products');

        $this->assertNotNull($url);
        $this->assertTrue(ImageStore::exists($url));

        return (string) $url;
    }

    /**
     * What the edit form's file-upload field hands to the browser.
     *
     * @return array<string, array{name:string,size:int,type:?string,url:string}>
     */
    private function uploadedFiles(Schema $schema, string $field): array
    {
        foreach ($schema->getFlatComponents(withHidden: true) as $component) {
            if ($component instanceof FileUpload && $component->getName() === $field) {
                return $component->getUploadedFiles() ?? [];
            }
        }

        return $this->fail("No upload field [{$field}] found in the form schema.");
    }

    /** Close the output buffer a Filament page leaves open (see MediaStorageTest::storefront). */
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
