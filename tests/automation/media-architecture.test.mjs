import test from 'node:test';
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';

const read = (path) => readFile(new URL(`../../${path}`, import.meta.url), 'utf8');

test('one MEDIA_DISK setting drives both the media library and Filament uploads', async () => {
    const [media, filament] = await Promise.all([
        read('config/media-library.php'),
        read('config/filament.php'),
    ]);
    assert.match(media, /'disk_name' => env\('MEDIA_DISK'\) \?: 'public'/);
    assert.match(filament, /'default_filesystem_disk' => env\('MEDIA_DISK'\) \?: 'public'/);
    // Filament must never fall back to the generic (private) default disk again (comments excluded).
    assert.doesNotMatch(filament.replace(/\/\*[\s\S]*?\*\//g, ''), /FILESYSTEM_DISK/);
});

test('the public disk URL is host-relative and independent of APP_URL', async () => {
    const filesystems = await read('config/filesystems.php');
    const publicDisk = filesystems.slice(filesystems.indexOf("'public' => ["), filesystems.indexOf("'s3' => ["));
    assert.match(publicDisk, /'url' => rtrim\(\(string\) \(env\('MEDIA_URL'\) \?: '\/storage'\), '\/'\)/);
    assert.doesNotMatch(publicDisk, /env\('APP_URL'/);
});

test('environment templates declare the public media disk', async () => {
    for (const file of ['.env.example', '.env.staging.example', '.env.production.example']) {
        assert.match(await read(file), /^MEDIA_DISK=public$/m, `${file} lacks MEDIA_DISK=public`);
    }
});

test('the default disk stays private — FILESYSTEM_DISK=public is not a deployment recipe', async () => {
    // Media follows MEDIA_DISK, so FILESYSTEM_DISK=public buys nothing and puts
    // Livewire's pre-validation temp uploads inside the web-served root.
    for (const file of ['.env.example', '.env.staging.example', '.env.production.example']) {
        assert.match(await read(file), /^FILESYSTEM_DISK=local/m, `${file} must keep the default disk private`);
    }

    for (const file of ['docs/DEPLOY_RHYTHM_STEP_BY_STEP.md', 'docs/MILESWEB_DEPLOYMENT.md']) {
        assert.match(await read(file), /^FILESYSTEM_DISK=local/m, `${file} still ships the old FILESYSTEM_DISK=public recipe`);
        assert.match(await read(file), /^MEDIA_DISK=public/m, `${file} must name the media disk instead`);
    }

    assert.match(await read('app/Console/Commands/MediaDoctor.php'), /FILESYSTEM_DISK=local/);
});

test('storefront models resolve conversions through Spatie getAvailableUrl instead of hand-rolled fallbacks', async () => {
    for (const model of ['Product', 'ProductVariant', 'HeroSlide']) {
        const source = await read(`app/Models/${model}.php`);
        assert.doesNotMatch(source, /hasGeneratedConversion/, `${model} re-implements the conversion fallback`);
    }
    assert.match(await read('app/Models/Product.php'), /getAvailableUrl\(\['gallery-webp'\]\)/);
    assert.match(await read('app/Models/Product.php'), /getAvailableUrl\(\['thumb-webp'\]\)/);
});

test('product and variant galleries are served as bounded WebP conversions, not originals', async () => {
    const [product, variant, resource] = await Promise.all([
        read('app/Models/Product.php'),
        read('app/Models/ProductVariant.php'),
        read('app/Filament/Resources/ProductResource.php'),
    ]);

    // Variant images are what the PDP swaps to, so they need the same 1200px
    // WebP chain as the product gallery — never the raw upload.
    assert.match(variant, /addMediaConversion\('variant-gallery-webp'\)[\s\S]*?->width\(1200\)/);
    assert.match(variant, /getAvailableUrl\(\['variant-gallery-webp'\]\)/);
    assert.doesNotMatch(variant, /galleryUrls[\s\S]{0,600}?->getUrl\(\)/, 'Variant gallery must not serve originals');

    // Social-share images are handed to crawlers as-is; gallery conversions
    // must not be queued for the og collection.
    assert.match(product, /addMediaConversion\('thumb-webp'\)\s*->performOnCollections\('gallery'\)/);
    assert.match(product, /addMediaConversion\('gallery-webp'\)\s*->performOnCollections\('gallery'\)/);

    // The admin list shows the stored 480px-webp thumbnail column (M-7)
    // instead of resolving full-size originals per row.
    assert.match(resource, /StoredMediaUrlColumn::make\('thumbnail_url'\)[\s\S]{0,60}?->circular\(\)/);
});

test('every admin upload field bounds bytes, pixels and mime type without widening to image/*', async () => {
    const source = await read('app/Filament/Components/MediaUpload.php');
    // Comments explain why image() is avoided — assert on code only.
    const factory = source.replace(/\/\*[\s\S]*?\*\//g, '').replace(/\/\/.*$/gm, '');

    // A byte limit alone does not bound decode cost (decompression bombs).
    assert.match(factory, /dimensions:max_width=\{\$maxWidth\},max_height=\{\$maxHeight\}/);
    assert.match(factory, /public const MAX_WIDTH = \d{4}/);
    assert.match(factory, /public const MAX_HEIGHT = \d{4}/);

    // Filament's image() rewrites acceptedFileTypes to `image/*`, which would
    // re-admit SVG (script-capable) — the explicit raster list must stay.
    assert.doesNotMatch(factory, /->image\(\)/);

    // Galleries are reorderable: the first image is the storefront hero/card.
    assert.match(factory, /->reorderable\(\)/);
});

test('resolved media URLs are persisted in DB columns and read column-first (M-7)', async () => {
    const [migration, observer, contract, concern, provider, product, variant, command] = await Promise.all([
        read('database/migrations/2026_10_03_000001_add_resolved_media_url_columns.php'),
        read('app/Observers/MediaUrlObserver.php'),
        read('app/Models/Contracts/HasResolvedMediaUrls.php'),
        read('app/Models/Concerns/SyncsResolvedMediaUrls.php'),
        read('app/Providers/AppServiceProvider.php'),
        read('app/Models/Product.php'),
        read('app/Models/ProductVariant.php'),
        read('app/Console/Commands/SyncMediaUrls.php'),
    ]);

    // One nullable column set per media-bearing model.
    for (const column of ['gallery_urls', 'thumbnail_url', 'og_image_url', 'logo_url', 'icon_url', 'desktop_image_url', 'mobile_image_url', 'image_url']) {
        assert.match(migration, new RegExp(`'${column}'`), `migration lacks ${column}`);
    }
    assert.doesNotMatch(migration, /->after\(/, 'no ->after(): SQLite ALTER has no AFTER clause');

    // The cache is written quietly (forceFill + saveQuietly), never mass-assigned.
    assert.match(concern, /forceFill\(\$changes\)->saveQuietly\(\)/);
    // …and without touching updated_at, which drives Trending/merchandising order.
    assert.match(concern, /\$this->timestamps = false/);
    assert.match(contract, /public function resolvedMediaUrls\(\): array/);

    // Every media change re-syncs the owner: upload, delete, reorder, conversion.
    assert.match(observer, /public function created\(Media \$media\)/);
    assert.match(observer, /public function deleted\(Media \$media\)/);
    assert.match(observer, /'order_column'/);
    assert.match(observer, /'generated_conversions'/);

    // A media change moves a rendered URL → the cached homepage payload must go,
    // otherwise a replaced image renders the URL of a deleted file.
    assert.match(observer, /HomepageDataObserver::flush\(\)/);

    // Quiet media writes (relocation) must sync the owner explicitly.
    const relocation = await read('app/Services/MediaRelocationService.php');
    assert.match(relocation, /saveQuietly\(\)[\s\S]{0,400}?syncResolvedMediaUrls\(\)/);
    assert.match(provider, /config\('media-library\.media_model'[\s\S]{0,80}MediaUrlObserver::class/);

    // Reads are column-first with a Media Library fallback.
    assert.match(product, /return \$this->thumbnail_url\s*\?\?\s*\$this->getFirstMedia\('gallery'\)/);
    assert.match(product, /public function ogImage\(\)/);
    assert.match(variant, /return \$this->thumbnail_url\s*\?\?\s*\$this->getFirstMedia\('variant_gallery'\)/);

    // Repair/backfill path exists and is idempotent by construction.
    assert.match(command, /media:sync-urls/);
    assert.match(command, /--dry-run/);
});

test('admin list thumbnails render stored URL columns instead of querying media', async () => {
    const resources = ['Product', 'Brand', 'Category', 'HeroSlide', 'HomepageBlock'];

    // Filament's ImageColumn resolves its state as a *disk path*, which would
    // break a stored /storage/... web URL — hence the app's own column class.
    const column = await read('app/Filament/Columns/StoredMediaUrlColumn.php');
    assert.match(column, /extends ImageColumn/);
    assert.match(column, /str_starts_with\(\$state, '\/'\)/);

    for (const name of resources) {
        const source = await read(`app/Filament/Resources/${name}Resource.php`);
        assert.doesNotMatch(source, /SpatieMediaLibraryImageColumn/, `${name} list must not query media per row`);
        assert.match(source, /StoredMediaUrlColumn::make\('[a-z_]*url'\)/, `${name} list must render a stored URL column`);
    }

    // …and the product list no longer eager-loads the media relation at all.
    const product = await read('app/Filament/Resources/ProductResource.php');
    assert.doesNotMatch(product, /->with\(\['category', 'brand', 'media'/);
});

test('the deploy script backfills media URL columns without failing the deploy', async () => {
    const deploy = await read('scripts/deploy-cpanel.sh');
    assert.match(deploy, /media_sync_urls\(\) \{[\s\S]*?artisan media:sync-urls --only-missing/);
    assert.match(deploy, /media_relocate; media_sync_urls; optimize/);
    assert.match(deploy, /resolveColumns|media_sync_urls/);
});

test('SEO tags make host-relative media URLs absolute at the output boundary', async () => {
    const [layout, product] = await Promise.all([
        read('resources/views/layouts/app.blade.php'),
        read('resources/views/product/show.blade.php'),
    ]);
    assert.match(layout, /\$ogImage = url\(/);
    assert.match(product, /'image' => url\(/);
});

test('media operations: relocate command, deploy hooks and architecture doc are in place', async () => {
    const [command, deploy, doc, optimisation] = await Promise.all([
        read('app/Console/Commands/RelocateMedia.php'),
        read('scripts/deploy-cpanel.sh'),
        read('docs/media-architecture.md'),
        read('docs/media-optimization.md'),
    ]);
    assert.match(command, /media:relocate/);
    assert.match(deploy, /migrate; seed; storage_link; media_relocate; media_sync_urls; optimize/);
    assert.match(deploy, /migrate; storage_link; media_relocate; media_sync_urls; optimize/);
    // `update` must continue in the freshly pulled copy of the script (bash already parsed the old one).
    assert.match(deploy, /git pull --ff-only[^\n]*\n[\s\S]*?exec bash "\$APP_DIR\/scripts\/deploy-cpanel\.sh" update-steps/);
    assert.match(deploy, /\n  update-steps\)[\s\S]*?media_relocate/);
    assert.match(doc, /MEDIA_DISK/);
    assert.match(doc, /media:relocate/);
    assert.match(optimisation, /media-architecture\.md/);
});

test('exactly the media disk owns the /storage URL — the private disk is never served', async () => {
    const filesystems = await read('config/filesystems.php');
    const localDisk = filesystems.slice(filesystems.indexOf("'local' => ["), filesystems.indexOf("'public' => ["));
    const publicDisk = filesystems.slice(filesystems.indexOf("'public' => ["), filesystems.indexOf("'s3' => ["));

    // Laravel registers `GET /storage/{path}` for every local disk with
    // `serve => true`. On the PRIVATE disk that route demands a signature
    // (403 in dev, 404 in production) and reads storage/app/private — so when
    // the `public/storage` symlink was missing it answered every media URL and
    // the images 404'd right after save, in the panel and on the storefront.
    assert.match(localDisk, /'serve' => false/);
    assert.doesNotMatch(localDisk, /'serve' => true/);
    assert.match(publicDisk, /'serve' => true/);
});

test('media:doctor diagnoses the chain and is wired into the deploy check', async () => {
    const [command, deploy] = await Promise.all([
        read('app/Console/Commands/MediaDoctor.php'),
        read('scripts/deploy-cpanel.sh'),
    ]);

    assert.match(command, /'media:doctor/);
    assert.match(command, /--fix/);
    assert.doesNotMatch(command, /function fail\(|function warn\(/, 'Command::fail()/warn() are reserved — use reportFail()/reportWarn()');
    assert.match(command, /media:sync-urls/);   // URL-column repair path
    assert.match(command, /storage:link/);      // symlink repair path

    assert.match(deploy, /media_doctor\(\) \{[\s\S]*?artisan media:doctor/);
    assert.match(deploy, /health; media_doctor \|\| true/);
});

test('the served-media regression has feature coverage', async () => {
    const feature = await read('tests/Feature/MediaStorageTest.php');

    // The URL must be served by the media disk with no symlink involved…
    assert.match(feature, /test_media_urls_are_served_from_the_media_disk_without_a_storage_symlink/);
    // …the private disk must NOT own it (403 before the fix, 404 after)…
    assert.match(feature, /test_the_private_disk_is_not_served_at_the_media_url_path[\s\S]*?assertNotFound\(\)/);
    // …and one disk only may claim /storage.
    assert.match(feature, /test_only_the_media_disk_owns_the_storage_url_path[\s\S]*?assertSame\(\[\$mediaDisk\], \$served\['\/storage'\]/);
});
