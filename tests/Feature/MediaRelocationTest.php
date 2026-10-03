<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Services\MediaRelocationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\Concerns\IsolatesMediaDisks;
use Tests\TestCase;

/**
 * `php artisan media:relocate` repairs media that earlier panel uploads left on
 * the private default disk (storefront 403/404, previews stuck loading).
 */
class MediaRelocationTest extends TestCase
{
    use IsolatesMediaDisks;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->isolateMediaDisks();
    }

    /** A media item stored the way the bug stored it: on the private `local` disk. */
    private function legacyMedia(string $name = 'legacy.jpg', string $disk = 'local', ?string $conversionsDisk = null): Media
    {
        $adder = Product::factory()->create()
            ->addMedia(UploadedFile::fake()->image($name, 120, 120));

        if ($conversionsDisk !== null) {
            $adder->storingConversionsOnDisk($conversionsDisk);
        }

        return $adder->toMediaCollection('gallery', $disk);
    }

    /** @return list<string> */
    private function filesOn(string $disk): array
    {
        return Storage::disk($disk)->allFiles();
    }

    public function test_it_moves_original_and_conversions_from_the_private_disk_to_the_media_disk(): void
    {
        $media = $this->legacyMedia();
        $original = $media->getPathRelativeToRoot();
        $conversion = $media->getPathRelativeToRoot('thumb-webp');

        // Legacy state: everything on the private disk, nothing public.
        Storage::disk('local')->assertExists($original);
        Storage::disk('local')->assertExists($conversion);
        $this->assertSame([], $this->filesOn('public'));

        $this->artisan('media:relocate')
            ->expectsOutputToContain("Moved media #{$media->id}")
            ->assertSuccessful();

        $media->refresh();
        $this->assertSame('public', $media->disk);
        $this->assertSame('public', $media->conversions_disk);

        Storage::disk('public')->assertExists($original);
        Storage::disk('public')->assertExists($conversion);
        $this->assertSame([], $this->filesOn('local'), 'Old files and the per-media folder are removed after the move.');
        $this->assertSame([], Storage::disk('local')->directories(), 'No empty per-media folder is left behind.');

        // What the storefront links to is now a plain public URL backed by a real file.
        $this->assertSame('/storage/'.$original, $media->getUrl());
    }

    public function test_dry_run_reports_but_changes_nothing(): void
    {
        $media = $this->legacyMedia();

        $this->artisan('media:relocate', ['--dry-run' => true])
            ->expectsOutputToContain("Would move media #{$media->id}")
            ->assertSuccessful();

        $this->assertSame('local', $media->fresh()->disk);
        Storage::disk('local')->assertExists($media->getPathRelativeToRoot());
        $this->assertSame([], $this->filesOn('public'));
    }

    public function test_it_is_idempotent_and_leaves_correctly_placed_media_alone(): void
    {
        $legacy = $this->legacyMedia('old.jpg');
        $good = $this->legacyMedia('new.jpg', disk: 'public');

        $this->artisan('media:relocate')->assertSuccessful();
        $filesAfterFirstRun = $this->filesOn('public');

        $this->artisan('media:relocate')
            ->expectsOutputToContain('Nothing to move')
            ->assertSuccessful();

        $this->assertSame($filesAfterFirstRun, $this->filesOn('public'));
        $this->assertSame('public', $legacy->fresh()->disk);
        $this->assertSame('public', $good->fresh()->disk);
    }

    public function test_conversions_stored_on_another_disk_are_moved_independently(): void
    {
        $media = $this->legacyMedia('split.jpg', disk: 'public', conversionsDisk: 'local');
        $conversion = $media->getPathRelativeToRoot('thumb-webp');

        $this->assertSame('public', $media->disk);
        $this->assertSame('local', $media->conversions_disk);
        Storage::disk('local')->assertExists($conversion);

        $this->artisan('media:relocate')->assertSuccessful();

        $media->refresh();
        $this->assertSame('public', $media->conversions_disk);
        Storage::disk('public')->assertExists($media->getPathRelativeToRoot());
        Storage::disk('public')->assertExists($conversion);
        $this->assertSame([], $this->filesOn('local'));
    }

    public function test_a_missing_original_is_reported_and_its_row_is_not_touched(): void
    {
        $broken = $this->legacyMedia('gone.jpg');
        $healthy = $this->legacyMedia('fine.jpg');
        Storage::disk('local')->delete($broken->getPathRelativeToRoot());

        $this->artisan('media:relocate')
            ->expectsOutputToContain("FAILED media #{$broken->id}")
            ->assertFailed();

        $this->assertSame('local', $broken->fresh()->disk, 'A row whose file is missing must stay as it is for investigation.');
        $this->assertSame('public', $healthy->fresh()->disk, 'One bad row must not block the others.');
    }

    public function test_diagnostics_warn_when_the_media_disk_is_not_publicly_readable(): void
    {
        $service = app(MediaRelocationService::class);
        $this->assertTrue($service->diagnostics()['public']);

        config(['media-library.disk_name' => 'local']); // a misconfigured MEDIA_DISK

        $this->assertFalse($service->diagnostics()['public']);
        $this->artisan('media:relocate')
            ->expectsOutputToContain('is not publicly readable')
            ->assertSuccessful();
    }

    public function test_diagnostics_flag_a_missing_storage_link_and_recognise_a_working_one(): void
    {
        $link = $this->mediaRoot.'/web/storage';
        File::ensureDirectoryExists(dirname($link));
        config(['filesystems.links' => [$link => $this->mediaRoot.'/public']]);

        $service = app(MediaRelocationService::class);

        $this->assertFalse($service->diagnostics()['links'][0]['ok']);
        $this->artisan('media:relocate')->expectsOutputToContain('MISSING — run: php artisan storage:link');

        symlink($this->mediaRoot.'/public', $link);

        $this->assertTrue($service->diagnostics()['links'][0]['ok']);
        $this->assertSame('public', $service->diagnostics()['disk']);
        $this->assertSame('/storage', $service->diagnostics()['url']);
    }
}
