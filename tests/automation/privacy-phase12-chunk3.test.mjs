import assert from 'node:assert/strict';
import { readFileSync, readdirSync, statSync } from 'node:fs';
import path from 'node:path';
import test from 'node:test';

const root = path.resolve(import.meta.dirname, '../..');
const read = (relative) => readFileSync(new URL(`../../${relative}`, import.meta.url), 'utf8');

const bladeFiles = [];
(function walk(directory) {
    for (const entry of readdirSync(directory)) {
        const full = path.join(directory, entry);
        if (statSync(full).isDirectory()) walk(full);
        else if (full.endsWith('.blade.php')) bladeFiles.push(full);
    }
})(path.join(root, 'resources/views'));

// Blade expressions contain `->` arrows, so tag matching must tolerate them.
const TAG = String.raw`(?:->|[^>])*`;
const imgRe = new RegExp(String.raw`<img\b${TAG}>`, 'g');
const buttonRe = new RegExp(String.raw`<button\b(${TAG})>([\s\S]*?)</button>`, 'g');

await test('every rendered image carries an alt attribute', () => {
    assert.ok(bladeFiles.length > 30, 'expected the full blade inventory');
    const missing = [];
    for (const file of bladeFiles) {
        for (const match of readFileSync(file, 'utf8').matchAll(imgRe)) {
            if (!/\balt\s*=/.test(match[0])) missing.push(`${file} :: ${match[0].slice(0, 60)}`);
        }
    }
    assert.deepEqual(missing, []);
});

await test('icon-only buttons keep an accessible name', () => {
    const offending = [];
    for (const file of bladeFiles) {
        for (const match of readFileSync(file, 'utf8').matchAll(buttonRe)) {
            const [, attrs, inner] = match;
            const withoutSvg = inner.replace(/<svg[\s\S]*?<\/svg>/g, '');
            const visibleText = withoutSvg
                .replace(/<[^>]+>/g, '')
                .replace(/\{\{[\s\S]*?\}\}/g, '')
                .trim();
            // A Blade echo outside the SVG also renders an accessible name.
            const bladeEcho = /\{\{/.test(withoutSvg);
            const labelled = /aria-label|aria-labelledby/.test(attrs) || /sr-only/.test(inner);
            if (!labelled && !bladeEcho && visibleText === '' && /<svg/.test(inner)) {
                offending.push(`${file} :: ${match[0].slice(0, 60)}`);
            }
        }
    }
    assert.deepEqual(offending, []);
});

await test('primary layout keeps the skip link and main landmark', () => {
    const layout = read('resources/views/layouts/app.blade.php');
    assert.match(layout, /<a href="#main-content" class="skip-link">/);
    assert.match(layout, /<main id="main-content"/);
});

await test('no account deletion, export or erasure route exists before approval', () => {
    const web = read('routes/web.php');
    assert.doesNotMatch(web, /Route::(?:delete|post|get)\('\/account\/(?:delete|export|erase|anonym)/i);
    assert.doesNotMatch(web, /destroyAccount|exportAccount|eraseAccount|deleteAccount/);
});

await test('returns and tax behavior remain disabled by default', () => {
    assert.match(read('app/Services/SiteSettingsService.php'), /'returns_enabled' => '0'/);
    assert.match(read('app/Services/OrderService.php'), /\$this->settings->get\('tax_rules_enabled', '0'\) === '1'/);
    assert.match(read('app/Services/ReturnRequestService.php'), /\$this->settings->get\('returns_enabled', '0'\) !== '1'/);
    const order = read('app/Http/Controllers/OrderController.php');
    assert.match(order, /\$settings->get\('returns_enabled', '0'\) === '1'\s*&& \(int\) \$settings->get\('return_window_days', '0'\) > 0/);
});

await test('privacy data map retains the human-gated legal decisions', () => {
    const map = read('docs/phase12-privacy-data-map.md');
    assert.match(map, /## Human-gated decisions/);
    for (const decision of [
        'export format',
        'deletion versus anonymization',
        'Retention periods and legal basis',
        'tracking technologies and any approved consent language',
        'Terms, Privacy, Shipping, Returns, Warranty and Cancellation text',
    ]) {
        assert.ok(map.includes(decision), `missing decision: ${decision}`);
    }
    assert.match(map, /No account data export or deletion workflow is enabled/);
    assert.match(map, /No cookie\/consent banner is added/);
});

await test('Phase 12 plan closes Chunk 3 with the legal-text human gate recorded', () => {
    const plan = read('tasks/AUTO_MODE_PHASE_12_PLAN.md');
    assert.match(plan, /Chunk 3 — MVP privacy, legal and accessibility blockers[\s\S]*?\*\*Status:\*\* COMPLETE/);
    assert.match(plan, /AS-H011/);
});
