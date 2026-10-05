<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Media;
use App\Services\MediaReuseService;
use App\Support\MediaDisk;
use Illuminate\Console\Command;

/**
 * Store every image once (docs/media-reuse.md).
 *
 * Finds byte-identical originals (sha256) that are still stored twice — the
 * result of uploading the same picture in several places before media reuse
 * existed — keeps the oldest copy as the file owner, re-points every usage at
 * it, and removes the extra copies (original + conversions + responsive
 * images). Runs the checksum pass first for any file owner that has none yet.
 *
 * Nothing is deleted unless the usage rows were re-pointed successfully, and
 * MediaFileObserver keeps any file that is still in use — so the command is
 * safe to re-run and safe to interrupt.
 */
final class DedupeMedia extends Command
{
    protected $signature = 'media:dedupe
        {--dry-run : Report what would be merged without changing anything}
        {--disk= : Only consider one disk (e.g. public)}
        {--limit=500 : Maximum number of duplicate groups to process}
        {--no-hash : Skip the checksum pass (only use already stored checksums)}';

    protected $description = 'Merge byte-identical duplicate images into shared media rows (store each image once)';

    public function handle(MediaReuseService $reuse): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $disk = $this->option('disk');
        $disk = is_string($disk) && trim($disk) !== '' ? trim($disk) : null;
        $limit = max(1, (int) $this->option('limit'));

        $this->components->info('Media dedupe — '.($dryRun ? 'report only (dry run)' : 'merging duplicates'));

        if (! $this->hashes($reuse, $disk, $dryRun, (bool) $this->option('no-hash'))) {
            return self::FAILURE;
        }

        $report = function (string $level, string $message): void {
            match ($level) {
                'failed' => $this->components->error($message),
                'merged' => $this->line($message),
                'skipped' => $this->components->warn($message),
                default => $this->line($message),
            };
        };

        $result = $reuse->mergeDuplicates($dryRun, $disk, $limit, $report);

        $this->newLine();

        if ($result['groups'] === 0) {
            $this->components->info('No duplicate images found. Every stored image is unique.');

            return self::SUCCESS;
        }

        $verb = $dryRun ? 'Would merge' : 'Merged';

        $this->components->twoColumnDetail(
            $verb,
            "{$result['merged']} duplicate copy/copies in {$result['groups']} group(s) · {$result['repointed']} usage row(s) re-pointed",
        );

        if ($disk === null) {
            $this->line('  Tip: a specific disk only → php artisan media:dedupe --disk=public');
        }

        if (! $dryRun) {
            $this->line('  Reused rows are verified by: php artisan media:doctor');
        }

        if ($result['failed'] > 0) {
            $this->components->error("{$result['failed']} group item(s) failed — the rest were merged; re-run after fixing.");

            return self::FAILURE;
        }

        $this->components->info($dryRun
            ? 'Dry run — nothing was changed. Re-run without --dry-run to merge.'
            : 'Duplicates merged. Each image is now stored once and reused everywhere.');

        return self::SUCCESS;
    }

    /**
     * Give every file owner a checksum so duplicates can be found. Reads each
     * file once (Cloudinary rows are downloaded once — hence the warning).
     */
    private function hashes(MediaReuseService $reuse, ?string $disk, bool $dryRun, bool $skip): bool
    {
        if ($skip) {
            return true;
        }

        $query = Media::query()
            ->fileOwners()
            ->where(function ($builder): void {
                $builder->whereNull('checksum')->orWhere('checksum', '');
            })
            ->when($disk !== null, fn ($builder) => $builder->where('disk', $disk));

        $pending = (int) (clone $query)->count();

        if ($pending === 0) {
            $this->components->twoColumnDetail('Checksums', 'already complete');

            return true;
        }

        $cloudPending = (int) (clone $query)->where('disk', MediaDisk::CLOUDINARY)->count();

        if ($cloudPending > 0) {
            $this->components->warn("{$cloudPending} Cloudinary image(s) will be read once over the network to hash them.");
        }

        if ($dryRun) {
            $this->components->twoColumnDetail('Checksums', "{$pending} image(s) without a hash");

            if ($pending > 0) {
                $this->components->warn('Images without a hash cannot be compared yet — run once without --dry-run (hashing itself deletes nothing), then use --dry-run to review the merge.');
            }

            return true;
        }

        $hashed = 0;
        $failed = 0;

        $query->chunkById(200, function ($rows) use ($reuse, &$hashed, &$failed): void {
            foreach ($rows as $media) {
                $checksum = $reuse->checksum($media);

                if ($checksum === null) {
                    $failed++;

                    continue;
                }

                $hashed++;
            }
        });

        $this->components->twoColumnDetail('Checksums', "{$hashed} image(s) hashed".($failed > 0 ? " · {$failed} unreadable" : ''));

        if ($failed > 0) {
            $this->components->warn('Unreadable files were skipped — run php artisan media:doctor to see why.');
        }

        return true;
    }
}
