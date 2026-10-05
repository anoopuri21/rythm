import test from 'node:test';
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';

const read = (path) => readFile(new URL(`../../${path}`, import.meta.url), 'utf8');

/**
 * Static guards for the Cloudinary rollout (docs/cloudinary-media.md → M-9).
 *
 * Phase 1 = product images (gallery + og + variants) and category icons. The
 * rollout is a per-collection disk decision: nothing else may move to the cloud
 * disk, and every repair tool must treat cloud rows as intentional.
 */
test('the cloudinary disk is a normal filesystem disk fed by CLOUDINARY_* env', async () => {
    const filesystems = await read('config/filesystems.php');
    const disk = filesystems.slice(filesystems.indexOf("'cloudinary' => ["));

    assert.match(disk, /'driver' => 'cloudinary'/);
    assert.match(disk, /'url' => env\('CLOUDINARY_URL'\) \?: null/);
    assert.match(disk, /'cloud' => env\('CLOUDINARY_CLOUD_NAME'\)/);
    assert.match(disk, /'key' => env\('CLOUDINARY_KEY'\)/);
    assert.match(disk, /'secret' => env\('CLOUDINARY_SECRET'\)/);
});

test('the media model is the app one and the rollout is config-driven', async () => {
    const media = await read('config/media-library.php');
    const backslash = String.fromCharCode(92);

    assert.ok(
        media.includes("'media_model' => App" + backslash + "Models" + backslash + "Media::class"),
        'config/media-library.php must point at the app Media model',
    );
    assert.match(media, /'disk_name' => env\('MEDIA_DISK'\) \?: 'public'/);
    assert.match(media, /'enabled' => \(bool\) env\('MEDIA_CLOUDINARY', false\)/);
    assert.match(media, /'collections' => env\('MEDIA_CLOUDINARY_COLLECTIONS'\)/);
});

test('MediaDisk is the single disk decision and fails safe without credentials', async () => {
    const source = await read('app/Support/MediaDisk.php');

    assert.match(source, /DEFAULT_CLOUD_COLLECTIONS = \['gallery', 'og', 'variant_gallery', 'icon'\]/);
    assert.match(source, /public static function forCollection/);
    assert.match(source, /public static function enabled/);
    assert.match(source, /public static function isCloudinary/);

    // Enabled requires BOTH the switch and a resolvable cloud name.
    assert.match(source, /'media-library\.cloudinary\.enabled'/);
    assert.match(source, /self::cloudName\(\) !== null/);
});

