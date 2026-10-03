<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Product;
use App\Support\ImageStore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

/**
 * Puts product images into the simple shape: file in `public/uploads/products`,
 * URL on the product row (docs/media-architecture.md §7).
 *
 * It does two jobs, both safe to re-run:
 *
 *  1. copies images that are still only in the media library onto the uploads
 *     disk and fills `image` / `gallery` / `og_image` (products that already
 *     have a usable image are left alone — the old media files are kept), and
 *  2. reports every product whose stored image URL does not resolve to a file
 *     on disk, which is the "the link is wrong" check.
 */
final class MigrateProductImages extends Command
{
    protected $signature = 'product-images:migrate
                            {--dry-run : Show what would change without writing anything}';

    protected $description = 'Copy product media into public/uploads and store the image URL on the product row';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->info('Product images');
        $this->line('  Uploads disk:  '.ImageStore::DISK.' → '.$this->diskRoot());
        $this->line('  Public URL:    '.ImageStore::baseUrl().'/…');

        if (! is_dir($this->diskRoot())) {
            $this->warn('  The uploads folder does not exist yet — it is created with the first upload.');
        }

        $copied = 0;
        $skipped = 0;
        $missingFiles = [];

        Product::query()->withTrashed()->with('media')->orderBy('id')->each(function (Product $product) use ($dryRun, &$copied, &$skipped, &$missingFiles): void {
            $gallery = $product->getMedia('gallery');
            $og = $product->getFirstMedia('og');

            $needsImages = $product->image === null || ! ImageStore::exists($product->image);
            $urls = [];

            if ($needsImages) {
                foreach ($gallery as $media) {
                    $url = $this->copyToUploads($media, $dryRun);

                    if ($url !== null) {
                        $urls[] = $url;
                    }
                }

                if ($urls !== []) {
                    if (! $dryRun) {
                        $product->image = $urls[0];
                        $product->gallery = array_slice($urls, 1);

                        if ($product->og_image === null && $og !== null) {
                            $product->og_image = $this->copyToUploads($og, $dryRun);
                        }

                        $product->save();
                    }

                    $copied++;
                    $this->line(sprintf(
                        '  %s product #%d "%s" → %s%s',
                        $dryRun ? 'Would copy' : 'Copied',
                        $product->id,
                        $product->slug,
                        $urls[0],
                        count($urls) > 1 ? ' (+'.(count($urls) - 1).' more)' : '',
                    ));

                    return;
                }
            }

            $skipped++;

            foreach ($product->imageUrls() as $url) {
                if (! ImageStore::exists($url)) {
                    $missingFiles[] = ['id' => $product->id, 'slug' => $product->slug, 'url' => $url];
                }
            }
        });

        $this->newLine();
        $this->info(sprintf(
            '%s %d product(s); %d already had an image (or none to copy).',
            $dryRun ? 'Would copy' : 'Copied',
            $copied,
            $skipped,
        ));

        if ($missingFiles !== []) {
            $this->newLine();
            $this->warn('These products point at an image that is not on disk — re-upload it in the panel:');

            foreach ($missingFiles as $row) {
                $this->line("  #{$row['id']} {$row['slug']} → {$row['url']}");
            }

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /** Copy one media item's original file onto the uploads disk and return its URL. */
    private function copyToUploads(Media $media, bool $dryRun): ?string
    {
        try {
            $source = Storage::disk($media->disk);
            // Relative to the disk root — getPath() is the absolute filesystem path.
            $path = $media->getPathRelativeToRoot();

            if (! $source->exists($path)) {
                $this->warn("  media #{$media->id} has no file at {$media->disk}:{$path} — skipped");

                return null;
            }

            // The media id keeps the name unique even when two rows were
            // uploaded with the same original file name.
            $extension = pathinfo($media->file_name, PATHINFO_EXTENSION);
            $target = 'products/'.$media->id.'-'.substr(preg_replace('/[^A-Za-z0-9._-]/', '-', pathinfo($media->file_name, PATHINFO_FILENAME)), 0, 60)
                .($extension === '' ? '' : '.'.$extension);

            if ($dryRun) {
                return ImageStore::url($target);
            }

            ImageStore::disk()->put($target, $source->get($path), 'public');

            return ImageStore::url($target);
        } catch (Throwable $exception) {
            $this->warn("  media #{$media->id} could not be copied: {$exception->getMessage()}");

            return null;
        }
    }

    private function diskRoot(): string
    {
        return (string) config('filesystems.disks.'.ImageStore::DISK.'.root');
    }
}
