<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Category;
use App\Models\HeroSlide;
use App\Models\HomepageBlock;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

/**
 * Explains — and where safe, repairs — why admin-uploaded images show up
 * broken (404) in the panel or on the storefront.
 *
 * The failure mode this exists for: `/storage/...` is answered by whatever web
 * layer owns that path. With the `public/storage` symlink missing and the
 * PRIVATE disk serving `/storage` (Laravel registers that route for any local
 * disk with `serve => true`, and it demands a signature), every media URL 404s
 * although the upload itself succeeded — the panel preview breaks right after
 * save and the storefront shows broken images. `config/filesystems.php` now
 * pins `/storage` to the media disk; this command proves the whole chain on the
 * machine where it hurts.
 *
 * Read-only by default. `--fix` performs only idempotent repairs (symlink,
 * relocation, URL-column resync, stale conversion flags) and never deletes an
 * original.
 *
 * @see docs/media-architecture.md → M-1, M-2, M-7
 */
final class MediaDoctor extends Command
{
    protected $signature = 'media:doctor
        {--fix : Apply the safe, idempotent repairs (never deletes an original)}
        {--limit=1000 : How many rows to inspect per check (raise it on large catalogues)}';

    protected $description = 'Diagnose (and with --fix, repair) why uploaded images 404 in the panel or on the site';

    /** Must match SyncMediaUrls::TARGETS. */
    private const TARGETS = [Product::class, ProductVariant::class, Brand::class, Category::class, HeroSlide::class, HomepageBlock::class];

    private int $failures = 0;

    private int $warnings = 0;

    public function handle(): int
    {
        $fix = (bool) $this->option('fix');
        $mediaDisk = (string) config('media-library.disk_name');
        $limit = max(1, (int) $this->option('limit'));

        $this->newLine();
        $this->components->info('Media doctor — '.($fix ? 'diagnose + repair' : 'diagnose (add --fix to repair)'));

        // Nothing downstream can be trusted (or even read) without the disk.
        if (! $this->checkConfigContract($mediaDisk)) {
            $this->newLine();
            $this->components->twoColumnDetail('Result', '<fg=red>cannot continue</>');

            return self::FAILURE;
        }

        $this->checkStorageOwnership($mediaDisk);
        $this->checkSymlink($fix);
        $this->checkMediaRows($mediaDisk, $fix, $limit);
        $this->checkUrlColumns($mediaDisk, $fix, $limit);

        $this->newLine();
        $this->components->twoColumnDetail('Result', match (true) {
            $this->failures > 0 => "<fg=red>{$this->failures} problem(s)</>",
            $this->warnings > 0 => "<fg=yellow>{$this->warnings} warning(s)</>",
            default => '<fg=green>healthy</>',
        });

        if ($this->failures > 0 && ! $fix) {
            $this->components->warn('Re-run with --fix to repair the safe items, then reload the panel.');
        }

        return $this->failures > 0 ? self::FAILURE : self::SUCCESS;
    }

