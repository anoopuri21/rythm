import test from 'node:test';
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';

const read = (path) => readFile(new URL(`../../${path}`, import.meta.url), 'utf8');

test('launch smoke suite covers the core storefront release paths', async () => {
    const [smoke, packageJson, supervisorStateTest] = await Promise.all([
        read('tests/Feature/LaunchSmokeTest.php'),
        read('package.json'),
        read('tests/automation/supervisor-state.test.mjs'),
    ]);
    assert.match(packageJson, /"test:automation": "node --test tests\/automation\/\*\.test\.mjs"/);
    assert.match(supervisorStateTest, /process\.platform === 'win32'/);
    for (const contract of ["route('home')", "route('shop.index'", "route('cart.index')", "route('checkout.index')", "signedRoute('checkout.success'"]) {
        assert.ok(smoke.includes(contract), `missing smoke contract: ${contract}`);
    }
    assert.match(smoke, /assertRedirect\(route\('login'\)\)/);
    assert.match(smoke, /assertForbidden\(\)/);
});

test('catalogue qualification creates more than 500 products with bounded pagination and queries', async () => {
    const qualification = await read('tests/Feature/ShopLargeCatalogueQualificationTest.php');
    assert.match(qualification, /range\(1, 520\)/);
    assert.match(qualification, /assertSame\(520, \$page->total\(\)\)/);
    assert.match(qualification, /assertSame\(44, \$page->lastPage\(\)\)/);
    assert.match(qualification, /assertLessThanOrEqual\(10, \$queryCount/);
});

test('every staff role has an exhaustive permission regression contract', async () => {
    const governance = await read('tests/Feature/AdminGovernanceTest.php');
    for (const role of ['ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_CATALOGUE_MANAGER', 'ROLE_ORDER_MANAGER', 'ROLE_SUPPORT', 'ROLE_MARKETING', 'ROLE_FINANCE']) {
        assert.ok(governance.includes(role), `missing role: ${role}`);
    }
    assert.match(governance, /foreach \(\$permissions as \$permission\)/);
    assert.match(governance, /Unexpected \{\$permission\} result for \{\$role\}/);
});

test('QA checklist covers checkout payments mobile admin media and 500-plus catalogue gates', async () => {
    const qa = await read('docs/qa-checklist.md');
    for (const heading of ['Checkout paths', 'Payment, replay and refund matrix', 'Mobile and responsive UI', 'Admin permissions and workflows', 'Product import, upload and rendering', '500+ catalogue qualification']) {
        assert.ok(qa.includes(heading), `missing QA section: ${heading}`);
    }
    assert.match(qa, /360×800/);
    assert.match(qa, /payment\.authorized/);
    assert.match(qa, /Agent 0 records technical sign-off/);
});

test('release checklist is repeatable and keeps deployment human-gated', async () => {
    const release = await read('docs/release-checklist.md');
    for (const contract of ['approved commit SHA', 'MySQL backup', 'RAZORPAY_ALLOW_FAKE_PAYMENTS=false', 'composer install --no-dev', 'php artisan migrate --force', 'Immediate smoke gate', 'first 30 minutes']) {
        assert.ok(release.includes(contract), `missing release contract: ${contract}`);
    }
    assert.match(release, /Phase 18 and Agent 10 remain inactive/);
});

test('rollback plan protects post-release and financial writes', async () => {
    const rollback = await read('docs/rollback-plan.md');
    for (const contract of ['Configuration/cache rollback', 'Application/assets rollback', 'Migration rollback', 'Database restore', 'Financial integrity checks']) {
        assert.ok(rollback.includes(contract), `missing rollback section: ${contract}`);
    }
    assert.match(rollback, /Never use `migrate:fresh`, `db:wipe`/);
    assert.match(rollback, /not blindly retr(?:y|ied)/);
    assert.match(rollback, /Preserve all post-release customer\/admin uploads/);
});
