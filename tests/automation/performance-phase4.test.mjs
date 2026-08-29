import test from 'node:test';
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';

const read = (path) => readFile(new URL(`../../${path}`, import.meta.url), 'utf8');

test('heavy storefront JavaScript is loaded only for matching pages', async () => {
    const [app, carousels, footer] = await Promise.all([
        read('resources/js/app.js'),
        read('resources/js/modules/carousels.js'),
        read('resources/views/components/footer.blade.php'),
    ]);
    assert.match(app, /document\.querySelector\('\.swiper'\)/);
    assert.match(app, /import\('\.\/modules\/carousels'\)/);
    assert.match(app, /document\.querySelector\('\.hero-mm'\)/);
    assert.match(app, /import\('\.\/modules\/motion'\)/);
    assert.doesNotMatch(app, /^import .*modules\/motion/m);
    assert.match(carousels, /import 'swiper\/css'/);
    assert.doesNotMatch(footer, /reveal-section|data-reveal/);
});

test('product and hero media define bounded queued WebP conversions', async () => {
    const [product, hero, card] = await Promise.all([
        read('app/Models/Product.php'),
        read('app/Models/HeroSlide.php'),
        read('resources/views/components/shop-card.blade.php'),
    ]);
    for (const conversion of ['thumb-webp', 'gallery-webp']) assert.ok(product.includes(conversion));
    for (const conversion of ['hero-desktop-webp', 'hero-mobile-webp']) assert.ok(hero.includes(conversion));
    assert.match(product, /->queued\(\)/);
    assert.match(hero, /->queued\(\)/);
    assert.match(card, /thumbnailImage\(\)/);
});

test('large customer lists are paginated and homepage cold-cache N plus one fallback is absent', async () => {
    const [account, homepage] = await Promise.all([
        read('app/Http/Controllers/AccountController.php'),
        read('app/Services/HomepageDataService.php'),
    ]);
    assert.match(account, /->paginate\(10\)/);
    assert.doesNotMatch(homepage, /whereIn\('category_id', \$ids\)[\s\S]{0,160}first\(\)\?->heroImage/);
    assert.match(homepage, /Cache::remember/);
});

test('admin growth indexes and bounded shared-host queue worker exist', async () => {
    const [migration, schedule] = await Promise.all([
        read('database/migrations/2026_08_29_000006_add_admin_list_indexes.php'),
        read('routes/console.php'),
    ]);
    for (const index of ['contact_admin_queue_idx', 'reviews_admin_queue_idx', 'questions_admin_queue_idx', 'shipments_admin_queue_idx']) {
        assert.ok(migration.includes(index), `missing ${index}`);
    }
    assert.match(schedule, /queue:work --stop-when-empty --max-time=50/);
});

test('Phase 4 performance documentation defines measurable budgets and operations', async () => {
    const [budget, cache, media] = await Promise.all([
        read('docs/performance-budget.md'),
        read('docs/cache-strategy.md'),
        read('docs/media-optimization.md'),
    ]);
    assert.match(budget, /Global application JS/);
    assert.match(cache, /config:cache/);
    assert.match(cache, /must not be shared-cached/i);
    assert.match(media, /media-library:regenerate/);
});