    /** @return bool false when the media disk is missing entirely (stop there). */
    private function checkConfigContract(string $mediaDisk): bool
    {
        $diskConfig = (array) config("filesystems.disks.{$mediaDisk}", []);

        if ($diskConfig === []) {
            $this->reportFail("Media disk [{$mediaDisk}] is not configured", 'Check MEDIA_DISK in .env, then php artisan config:clear');

            return false;
        }

        $filamentDisk = (string) config('filament.default_filesystem_disk');

        if ($filamentDisk !== $mediaDisk) {
            $this->reportFail(
                "Panel uploads use disk [{$filamentDisk}] but media is stored on [{$mediaDisk}]",
                'One setting must drive both — docs/media-architecture.md → M-1',
            );
        } else {
            $this->reportOk("Panel uploads and Media Library both use [{$mediaDisk}]");
        }

        if (($diskConfig['visibility'] ?? null) !== 'public') {
            $this->reportFail("Disk [{$mediaDisk}] is not publicly readable", "Set 'visibility' => 'public' (M-1)");
        }

        // Livewire writes pre-validation temp uploads to the DEFAULT disk
        // (livewire.temporary_file_upload.disk is unset). If that is the
        // web-served media disk they end up inside the public root.
        $defaultDisk = (string) config('filesystems.default');

        if ($defaultDisk === $mediaDisk) {
            $this->reportWarn(
                "The default disk [{$defaultDisk}] is the publicly served media disk",
                'Keep FILESYSTEM_DISK=local: pre-validation temp uploads must not land in the web-served root',
            );
        }

        $url = (string) ($diskConfig['url'] ?? '');

        if (! str_starts_with($url, '/')) {
            $this->reportWarn(
                "Disk [{$mediaDisk}] URL [".($url ?: 'empty').'] is not host-relative',
                'Leave MEDIA_URL empty to get /storage URLs (M-2)',
            );
        }

        return true;
    }

    /**
     * Laravel registers `GET /storage/{path}` for every local disk with
     * `serve => true`. Exactly one disk may own that URI, and it must be the
     * media disk: on the private disk that route demands a signature (403 in
     * dev / 404 in production) and reads from storage/app/private — which is
     * exactly the "images 404 after save" bug when the symlink is missing.
     */
    private function checkStorageOwnership(string $mediaDisk): void
    {
        $owners = [];

        foreach ((array) config('filesystems.disks', []) as $name => $config) {
            if (($config['driver'] ?? null) !== 'local' || ($config['serve'] ?? false) !== true) {
                continue;
            }

            $path = isset($config['url'])
                ? (string) parse_url((string) $config['url'], PHP_URL_PATH)
                : '/storage';

            $uri = rtrim($path === '' ? '/storage' : $path, '/');

            $owners[$uri][] = (string) $name;
        }

        if ($owners === []) {
            $this->reportWarn(
                'No disk answers the media URL path',
                'Set serve => true on the media disk so /storage works even without the symlink (M-2)',
            );

            return;
        }

        foreach ($owners as $uri => $names) {
            if (count($names) > 1) {
                $this->reportFail(
                    'Disks ['.implode(', ', $names)."] all serve [{$uri}]",
                    'Laravel refuses to boot with two served disks on one URI — only the media disk may be served (M-2)',
                );

                continue;
            }

            if ($names[0] !== $mediaDisk) {
                $this->reportFail(
                    "The private disk [{$names[0]}] serves [{$uri}] instead of [{$mediaDisk}]",
                    'serve => true on the media disk, serve => false on the private disk (M-2)',
                );

                continue;
            }

            $this->reportOk("[{$mediaDisk}] owns [{$uri}] when no static file answers first");
        }
    }

    private function checkSymlink(bool $fix): void
    {
        $link = public_path('storage');
        $target = storage_path('app/public');

        if ($this->symlinkIsHealthy($link, $target)) {
            $this->reportOk('public/storage symlink points at storage/app/public (fast static serving)');

            return;
        }

        if ($fix) {
            $this->call('storage:link');

            if ($this->symlinkIsHealthy($link, $target)) {
                $this->reportOk('public/storage symlink created');

                return;
            }
        }

        $this->reportWarn(
            'public/storage symlink is missing or points elsewhere',
            'Images still resolve through the serve route, but each one is streamed by PHP. Fix: php artisan storage:link',
        );
    }

    /**
     * A symlink counts as healthy only when both ends resolve — a dangling link
     * (realpath false) would otherwise compare equal to a missing target.
     */
    private function symlinkIsHealthy(string $link, string $target): bool
    {
        if (! is_link($link)) {
            return false;
        }

        $resolvedLink = realpath($link);
        $resolvedTarget = realpath($target);

        return $resolvedLink !== false && $resolvedTarget !== false && $resolvedLink === $resolvedTarget;
    }

