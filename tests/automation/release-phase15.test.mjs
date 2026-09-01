import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';

const read = (path) => readFileSync(new URL(`../../${path}`, import.meta.url), 'utf8');

const checklist = read('docs/release-checklist.md');
const gitignore = read('.gitignore');
const envProduction = read('.env.production.example');
const rebuildScript = read('scripts/sandbox-rebuild.sh');

await test('the release checklist covers decision, backup, config, package, smoke and close', () => {
    for (const section of [
        '## 1. Release decision',
        '## 2. Backups and rollback readiness',
        '## 3. Production configuration',
        '## 4. Build and package',
        '## 5. Deployment execution',
        '## 6. Immediate smoke gate',
        '## 7. Observation and close',
    ]) {
        assert.ok(checklist.includes(section), `missing section: ${section}`);
    }
});

await test('the release checklist itself grants no deployment authority', () => {
    assert.match(checklist, /not deployment authorization/);
    assert.match(checklist, /Phase 18 and Agent 10 remain inactive/);
    assert.match(checklist, /Owner names the approved commit SHA, release window, operator and rollback decision-maker/);
});

await test('package hygiene forbids secrets, dumps and dev artifacts in the archive', () => {
    assert.match(checklist, /No `\.env`, credential, private key, database dump, logs, test artifacts, `node_modules`, or development cache enters public webroot\/release archive/);
    assert.match(checklist, /composer install --no-dev --prefer-dist --optimize-autoloader/);
    assert.match(checklist, /`npm ci --no-audit --no-fund`, dependency review and `npm run build` succeed from the lockfile/);
});

await test('forward migration is gated on SQL review and a fresh backup', () => {
    assert.match(checklist, /migrate --force` only after reviewing pending SQL and confirming backup/);
    assert.match(checklist, /Latest backup restore has been tested in isolation/);
});

await test('gitignore keeps package-relevant artifacts out of version control', () => {
    for (const pattern of ['.env', '/vendor', '/node_modules', '/public/build', '.agent-credentials', '*.log']) {
        assert.ok(gitignore.includes(pattern), `.gitignore missing: ${pattern}`);
    }
});

await test('production template keeps the HTTPS URL placeholder and empty app key', () => {
    assert.match(envProduction, /^APP_URL=https:\/\/example\.com$/m);
    assert.match(envProduction, /^APP_KEY=$/m);
});

await test('helper scripts carry no credential material', () => {
    assert.doesNotMatch(rebuildScript, /BEGIN (RSA |EC |OPENSSH )?PRIVATE KEY/);
    assert.doesNotMatch(rebuildScript, /rzp_(live|test)_[A-Za-z0-9]{8,}/);
});
