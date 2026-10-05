import test from 'node:test';
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';

const read = (path) => readFile(new URL(`../../${path}`, import.meta.url), 'utf8');

/**
 * Static guards for media reuse (docs/media-reuse.md → M-10).
 *
 * The whole point: an image used in several places is stored ONCE. That only
 * holds while (a) a custom path generator resolves shared rows into the owner's
 * file, (b) the media observer never deletes a file that is still used and
 * never renames a file a shared row does not own, and (c) reused rows generate
 * no conversions of their own.
 */
test('the media table gains reference columns (nullable + additive)', async () => {
    const migration = await read('database/migrations/2026_10_06_000001_add_shared_path_to_media_table.php');

    assert.match(migration, /\$table->string\('shared_path'\)->nullable\(\)->after\('disk'\)->index\(\)/);
    assert.match(migration, /\$table->unsignedBigInteger\('source_media_id'\)->nullable\(\)/);
    assert.match(migration, /\$table->string\('checksum', 64\)->nullable\(\)/);
    assert.match(migration, /Schema::hasColumn\('media', 'shared_path'\)/);
});

test('media-library points at the app path generator and media observer', async () => {
    const config = await read('config/media-library.php');
    const backslash = String.fromCharCode(92);

    assert.ok(
        config.includes("'path_generator' => App" + backslash + "Support" + backslash + "MediaPathGenerator::class"),
        'config/media-library.php must use the app path generator',
    );
    assert.ok(
        config.includes("'media_observer' => App" + backslash + "Observers" + backslash + "MediaFileObserver::class"),
        'config/media-library.php must use the app media observer',
    );
});

test('shared rows resolve into the owner file through the path generator', async () => {
    const generator = await read('app/Support/MediaPathGenerator.php');

    assert.match(generator, /class MediaPathGenerator extends DefaultPathGenerator/);
    assert.match(generator, /public function getBasePath\(Media \$media\): string/);
    assert.match(generator, /return \$shared !== '' \? \$shared : parent::getBasePath\(\$media\);/);
    assert.match(generator, /public static function ownerKeyFromBasePath/);
});

