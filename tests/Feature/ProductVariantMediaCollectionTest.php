<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\MediaCollection;
use Tests\TestCase;

/**
 * Regression: ProductVariant::registerMediaCollections() used to chain
 * Filament FileUpload methods (multiple/image/maxFiles/acceptAllMimeTypes)
 * onto Spatie's MediaCollection object. Those methods do not exist there,
 * so EVERY save of a product with a variant image upload fatals with
 * "Call to undefined method ...::multiple()" and the admin upload failed.
 */
class ProductVariantMediaCollectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_variant_media_collections_register_without_error(): void
    {
        $variant = ProductVariant::factory()->create();

        // Exercising the exact call sites medialibrary + Filament hit during
        // an admin upload (getRegisteredMediaCollections, FileAdder guards,
        // conversion registration).
        $collections = $variant->getRegisteredMediaCollections();

        $this->assertTrue(
            $collections->contains(fn (MediaCollection $collection): bool => $collection->name === 'variant_gallery'),
            'The variant_gallery collection must be registered on ProductVariant.',
        );

        $this->assertNotNull($variant->getMediaCollection('variant_gallery'));

        $variant->registerAllMediaConversions();
        $this->assertTrue(true, 'registerAllMediaConversions() must not fatal.');
    }

    public function test_variant_image_can_be_attached_like_admin_upload(): void
    {
        $variant = ProductVariant::factory()->create();

        // Mirrors the Filament save path: FileUpload → addMedia…toMediaCollection().
        $media = $variant
            ->addMedia(UploadedFile::fake()->image('guitar.jpg', 24, 24))
            ->toMediaCollection('variant_gallery');

        $this->assertSame('variant_gallery', $media->collection_name);
        $this->assertCount(1, $variant->fresh()->getMedia('variant_gallery'));
        Storage::disk('public')->assertExists($media->id.'/'.$media->file_name);
    }
}