    /**
     * Per-row file checks: originals that are gone, plus conversions the
     * database claims exist but that are missing on disk — the latter is a real
     * 404 source even when the disk, symlink and columns are all healthy.
     */
    private function checkMediaRows(string $mediaDisk, bool $fix, int $limit): void
    {
        /** @var class-string<Media> $mediaModel */
        $mediaModel = (string) config('media-library.media_model', Media::class);

        $misplaced = (int) $mediaModel::query()
            ->where(function ($query) use ($mediaDisk): void {
                $query->where('disk', '!=', $mediaDisk)
                    ->orWhere(fn ($inner) => $inner->whereNotNull('conversions_disk')->where('conversions_disk', '!=', $mediaDisk));
            })
            ->count();

        if ($misplaced > 0) {
            $this->reportFail(
                "{$misplaced} media row(s) point at a disk other than [{$mediaDisk}]",
                $fix ? 'relocating now' : 'php artisan media:relocate (or --fix)',
            );

            if ($fix) {
                $this->call('media:relocate');
            }
        } else {
            $this->reportOk("Every media row lives on [{$mediaDisk}]");
        }

        $scanned = 0;
        $missingOriginals = [];
        $staleConversions = 0;
        $storage = Storage::disk($mediaDisk);

        $mediaModel::query()
            ->where('disk', $mediaDisk)
            ->chunkById(200, function ($rows) use (&$scanned, &$missingOriginals, &$staleConversions, $storage, $fix, $limit): bool {
                foreach ($rows as $media) {
                    if ($scanned >= $limit) {
                        return false;
                    }

                    $scanned++;

                    try {
                        if (! $storage->exists($media->getPathRelativeToRoot())) {
                            $missingOriginals[] = (int) $media->getKey();

                            continue;
                        }

                        foreach ($media->getGeneratedConversions()->keys() as $key) {
                            $conversion = (string) $key;

                            if ($storage->exists($media->getPathRelativeToRoot($conversion))) {
                                continue;
                            }

                            $staleConversions++;

                            if ($fix) {
                                $remaining = (array) $media->generated_conversions;
                                unset($remaining[$conversion]);
                                // A normal save (not quiet) so MediaUrlObserver re-resolves the
                                // URL columns — the WebP URL it stored no longer exists.
                                $media->generated_conversions = $remaining;
                                $media->save();
                            }
                        }
                    } catch (Throwable) {
                        // An unreadable disk is already reported by the config checks.
                    }
                }

                return true;
            });

        $this->components->twoColumnDetail('Media files inspected', (string) $scanned);

        if ($staleConversions > 0) {
            $this->reportFail(
                "{$staleConversions} conversion flag(s) claim a WebP file that is missing",
                $fix ? 'cleared — the original is served again (regenerate later if wanted)' : 'php artisan media:doctor --fix',
            );
        }

        if ($missingOriginals !== []) {
            $this->reportFail(
                count($missingOriginals).' original file(s) are missing on disk (ids: '.implode(', ', array_slice($missingOriginals, 0, 5)).')',
                'The row is fine, the file is gone — restore from backup or re-upload that image',
            );
        } elseif ($scanned > 0) {
            $this->reportOk('Every inspected original exists on disk');
        }
    }

