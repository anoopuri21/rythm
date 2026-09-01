import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';

const read = (path) => readFileSync(new URL(`../../${path}`, import.meta.url), 'utf8');

const productQuery = read('app/Services/ProductQueryService.php');
const productController = read('app/Http/Controllers/ProductController.php');
const cartService = read('app/Services/CartService.php');
const accountController = read('app/Http/Controllers/AccountController.php');
const notificationController = read('app/Http/Controllers/NotificationController.php');
const homeController = read('app/Http/Controllers/HomeController.php');
const budget = read('docs/performance-budget.md');

await test('shop listing stays bounded at 12 per page with eager commerce relations', () => {
    assert.match(productQuery, /private const PER_PAGE = 12;/);
    assert.match(productQuery, /\$query->paginate\(self::PER_PAGE\)->withQueryString\(\)/);
    assert.match(productQuery, /->with\(\['brand', 'category', 'media'\]\)/);
    assert.match(productQuery, /->withCount\(\['reviews as reviews_count'/);
});

await test('product detail eager loads its relations and never serves inactive products', () => {
    assert.match(productController, /abort_unless\(\$product->is_active, 404\);/);
    for (const relation of ["'brand'", "'category.parent'", "'media'", "'seoEntry'"]) {
        assert.ok(productController.includes(relation), `missing eager load ${relation}`);
    }
    assert.match(productController, /'variants' => fn \(\$query\) => \$query->where\('is_active', true\)/);
});

await test('cart payload loads product, brand, media and variant in one query', () => {
    assert.match(cartService, /->items\(\)\s*->with\(\['product\.brand', 'product\.media', 'variant'\]\)\s*->get\(\)/);
});

await test('account, stock-alert and notification lists stay paginated', () => {
    assert.match(accountController, /->withCount\('items'\)[\s\S]*?->paginate\(10\)/);
    assert.match(accountController, /->paginate\(12, \['\*'\], 'stock_alert_page'\)/);
    assert.match(notificationController, /->paginate\(12\)/);
});

await test('homepage sections and SEO payloads are served from cache', () => {
    assert.match(homeController, /Cache::remember\('homepage\.sections', 3600/);
    assert.match(homeController, /Cache::remember\('homepage\.seo', 3600/);
});

await test('performance budget keeps the measured build table and pagination policy', () => {
    for (const required of [
        'Global application JS',
        'Motion JS, homepage only',
        'Storefront catalogue: 12 products per page',
        'Account orders: 10 per page',
        'Notifications: 12 per page',
        'never enabled publicly',
        'No carousels, GSAP, Lenis, CountUp, cinema or category pin code is loaded on checkout',
    ]) {
        assert.ok(budget.includes(required), `budget doc missing: ${required}`);
    }
});

await test('the measured 31 August build stays inside every browser budget', () => {
    const rows = {
        'Global application JS': { budgetKb: 15, measuredKb: 2.87 },
        'Carousel JS, only on pages containing `.swiper`': { budgetKb: 35, measuredKb: 29.34 },
        'Motion JS, homepage only': { budgetKb: 60, measuredKb: 53.27 },
        'Global CSS': { budgetKb: 30, measuredKb: 27.93 },
        'Carousel CSS, conditional': { budgetKb: 4, measuredKb: 2.35 },
    };
    for (const [label, { budgetKb, measuredKb }] of Object.entries(rows)) {
        const row = budget.split('\n').find((line) => line.includes(label));
        assert.ok(row, `budget row missing: ${label}`);
        assert.ok(row.includes(`≤ ${budgetKb} KB gzip`), `${label} budget cell changed`);
        assert.ok(row.includes(`${measuredKb} KB gzip`), `${label} measured evidence changed`);
        assert.ok(measuredKb <= budgetKb, `${label} exceeds budget`);
    }
});
