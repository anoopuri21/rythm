import test from 'node:test';
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';

const read = (path) => readFile(new URL(`../../${path}`, import.meta.url), 'utf8');

/**
 * Swiper sizes every category slide with an inline `style="width:…px"` derived
 * from `slidesPerView`/`spaceBetween`. The stylesheet carries a mirrored
 * `:not(.swiper-initialized)` fallback so the section degrades to a grid
 * instead of one full-width tile when Swiper never initialises.
 *
 * Those two sources must agree. If they drift, the no-JS/degraded layout shows
 * a different number of columns than the live carousel. See MEMORY §F footgun 15.
 */
const SLIDE_SELECTOR = '\\.cat-swiper:not\\(\\.swiper-initialized\\) \\.swiper-slide\\.cat-card';

test('categories carousel CSS fallback mirrors the Swiper breakpoints exactly', async () => {
    const [js, css] = await Promise.all([
        read('resources/js/modules/carousels.js'),
        read('resources/css/app.css'),
    ]);

    // --- JS side: the `.cat-swiper` instance only ---
    const start = js.indexOf("document.querySelector('.cat-swiper')");
    assert.notEqual(start, -1, 'no .cat-swiper instance found in carousels.js');
    const end = js.indexOf('remeasureOnSettle(catSwiper)', start);
    assert.notEqual(end, -1, '.cat-swiper instance is not passed to remeasureOnSettle()');
    const block = js.slice(start, end);

    const gap = Number(/spaceBetween:\s*([\d.]+)/.exec(block)?.[1]);
    const basePerView = Number(/slidesPerView:\s*([\d.]+)/.exec(block)?.[1]);
    assert.ok(Number.isFinite(gap) && gap > 0, 'spaceBetween missing on .cat-swiper');
    assert.ok(Number.isFinite(basePerView) && basePerView > 0, 'slidesPerView missing on .cat-swiper');

    const breakpointsBlock = /breakpoints:\s*\{([\s\S]*?)\n {12}\},/.exec(block)?.[1] ?? '';
    const jsBreakpoints = new Map();
    for (const m of breakpointsBlock.matchAll(/(\d+):\s*\{\s*slidesPerView:\s*([\d.]+)/g)) {
        jsBreakpoints.set(Number(m[1]), Number(m[2]));
    }
    assert.ok(jsBreakpoints.size >= 4, `expected the mobile→desktop breakpoint ladder, got ${jsBreakpoints.size}`);
    for (const [, perView] of jsBreakpoints) {
        // A breakpoint may override slidesPerView only; spaceBetween stays the base
        // value, which is what the CSS fallback assumes.
        assert.ok(!/spaceBetween/.test(breakpointsBlock), 'a categories breakpoint overrides spaceBetween — update the CSS fallback too');
        assert.ok(Number.isFinite(perView) && perView > 0, 'breakpoint slidesPerView must be a positive number');
    }

    // --- CSS side: base rule + one rule per min-width block ---
    // One alternation regex so a rule's media threshold comes from the block it
    // is actually written in, not from whatever @media happens to precede it.
    const WIDTH = 'calc\\(\\(100% - ([\\d.]+)px\\) / ([\\d.]+)\\);';
    const ruleRe = new RegExp(
        `@media \\(min-width: (\\d+)px\\) \\{\\s*${SLIDE_SELECTOR}\\s*\\{\\s*width: ${WIDTH}\\s*\\}\\s*\\}`
        + `|${SLIDE_SELECTOR}\\s*\\{\\s*width: ${WIDTH}`,
        'g',
    );
    const cssRules = new Map();
    for (const m of css.matchAll(ruleRe)) {
        const threshold = m[1] ? Number(m[1]) : 0;
        const subtract = Number(m[2] ?? m[4]);
        const divisor = Number(m[3] ?? m[5]);
        assert.ok(!cssRules.has(threshold), `duplicate fallback rule for the ${threshold}px threshold`);
        cssRules.set(threshold, { subtract, divisor });
    }

    assert.ok(cssRules.has(0), 'missing the base (no media query) fallback width');
    assert.deepEqual(
        [...cssRules.keys()].sort((a, b) => a - b),
        [0, ...jsBreakpoints.keys()].sort((a, b) => a - b),
        'CSS fallback breakpoints must match the Swiper breakpoints one-for-one',
    );

    for (const [threshold, { subtract, divisor }] of cssRules) {
        const perView = threshold === 0 ? basePerView : jsBreakpoints.get(threshold);
        assert.equal(divisor, perView, `fallback at ${threshold}px divides by ${divisor} but Swiper uses slidesPerView ${perView}`);
        const expectedSubtract = gap * (perView - 1);
        assert.ok(
            Math.abs(subtract - expectedSubtract) < 0.001,
            `fallback at ${threshold}px subtracts ${subtract}px but spaceBetween ${gap} × (slidesPerView ${perView} − 1) = ${expectedSubtract}px`,
        );
    }
});

test('categories carousel isolates Swiper failures and re-measures after paint', async () => {
    const js = await read('resources/js/modules/carousels.js');

    // Every instance goes through the guarded factory — a bare `new Swiper(`
    // outside it would reintroduce the silent, cascading failure mode.
    const bareInits = js.match(/new Swiper\(/g) ?? [];
    assert.equal(bareInits.length, 1, 'only createSwiper() may call `new Swiper(`');
    assert.match(js, /function createSwiper\(/);
    assert.match(js, /try \{\s*return new Swiper\(element, options\);\s*\} catch/);

    const instances = ['catSwiper', 'brandSwiper', 'testimonialSwiper', 'productSwiper'];
    for (const name of instances) {
        assert.match(js, new RegExp(`const ${name} = createSwiper\\(`), `${name} is not created via createSwiper()`);
        assert.match(js, new RegExp(`remeasureOnSettle\\(${name}\\)`), `${name} is not passed to remeasureOnSettle()`);
    }
    assert.match(js, /function remeasureOnSettle\(instance\) \{\s*if \(!instance\) return;/,
        'remeasureOnSettle() must tolerate a null instance returned by a failed init');

    const app = await read('resources/js/app.js');
    assert.match(app, /function loadModule\(/, 'dynamic imports must be wrapped so a 404 chunk is reported');
    assert.doesNotMatch(app, /jobs\.push\(import\(/, 'every dynamic import must go through loadModule()');
});

test('categories fallback goes inert once Swiper initialises', async () => {
    const css = await read('resources/css/app.css');

    // Unscoped fallback rules would fight Swiper's inline widths forever.
    const unscoped = css.match(/\.cat-swiper \.swiper-slide\.cat-card\s*\{[^}]*width:/);
    assert.equal(unscoped, null, 'fallback width must be scoped to :not(.swiper-initialized)');

    const scoped = css.match(/\.cat-swiper:not\(\.swiper-initialized\) \.swiper-slide\.cat-card/g) ?? [];
    assert.ok(scoped.length >= 5, `expected the base rule plus 4 breakpoints, found ${scoped.length}`);
});