    /**
     * The stored columns are what the panel and the storefront actually render
     * (M-7), so they are also a *web* concern: a column that points at a file
     * which is gone 404s even when the disk, the symlink and the files are all
     * fine — and a row that has media but no resolved URL at all means the
     * backfill never ran for it.
     *
     * Read-only unless `--fix`, which re-runs `media:sync-urls` (a full pass:
     * it repairs both the stale and the missing values).
     */
    private function checkUrlColumns(string $mediaDisk, bool $fix, int $limit): void
    {
        $storage = Storage::disk($mediaDisk);
        $inspected = 0;
        $unresolved = 0;
        /** @var list<string> $stale */
        $stale = [];
        /** @var list<string> $pendingTables */
        $pendingTables = [];

        foreach (self::TARGETS as $class) {
            $instance = new $class;
            /** @var list<string> $columns */
            $columns = array_keys($instance->resolvedMediaUrls());

            if (! Schema::hasColumns($instance->getTable(), $columns)) {
                $pendingTables[] = $instance->getTable();

                continue;
            }

            $unresolved += (int) $this->rows($class)
                ->whereHas('media')
                ->where(function (Builder $query) use ($columns): void {
                    foreach ($columns as $column) {
                        $query->whereNull($column);
                    }
                })
                ->count();

            $this->rows($class)
                ->where(function (Builder $query) use ($columns): void {
                    foreach ($columns as $column) {
                        $query->orWhereNotNull($column);
                    }
                })
                ->chunkById(200, function ($rows) use (&$stale, &$inspected, $columns, $storage, $limit): bool {
                    foreach ($rows as $row) {
                        if ($inspected >= $limit) {
                            return false;
                        }

                        $inspected++;

                        foreach ($columns as $column) {
                            foreach ((array) $row->{$column} as $url) {
                                // A CDN / absolute URL is not ours to verify, and the
                                // product hero column may hold a committed /images/... path.
                                if (! is_string($url) || ! str_starts_with($url, '/storage/')) {
                                    continue;
                                }

                                $path = rawurldecode(Str::after($url, '/storage/'));

                                try {
                                    $exists = $storage->exists($path);
                                } catch (Throwable) {
                                    continue;
                                }

                                if (! $exists) {
                                    $stale[] = class_basename($row).'#'.$row->getKey().' → '.$column;
                                }
                            }
                        }
                    }

                    return true;
                });
        }

        $this->components->twoColumnDetail('Stored image URLs checked', (string) $inspected);

        if ($pendingTables !== []) {
            $this->reportWarn(
                'URL column migration is pending on ['.implode(', ', $pendingTables).'] (falling back to Media Library)',
                'Run: php artisan migrate && php artisan media:sync-urls --only-missing',
            );

            if (count($pendingTables) === count(self::TARGETS)) {
                return;
            }
        }

        if ($stale === [] && $unresolved === 0) {
            $this->reportOk('Every stored image URL points at a file that exists');

            return;
        }

        if ($stale !== []) {
            $this->reportFail(
                count($stale).' stored image URL(s) point at a file that is gone ('.implode(', ', array_slice($stale, 0, 5)).')',
                $fix ? 'resyncing — if a URL does not change, that file must be restored or the image re-uploaded' : 'php artisan media:sync-urls (or --fix)',
            );
        }

        if ($unresolved > 0) {
            $this->reportWarn(
                "{$unresolved} row(s) have media but no stored image URL yet",
                'php artisan media:sync-urls --only-missing (or --fix)',
            );
        }

        if ($fix) {
            $this->call('media:sync-urls');
        }
    }

    /**
     * The base query for a target model, including soft-deleted rows exactly
     * like `media:sync-urls` does (their media columns are still rendered in
     * the admin's trashed view).
     *
     * @param  class-string<Model>  $class
     * @return Builder<Model>
     */
    private function rows(string $class): Builder
    {
        /** @var Builder<Model> $query */
        $query = $class::query();

        if (in_array(SoftDeletes::class, class_uses_recursive($class), true)) {
            $query->withTrashed();
        }

        return $query;
    }

    private function reportOk(string $message): void
    {
        $this->components->twoColumnDetail('<fg=green>OK</>', $message);
    }

    private function reportWarn(string $message, string $hint): void
    {
        $this->warnings++;
        $this->components->twoColumnDetail('<fg=yellow>WARN</>', $message);
        $this->components->bulletList(['→ '.$hint]);
    }

    private function reportFail(string $message, string $hint): void
    {
        $this->failures++;
        $this->components->twoColumnDetail('<fg=red>FAIL</>', $message);
        $this->components->bulletList(['→ '.$hint]);
    }
}
