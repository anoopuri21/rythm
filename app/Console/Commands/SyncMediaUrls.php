<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Contracts\HasResolvedMediaUrls;
use App\Models\HeroSlide;
use App\Models\HomepageBlock;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Throwable;

/**
 * Backfill / repair the persisted media-URL columns (docs/media-architecture.md → M-7).
 *
 * The MediaUrlObserver keeps these columns fresh for every normal app write, so
 * this command is for rows created before the columns existed, plus repairs
 * after a bulk media operation done outside the app (raw SQL, rsync, an
 * external tool). Re-running is always safe: it only writes changed values.
 */
final class SyncMediaUrls extends Command
{
    protected $signature = 'media:sync-urls
        {--dry-run : Report what would change without writing}
        {--only-missing : Only consider rows with at least one unresolved (NULL) URL column}
        {--chunk=200 : Rows per batch}';

    protected $description = 'Resolve Media Library URLs into the stored DB columns (backfill/repair)';

    /** @var list<class-string<Model&HasResolvedMediaUrls>> */
    private const TARGETS = [
        Product::class,
        ProductVariant::class,
        Brand::class,
        Category::class,
        HeroSlide::class,
        HomepageBlock::class,
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $onlyMissing = (bool) $this->option('only-missing');
        $chunk = max(1, (int) $this->option('chunk'));
        $failures = 0;

        foreach (self::TARGETS as $class) {
            $columns = array_keys((new $class)->resolvedMediaUrls());

            try {
                $result = $this->syncModel($class, $columns, $dryRun, $onlyMissing, $chunk);
            } catch (Throwable $exception) {
                $failures++;
                $this->components->error(class_basename($class).': '.$exception->getMessage());

                continue;
            }

            $this->components->twoColumnDetail(
                class_basename($class),
                sprintf('%d scanned · %d %s · %d already current', $result['scanned'], $result['changed'], $dryRun ? 'would change' : 'updated', $result['unchanged']),
            );
        }

        if ($failures > 0) {
            $this->components->error("{$failures} model(s) failed — nothing was deleted; fix the error and re-run.");

            return self::FAILURE;
        }

        $this->components->info($dryRun ? 'Dry run — no rows were written.' : 'Media URLs are in sync.');

        return self::SUCCESS;
    }

    /**
     * @param  class-string<Model&HasResolvedMediaUrls>  $class
     * @param  list<string>  $columns
     * @return array{scanned:int, changed:int, unchanged:int}
     */
    private function syncModel(string $class, array $columns, bool $dryRun, bool $onlyMissing, int $chunk): array
    {
        $scanned = 0;
        $changed = 0;

        $this->query($class, $columns, $onlyMissing)
            ->chunkById($chunk, function ($models) use ($dryRun, &$scanned, &$changed): void {
                foreach ($models as $model) {
                    /** @var Model&HasResolvedMediaUrls $model */
                    $scanned++;

                    if ($model->resolvedMediaUrlChanges() === []) {
                        continue;
                    }

                    $changed++;

                    if (! $dryRun) {
                        $model->syncResolvedMediaUrls();
                    }
                }
            }, 'id');

        return ['scanned' => $scanned, 'changed' => $changed, 'unchanged' => $scanned - $changed];
    }

    /**
     * @param  class-string<Model&HasResolvedMediaUrls>  $class
     * @param  list<string>  $columns
     * @return Builder<Model>
     */
    private function query(string $class, array $columns, bool $onlyMissing): Builder
    {
        /** @var Builder<Model> $query */
        $query = $class::query();

        if (in_array(SoftDeletes::class, class_uses_recursive($class), true)) {
            $query->withTrashed();
        }

        if ($onlyMissing) {
            $query->where(function (Builder $missing) use ($columns): void {
                foreach ($columns as $column) {
                    $missing->orWhereNull($column);
                }
            });
        }

        return $query;
    }
}
