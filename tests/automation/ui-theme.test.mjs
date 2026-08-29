import test from 'node:test';
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';

const read = (path) => readFile(new URL(`../../${path}`, import.meta.url), 'utf8');

test('premium theme is loaded and uses the approved brand and type tokens', async () => {
    const [app, theme, tailwind, layout] = await Promise.all([
        read('resources/css/app.css'),
        read('resources/css/theme.css'),
        read('tailwind.config.js'),
        read('resources/views/layouts/app.blade.php'),
    ]);

    assert.match(app, /@import ['"]\.\/theme\.css['"]/);
    assert.match(theme, /--ry-color-primary:\s*#b20202/i);
    assert.match(theme, /--ry-color-primary-strong:\s*#930303/i);
    assert.match(theme, /--ry-color-accent-soft:\s*#e7f4f1/i);
    assert.match(theme, /--ry-font-sans:\s*'Poppins'/);
    assert.match(tailwind, /sans:\s*\['Poppins'/);
    assert.match(layout, /family=Poppins/);
});

test('shared UI primitives and standardized media ratios exist', async () => {
    const theme = await read('resources/css/theme.css');
    const components = ['button', 'badge', 'alert', 'input', 'empty-state', 'skeleton', 'media'];

    await Promise.all(components.map((name) => read(`resources/views/components/ui/${name}.blade.php`)));
    for (const selector of ['.ui-btn', '.ui-card', '.ui-badge', '.ui-alert', '.ui-input', '.ui-empty', '.ui-skeleton']) {
        assert.ok(theme.includes(selector), `missing ${selector}`);
    }
    assert.match(theme, /\.ui-media--product\s*\{\s*aspect-ratio:\s*1\s*\/\s*1/);
    assert.match(theme, /\.ui-media--banner\s*\{\s*aspect-ratio:\s*16\s*\/\s*7/);
    assert.match(theme, /prefers-reduced-motion:\s*reduce/);
});

test('shared product cards opt into the visual system without changing their grids', async () => {
    const [shop, homepage, legacy] = await Promise.all([
        read('resources/views/components/shop-card.blade.php'),
        read('resources/views/components/mega-product-card.blade.php'),
        read('resources/views/components/product-card.blade.php'),
    ]);

    for (const card of [shop, homepage, legacy]) {
        assert.match(card, /ui-card/);
        assert.match(card, /ui-media--product/);
    }
});

test('luxury additions remain semantic and restrained', async () => {
    const [theme, cart, checkout, badge] = await Promise.all([
        read('resources/css/theme.css'),
        read('resources/views/livewire/cart-page.blade.php'),
        read('resources/views/livewire/checkout-wizard.blade.php'),
        read('resources/views/components/ui/badge.blade.php'),
    ]);

    for (const variant of ['new', 'best-seller', 'limited']) {
        assert.ok(theme.includes(`.ui-badge--${variant}`), `missing premium ${variant} badge`);
    }
    assert.match(theme, /\.ui-card\s*\{\s*box-shadow:\s*none/);
    assert.match(theme, /\.ui-summary-panel/);
    assert.match(cart, /ui-summary-panel/);
    assert.match(checkout, /ui-summary-panel/);
    assert.match(badge, /ui-badge--/);
});