test('the media model exposes reuse helpers and never resolves a shared row to its own path', async () => {
    const media = await read('app/Models/Media.php');

    assert.match(media, /public function isShared\(\): bool/);
    assert.match(media, /public function sharingBasePath\(\): string/);
    assert.match(media, /public function sharedOwner\(\): BelongsTo/);
    assert.match(media, /public function usages\(\): HasMany/);
    assert.match(media, /public function scopeResolvingTo\(Builder \$query, string \$basePath\): Builder/);
    // a class cannot declare an instance and a static method under one name — the
    // query helper must stay distinguishable from sharingBasePath()
    assert.match(media, /public static function rowsResolvingTo\(string \$basePath, \?int \$exceptId = null\): Builder/);
    assert.doesNotMatch(media, /public static function sharingBasePath\(/);
    assert.match(media, /public function scopeFileOwners\(Builder \$query\): Builder/);
});

test('the file observer protects shared files and mirrors conversions', async () => {
    const observer = await read('app/Observers/MediaFileObserver.php');

    assert.match(observer, /class MediaFileObserver extends SpatieMediaObserver/);
    // deleting: shared rows never remove files, owners keep files still in use
    assert.match(observer, /if \(\$media->isShared\(\)\) \{[\s\S]*?Media::rowsResolvingTo\(\$media->sharingBasePath\(\), \$media->getKey\(\)\)->exists\(\)[\s\S]*?parent::deleted\(\$media\);/);
    assert.match(observer, /update\(\['source_media_id' => null\]\)/);
    // a shared row may not rename a file it does not own — only a re-point
    // (shared_path changed) or the owner's mirrored name is allowed
    assert.match(observer, /! \$media->isDirty\('shared_path'\) && \$media->isDirty\('file_name'\)/);
    assert.match(observer, /if \(! \$owner instanceof Media \|\| \$owner->file_name !== \$media->file_name\) \{\s*\$media->file_name = \$media->getOriginal\('file_name'\);\s*\}/);
    // conversions/responsive state is mirrored to every usage
    assert.match(observer, /\$row->generated_conversions = \$owner->generated_conversions;/);
    assert.match(observer, /\$row->responsive_images = \$owner->responsive_images;/);
});

test('reused rows never generate their own conversions', async () => {
    for (const model of ['Product', 'ProductVariant', 'HeroSlide']) {
        const source = await read(`app/Models/${model}.php`);

        assert.match(
            source,
            /if \(\$media\?->isShared\(\)\) \{\s*return;\s*\}/,
            `${model} must skip conversions for reused images`,
        );
    }
});

test('reuse is written without touching the disk', async () => {
    const service = await read('app/Services/MediaReuseService.php');

    assert.match(service, /public function attach\(Media \$source, HasMedia \$target, string \$collection, \?int \$order = null\): Media/);
    assert.match(service, /'shared_path' => \$basePath,/);
    assert.match(service, /'source_media_id' => \$owner->getKey\(\),/);
    // No FileAdder: a reused row must never copy the file.
    assert.doesNotMatch(service, /addMedia\(|addMediaFromDisk\(|->copy\(|->put\(/);
    // singleFile collections are replaced, not appended.
    assert.match(service, /singleFile === true[\s\S]*?clearMediaCollection\(\$collection\)/);
});

test('media:dedupe merges duplicates only after the usages were re-pointed', async () => {
    const [command, service] = await Promise.all([
        read('app/Console/Commands/DedupeMedia.php'),
        read('app/Services/MediaReuseService.php'),
    ]);

    assert.match(command, /protected \$signature = 'media:dedupe/);
    assert.match(command, /--dry-run : Report what would be merged without changing anything/);
    assert.match(command, /--no-hash : Skip the checksum pass/);
    assert.match(service, /public function mergeDuplicates\(bool \$dryRun = false/);
    assert.match(service, /public function checksum\(Media \$media, bool \$persist = true\): \?string/);
    // the duplicate's own location is captured BEFORE the rows are re-pointed
    // (afterwards the duplicate resolves to the keeper's file!)
    assert.match(service, /\$duplicateBaseDir = \$generator->getPath\(\$duplicate\);/);
    assert.ok(
        service.indexOf('$duplicateBaseDir = $generator->getPath($duplicate);') < service.indexOf('foreach ($rows as $row)'),
        'media:dedupe must capture the duplicate path before re-pointing',
    );
    // re-point first (loop over rows), delete the extra files afterwards
    assert.match(service, /foreach \(\$rows as \$row\) \{\s*\$row->forceFill\(\[[\s\S]*?'shared_path' => \$keeperBase,[\s\S]*?\]\)->save\(\);[\s\S]*?\$repointed\+\+;\s*\}[\s\S]*?\$this->removeFiles\(/);
});

test('repair tooling ignores reused rows', async () => {
    const [relocation, doctor] = await Promise.all([
        read('app/Services/MediaRelocationService.php'),
        read('app/Console/Commands/MediaDoctor.php'),
    ]);

    assert.match(relocation, /reuses another row's file and is not relocated on its own/);
    assert.match(relocation, /private function moveSharedRowsToTarget\(Media \$media, string \$target\): void/);
    assert.match(relocation, /whereNull\('shared_path'\)/);

    assert.match(doctor, /private function checkSharedRows\(int \$limit\): void/);
    assert.match(doctor, /private function checkDuplicates\(\): void/);
    assert.match(doctor, /whereNull\('shared_path'\)/);
});

test('the admin media library reuses images from a whitelist of targets', async () => {
    const [resource, page, provider] = await Promise.all([
        read('app/Filament/Resources/MediaLibraryResource.php'),
        read('app/Filament/Resources/MediaLibraryResource/Pages/ManageMediaLibrary.php'),
        read('app/Providers/AppServiceProvider.php'),
    ]);

    assert.match(resource, /protected static \?string \$model = Media::class;/);
    assert.match(resource, /private const REUSE_TARGETS = \[/);
    for (const target of ['Product::class', 'ProductVariant::class', 'Category::class', 'Brand::class', 'HeroSlide::class', 'HeroBanner::class', 'HomepageBlock::class']) {
        assert.ok(resource.includes(target), `Media library must offer ${target}`);
    }
    // only whitelisted classes may be instantiated from request data
    assert.match(resource, /array_key_exists\(\$type, self::REUSE_TARGETS\) \? \$type : null/);
    assert.match(resource, /public static function canCreate\(\): bool\s*\{\s*return false;/);
    // host-relative /storage/... URLs need StoredMediaUrlColumn — ImageColumn
    // would treat them as disk paths and render nothing
    assert.match(resource, /StoredMediaUrlColumn::make\('preview'\)/);
    assert.doesNotMatch(resource, /ImageColumn::make\(/);
    assert.match(resource, /Gate::allows\('create', Media::class\)/);
    assert.match(page, /class ManageMediaLibrary extends ManageRecords/);
    assert.match(provider, /AppMedia::class => CataloguePolicy::class,/);
});

test('the social image needs no second upload and reused rows stay internal', async () => {
    const [product, resource, doc, architecture] = await Promise.all([
        read('app/Models/Product.php'),
        read('app/Filament/Resources/ProductResource.php'),
        read('docs/media-reuse.md'),
        read('docs/media-architecture.md'),
    ]);

    assert.match(product, /\?\? \$this->getFirstMedia\('gallery'\)\?->getUrl\(\)/);
    assert.match(resource, /leave empty to use the first gallery image/);

    assert.match(doc, /media:dedupe --dry-run/);
    assert.match(doc, /Use elsewhere/);
    assert.match(architecture, /\| M-10 \|/);
    assert.match(architecture, /media-reuse\.md/);
});
