import test from 'node:test';
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';

const read = (path) => readFile(new URL(`../../${path}`, import.meta.url), 'utf8');

test('homepage conversion funnel includes confidence, FAQ and final CTA surfaces', async () => {
    const [home, confidence] = await Promise.all([
        read('resources/views/home/index.blade.php'),
        read('resources/views/home/_confidence.blade.php'),
    ]);
    assert.match(home, /home\._hero/);
    assert.match(home, /home\._categories/);
    assert.match(home, /home\._new-arrivals/);
    assert.match(home, /home\._deals/);
    assert.match(home, /home\._confidence/);
    assert.match(confidence, /testimonials/);
    assert.match(confidence, /home-faq-title/);
    assert.match(confidence, /home-final-cta-title/);
});

test('product decision page exposes recent, related, FAQ, trust and availability paths', async () => {
    const [controller, query, product, cart] = await Promise.all([
        read('app/Http/Controllers/ProductController.php'),
        read('app/Services/ProductQueryService.php'),
        read('resources/views/product/show.blade.php'),
        read('resources/views/livewire/add-to-cart.blade.php'),
    ]);
    assert.match(controller, /storefront\.recent_products/);
    assert.match(query, /function recentlyViewed/);
    assert.match(product, /Recently viewed/);
    assert.match(product, /Related products/);
    assert.match(product, /Frequently asked questions/);
    assert.match(product, /Shipping information/);
    assert.match(cart, /Ask about availability/);
    assert.doesNotMatch(cart, /EMI from/);
    assert.doesNotMatch(product, />1 Year</);
});

test('checkout, footer and account expose policy and support paths', async () => {
    const [checkout, footer, account] = await Promise.all([
        read('resources/views/livewire/checkout-wizard.blade.php'),
        read('resources/views/components/footer.blade.php'),
        read('resources/views/account/index.blade.php'),
    ]);
    for (const slug of ['/shipping', '/returns', '/privacy']) assert.ok(checkout.includes(slug));
    for (const slug of ['/shipping', '/returns', '/faqs']) assert.ok(footer.includes(slug));
    assert.match(account, /account-panel-support/);
    assert.match(account, /Track an order/);
});

test('catalogue has bounded pagination and supporting storefront indexes', async () => {
    const [query, migration] = await Promise.all([
        read('app/Services/ProductQueryService.php'),
        read('database/migrations/2026_08_29_000004_add_storefront_catalog_indexes.php'),
    ]);
    assert.match(query, /private const PER_PAGE = 12/);
    for (const index of ['storefront_price', 'storefront_stock', 'storefront_newest', 'storefront_featured']) {
        assert.ok(migration.includes(index), `missing ${index} index`);
    }
});

test('Phase 2 storefront documentation records coverage and open policy gates', async () => {
    const [flow, checklist, coverage] = await Promise.all([
        read('docs/storefront-flow.md'),
        read('docs/page-checklist.md'),
        read('docs/content-coverage.md'),
    ]);
    assert.match(flow, /Primary purchase journey/);
    assert.match(checklist, /500\+ active products/);
    assert.match(coverage, /Back-in-stock/);
    assert.match(coverage, /dedicated return cases need approved policy/i);
});
