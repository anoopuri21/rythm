import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';

const read = (path) => readFileSync(new URL(`../../${path}`, import.meta.url), 'utf8');

const script = read('tasks/PHASE_16_OWNER_UAT_SCRIPT.md');
const tracker = read('tasks/MASTER_PROJECT_TRACKER.md');

await test('the owner UAT script covers every critical journey of Phase 16', () => {
    for (const journey of [
        '## 1. Browse and search',
        '## 2. Cart and checkout',
        '## 3. Test payment and order',
        '## 4. Account surfaces',
        '## 5. Admin essentials',
        '## 6. Responsive viewports',
        '## 7. Result recording',
    ]) {
        assert.ok(script.includes(journey), `UAT script missing: ${journey}`);
    }
});

await test('the script stays truthful about access rules and test mode', () => {
    assert.match(script, /guest checkout is intentionally disabled/);
    assert.match(script, /test mode/);
    assert.match(script, /not production sign-off/);
    assert.match(script, /do not change data directly in the database/);
});

await test('the script pins the four agreed viewports and the result template', () => {
    for (const viewport of ['1440×900', '768×1024', '390×844', '360×800']) {
        assert.ok(script.includes(viewport), `missing viewport ${viewport}`);
    }
    assert.match(script, /PASS \/ FAIL/);
    assert.match(script, /git rev-parse HEAD/);
});

await test('Phase 15 and 16 records stay consistent with pending owner execution', () => {
    assert.match(tracker, /\| 15 \|[^\n]+\| IN PROGRESS \|/);
    assert.match(tracker, /\| 16 \|[^\n]+\| IN PROGRESS \|/);
    assert.match(tracker, /PHASE_16_OWNER_UAT_SCRIPT\.md/);
});
