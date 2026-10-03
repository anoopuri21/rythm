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

test('storefront models resolve conversions through Spatie getAvailableUrl instead of hand-rolled fallbacks', async () => {
    for (const model of ['Product', 'ProductVariant', 'HeroSlide']) {
        const source = await read(`app/Models/${model}.php`);
        assert.doesNotMatch(source, /hasGeneratedConversion/, `${model} re-implements the conversion fallback`);
    }
    assert.match(await read('app/Models/Product.php'), /getAvailableUrl\(\['gallery-webp'\]\)/);
    assert.match(await read('app/Models/Product.php'), /getAvailableUrl\(\['thumb-webp'\]\)/);
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
    assert.match(deploy, /migrate; seed; storage_link; media_relocate; optimize/);
    assert.match(deploy, /migrate; storage_link; media_relocate; optimize/);
    // `update` must continue in the freshly pulled copy of the script (bash already parsed the old one).
    assert.match(deploy, /git pull --ff-only[^\n]*\n[\s\S]*?exec bash "\$APP_DIR\/scripts\/deploy-cpanel\.sh" update-steps/);
    assert.match(deploy, /\n  update-steps\)[\s\S]*?media_relocate/);
    assert.match(doc, /MEDIA_DISK/);
    assert.match(doc, /media:relocate/);
    assert.match(optimisation, /media-architecture\.md/);
});