test('Cloudinary delivery URLs are derived, never fetched through the Admin API', async () => {
    const source = await read('app/Support/CloudinaryDeliveryUrl.php');

    assert.match(source, /res\.cloudinary\.com/);
    assert.match(source, /image\/upload/);

    // No disk/API round trip in the code itself (comments may explain why).
    const code = source.replace(/\/\*[\s\S]*?\*\//g, '').replace(/\/\/.*$/gm, '');
    assert.doesNotMatch(code, /adminApi|->url\(|Storage::/);
});

test('the phase-1 collections write to Cloudinary from both the panel and the models', async () => {
    const [product, variant, category, upload] = await Promise.all([
        read('app/Models/Product.php'),
        read('app/Models/ProductVariant.php'),
        read('app/Models/Category.php'),
        read('app/Filament/Components/MediaUpload.php'),
    ]);

    assert.match(product, /useDisk\(MediaDisk::forCollection\('gallery'\)\)/);
    assert.match(product, /useDisk\(MediaDisk::forCollection\('og'\)\)/);
    assert.match(variant, /useDisk\(MediaDisk::forCollection\('variant_gallery'\)\)/);
    assert.match(category, /useDisk\(MediaDisk::forCollection\('icon'\)\)/);

    // The panel field reads the same class, so it cannot drift from the model.
    assert.match(upload, /->disk\(MediaDisk::forCollection\(\$collection\)\)/);
});

test('cloud media queue no local conversions and resolve via the Media model', async () => {
    const [product, variant, media] = await Promise.all([
        read('app/Models/Product.php'),
        read('app/Models/ProductVariant.php'),
        read('app/Models/Media.php'),
    ]);

    for (const source of [product, variant]) {
        assert.match(source, /if \(MediaDisk::isCloudinary\(\$media\?->disk\)\) \{[\s\S]*?return;/);
    }

    assert.match(media, /class Media extends SpatieMedia/);
    assert.match(media, /public function getUrl\(string \$conversionName = ''\): string/);
    assert.match(media, /public function getAvailableUrl\(array \$conversionNames\): string/);
    assert.match(media, /CLOUDINARY_TRANSFORMATIONS = \[/);
});

test('relocation and media:doctor treat Cloudinary rows as intentional, not misplaced', async () => {
    const [relocation, doctor] = await Promise.all([
        read('app/Services/MediaRelocationService.php'),
        read('app/Console/Commands/MediaDoctor.php'),
    ]);

    assert.match(relocation, /MediaDisk::isCloudinary\(\$originalDisk\)/);
    assert.match(relocation, /where\('disk', '!=', MediaDisk::CLOUDINARY\)/);
    assert.match(relocation, /cloudHostedCount/);

    assert.match(doctor, /private function checkCloudinary/);
    assert.match(doctor, /where\('disk', MediaDisk::CLOUDINARY\)/);
    assert.match(doctor, /where\('disk', '!=', MediaDisk::CLOUDINARY\)/);
});

test('environment templates keep the local disk contract and add the Cloudinary switch', async () => {
    for (const file of ['.env.example', '.env.staging.example', '.env.production.example']) {
        const contents = await read(file);

        assert.match(contents, /^MEDIA_DISK=public$/m, `${file} must keep MEDIA_DISK=public`);
        assert.match(contents, /^FILESYSTEM_DISK=local/m, `${file} must keep the default disk private`);
        assert.match(contents, /^MEDIA_CLOUDINARY=/m, `${file} lacks the Cloudinary switch`);
        assert.match(contents, /CLOUDINARY_URL=/, `${file} lacks the credentials shape`);
    }
});

test('the rollout stays off for the PHP test suite unless a test opts in', async () => {
    const phpunit = await read('phpunit.xml');

    assert.match(phpunit, /<env name="MEDIA_CLOUDINARY" value="false" force="true"\/>/);
});

test('the Cloudinary suite proves no upload is written to the local disk', async () => {
    const suite = await read('tests/Feature/CloudinaryMediaTest.php');

    // Every phase-1 collection that the owner uploads through has a test that
    // asserts the file exists on the cloud disk AND does not exist on the
    // public (local, web-served) disk. Losing that pair would let a regression
    // silently start storing product/category images on this server again.
    const cloudExists = suite.match(/Storage::disk\('cloudinary'\)->exists\(\$media->getPathRelativeToRoot\(\)\)/g) ?? [];
    const localMissing = suite.match(/assertFalse\(\s*Storage::disk\('public'\)->exists\(\$media->getPathRelativeToRoot\(\)\)/g) ?? [];

    assert.ok(cloudExists.length >= 3, `Expected product, category and variant uploads to assert the cloud file exists (found ${cloudExists.length})`);
    assert.ok(localMissing.length >= 3, `Expected at least 3 "nothing on the local disk" assertions (found ${localMissing.length})`);

    // A legacy row must keep its exact /storage URL with the rollout on —
    // media:sync-urls included (M-2/M-7).
    assert.ok(suite.includes('test_enabling_cloudinary_never_changes_a_legacy_storage_url'));
    assert.match(suite, /assertSame\(\$thumbBefore, \$fresh->thumbnail_url/);
    assert.match(suite, /artisan\('media:sync-urls'\)/);

    // Reuse (M-10) and duplicate cleanup (media:dedupe) are covered ON the
    // cloud disk: one asset, no second upload, no local copy.
    assert.ok(suite.includes('test_a_reused_image_stays_one_cloudinary_asset_and_uploads_nothing'));
    assert.ok(suite.includes('test_media_dedupe_merges_duplicate_cloud_uploads_into_one_asset'));
    assert.match(suite, /assertCount\(1, Storage::disk\('cloudinary'\)->allFiles\(\)/);
});

test('reuse, dedupe and relocation stay cloud-aware in the media services', async () => {
    const [reuse, relocation, observer] = await Promise.all([
        read('app/Services/MediaReuseService.php'),
        read('app/Services/MediaRelocationService.php'),
        read('app/Observers/MediaFileObserver.php'),
    ]);

    // A reused row inherits the owner's disk — cloud assets are shared by
    // public_id, so the usage resolves to the same one asset (never re-uploaded).
    assert.match(reuse, /'disk' => \$owner->disk/);
    assert.match(reuse, /'conversions_disk' => \$owner->conversions_disk/);
    // The duplicate copy is deleted from ITS disk (cloud included), and every
    // cleanup call is Throwable-tolerant: the adapter throws when a delete fails.
    assert.match(reuse, /Storage::disk\(\$diskName\)->delete\(\$original\)/);
    assert.match(reuse, /array_unique\(\[\$disk, \$derivedDisk\]\)/);

    // Nothing may copy a cloud file onto this server to "repair" it.
    assert.match(relocation, /MediaDisk::isCloudinary\(\$originalDisk\)/);
    assert.doesNotMatch(observer, /Storage::disk\(MediaDisk::mediaDisk/);
});

test('the rollout is documented next to the media architecture contract', async () => {
    const [doc, architecture] = await Promise.all([
        read('docs/cloudinary-media.md'),
        read('docs/media-architecture.md'),
    ]);

    assert.match(doc, /MEDIA_CLOUDINARY=true/);
    assert.match(doc, /media:doctor/);
    assert.match(architecture, /\| M-9 \|/);
    assert.match(architecture, /cloudinary-media\.md/);

    // The verification section must keep the two proofs the owner asked for:
    // nothing lands on the local disk, and an old image keeps its exact URL.
    assert.match(doc, /php artisan test --filter=CloudinaryMediaTest/);
    assert.match(doc, /find storage\/app\/public -type f \| wc -l/);
    assert.match(doc, /storage\/app\/public` still has no new file/);
    assert.match(doc, /exact\*\* original[\s\S]{0,40}\/storage\/\.\.\./);
    assert.match(doc, /php artisan media:dedupe --dry-run/);
});
