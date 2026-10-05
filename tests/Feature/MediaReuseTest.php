<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\MediaLibraryResource\Pages\ManageMediaLibrary;
use App\Models\Media;
use App\Models\Product;
use App\Models\User;
use App\Services\MediaRelocationService;
use App\Services\MediaReuseService;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use RuntimeException;
use Tests\Concerns\IsolatesMediaDisks;
use Tests\TestCase;

/**
 * Media reuse — "upload once, use it anywhere" (docs/media-reuse.md → M-10).
 *
 * The promise under test: the same image used in several places is stored
 * exactly once (local disk and Cloudinary alike), every usage resolves to that
 * one file, and removing a usage never breaks the others.
 */
class MediaReuseTest extends TestCase
{
    use IsolatesMediaDisks;
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->isolateMediaDisks();
        Queue::fake();

        $this->admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    }

    // ── Reuse writes no second copy ─────────────────────────────────────────

    public function test_a_reused_image_is_stored_once_and_resolves_to_the_same_url(): void
    {
        $owner = Product::factory()->create();
        $media = $owner->addMedia(UploadedFile::fake()->image('shared.jpg', 120, 120))
            ->toMediaCollection('gallery');

        $filesOnDisk = Storage::disk('public')->allFiles();

        $other = Product::factory()->create();
        $reference = app(MediaReuseService::class)->attach($media, $other, 'gallery');

        $this->assertTrue($reference->isShared());
        $this->assertSame($media->getPathRelativeToRoot(), $reference->getPathRelativeToRoot());
        $this->assertSame($media->getUrl(), $reference->getUrl());

        // The reused row has no directory of its own: not a single byte was
        // written, the disk holds exactly the files it held before.
        $this->assertSame(
            $filesOnDisk,
            Storage::disk('public')->allFiles(),
            'Reusing an image must not create another copy on disk.',
        );

        $other = $other->fresh();
        $this->assertSame([$media->getUrl()], $other->gallery_urls);
        $this->assertSame($media->getUrl(), $other->thumbnailImage());
    }

    public function test_reusing_the_same_image_twice_is_idempotent(): void
    {
        $owner = Product::factory()->create();
        $media = $owner->addMedia(UploadedFile::fake()->image('once.jpg', 90, 90))->toMediaCollection('gallery');

        $target = Product::factory()->create();
        $reuse = app(MediaReuseService::class);

        $first = $reuse->attach($media, $target, 'gallery');
        $second = $reuse->attach($media, $target, 'gallery');

        $this->assertTrue($first->is($second), 'The same image cannot be attached to one place twice.');
        $this->assertSame(1, $target->fresh()->getMedia('gallery')->count());
    }

    public function test_an_image_can_be_reused_in_another_collection_of_the_same_record(): void
    {
        $product = Product::factory()->create();
        $media = $product->addMedia(UploadedFile::fake()->image('social.jpg', 200, 120))
            ->toMediaCollection('gallery');

        $reference = app(MediaReuseService::class)->attach($media, $product, 'og');

        $this->assertSame('og', $reference->collection_name);
        $this->assertSame($media->getUrl(), $product->fresh()->og_image_url);
        $this->assertSame($media->getPathRelativeToRoot(), $reference->getPathRelativeToRoot());
    }

    public function test_the_social_image_falls_back_to_the_first_gallery_image(): void
    {
        $product = Product::factory()->create();
        $media = $product->addMedia(UploadedFile::fake()->image('fallback.jpg', 300, 200))
            ->toMediaCollection('gallery');

        // No og upload, no og column — the gallery original is used, so the
        // social image never needs a second upload.
        $this->assertNull($product->fresh()->og_image_url);
        $this->assertSame($media->getUrl(), $product->fresh()->ogImage());
    }

    // ── Conversions upgrade every usage ─────────────────────────────────────

    public function test_conversion_completion_upgrades_every_usage(): void
    {
        $owner = Product::factory()->create();
        $media = $owner->addMedia(UploadedFile::fake()->image('webp.jpg', 150, 150))->toMediaCollection('gallery');

        $other = Product::factory()->create();
        $reference = app(MediaReuseService::class)->attach($media, $other, 'gallery');

        $this->assertSame($media->getUrl(), $other->fresh()->thumbnail_url, 'Until the conversion exists, the original is served.');

        $media->markAsConversionGenerated('thumb-webp');
        $media->markAsConversionGenerated('gallery-webp');
        $media->save();

        // The reused row resolves into the owner's conversions directory…
        $this->assertSame($media->getUrl('thumb-webp'), $reference->fresh()->getUrl('thumb-webp'));

        // …and the product that reuses it stores the upgraded URL (M-7).
        $this->assertSame($media->getUrl('thumb-webp'), $other->fresh()->thumbnail_url);
        $this->assertSame([$media->getUrl('gallery-webp')], $other->fresh()->gallery_urls);
    }

    // ── Deleting never breaks the other usages ──────────────────────────────

    public function test_deleting_a_usage_keeps_the_file_and_the_original(): void
    {
        $owner = Product::factory()->create();
        $media = $owner->addMedia(UploadedFile::fake()->image('keep.jpg', 80, 80))->toMediaCollection('gallery');

        $other = Product::factory()->create();
        $reference = app(MediaReuseService::class)->attach($media, $other, 'gallery');

        $reference->delete();

        $this->assertTrue(Storage::disk('public')->exists($media->getPathRelativeToRoot()), 'The stored file belongs to its owner and must survive.');
        $this->assertSame([], $other->fresh()->gallery_urls);
        $this->assertSame($media->getUrl(), $owner->fresh()->thumbnailImage());
    }

    public function test_deleting_the_owner_keeps_the_file_until_the_last_usage(): void
    {
        $owner = Product::factory()->create();
        $media = $owner->addMedia(UploadedFile::fake()->image('survives.jpg', 80, 80))->toMediaCollection('gallery');

        $other = Product::factory()->create();
        $reference = app(MediaReuseService::class)->attach($media, $other, 'gallery');

        $path = $media->getPathRelativeToRoot();
        $url = $media->getUrl();

        $media->delete();

        $this->assertTrue(Storage::disk('public')->exists($path), 'Another usage still needs the file.');
        $this->assertSame($url, $other->fresh()->thumbnail_url, 'The reused URL keeps working after the owner row is gone.');

        // The last usage takes the file with it.
        $reference->fresh()->delete();

        $this->assertFalse(Storage::disk('public')->exists($path), 'The last usage cleans up the stored file.');
    }

    // ── Repair tooling respects reused rows ─────────────────────────────────

    public function test_relocation_never_moves_a_reused_row(): void
    {
        $owner = Product::factory()->create();
        $media = $owner->addMedia(UploadedFile::fake()->image('relocate.jpg', 80, 80))->toMediaCollection('gallery');

        $other = Product::factory()->create();
        $reference = app(MediaReuseService::class)->attach($media, $other, 'gallery');

        $service = app(MediaRelocationService::class);

        $this->assertSame(0, $service->misplacedCount(), 'Reused rows are not misplaced — they follow their owner.');

        $this->expectException(RuntimeException::class);
        $service->relocate($reference);
    }

    public function test_media_doctor_is_healthy_with_reused_rows(): void
    {
        $owner = Product::factory()->create();
        $media = $owner->addMedia(UploadedFile::fake()->image('doctor.jpg', 80, 80))->toMediaCollection('gallery');

        app(MediaReuseService::class)->attach($media, Product::factory()->create(), 'gallery');

        $this->artisan('media:doctor')->assertExitCode(0);
    }

    // ── Existing duplicates can be merged ───────────────────────────────────

    public function test_media_dedupe_merges_identical_uploads_into_one_stored_file(): void
    {
        $productA = Product::factory()->create();
        $productB = Product::factory()->create();

        // The very same bytes uploaded twice (exactly what the owner complained
        // about) — two media rows, two files.
        $path = UploadedFile::fake()->image('same-photo.jpg', 70, 70)->getRealPath();

        $owner = $productA->addMedia($path)->usingFileName('same-photo.jpg')->toMediaCollection('gallery');
        $duplicate = $productB->addMedia($path)->usingFileName('same-photo.jpg')->toMediaCollection('gallery');

        $duplicatePath = $duplicate->getPathRelativeToRoot();
        $keptPath = $owner->getPathRelativeToRoot();

        $this->assertNotSame($keptPath, $duplicatePath);
        $this->assertSame(2, Media::query()->fileOwners()->count());

        $this->artisan('media:dedupe --dry-run')->assertExitCode(0);
        $this->assertSame(2, Media::query()->fileOwners()->count(), 'A dry run must not merge anything.');

        $this->artisan('media:dedupe')->assertExitCode(0);

        $duplicate = $duplicate->fresh();

        $this->assertSame(1, Media::query()->fileOwners()->count());
        $this->assertTrue($duplicate->isShared(), 'The extra copy becomes a usage of the kept file.');
        $this->assertSame($keptPath, $duplicate->getPathRelativeToRoot());
        $this->assertFalse(Storage::disk('public')->exists($duplicatePath), 'The duplicate copy is gone from disk.');
        $this->assertTrue(Storage::disk('public')->exists($keptPath), 'The kept file must never be touched.');

        // Both products still show the image — now from the one stored file.
        $expected = $owner->getUrl();
        $this->assertSame($expected, $productA->fresh()->thumbnailImage());
        $this->assertSame($expected, $productB->fresh()->thumbnailImage());
    }

    // ── Admin: the media library page reuses without uploading ─────────────

    public function test_admin_can_reuse_an_image_from_the_media_library(): void
    {
        $owner = Product::factory()->create(['name' => 'Source Guitar']);
        $media = $owner->addMedia(UploadedFile::fake()->image('library.jpg', 120, 120))->toMediaCollection('gallery');

        $target = Product::factory()->create(['name' => 'Target Guitar']);
        $filesOnDisk = Storage::disk('public')->allFiles();

        Livewire::actingAs($this->admin, 'admin')
            ->test(ManageMediaLibrary::class)
            ->assertOk()
            ->callAction(
                TestAction::make('reuse')->table($media),
                data: [
                    'target_type' => Product::class,
                    'record_id' => $target->getKey(),
                    'collection' => 'gallery',
                ],
            )
            ->assertHasNoErrors();

        $this->assertSame([$media->getUrl()], $target->fresh()->gallery_urls);
        $this->assertSame(1, Media::query()->where('shared_path', $media->sharingBasePath())->count());
        $this->assertSame($filesOnDisk, Storage::disk('public')->allFiles(), 'The admin action must not upload a copy either.');
    }
}
