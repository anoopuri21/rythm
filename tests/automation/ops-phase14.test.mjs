import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';

const read = (path) => readFileSync(new URL(`../../${path}`, import.meta.url), 'utf8');

const consoleRoutes = read('routes/console.php');
const hostingOps = read('tasks/PHASE_0B_SHARED_HOSTING_OPERATIONS.md');
const rollback = read('docs/rollback-plan.md');
const runbook = read('docs/ops-runbook.md');
const envProduction = read('.env.production.example');
const loggingConfig = read('config/logging.php');

await test('the shared-host queue worker is bounded, scheduled and overlap-safe', () => {
    assert.match(consoleRoutes, /Schedule::command\('queue:work --stop-when-empty --max-time=50 --tries=3 --timeout=45'\)/);
    assert.match(consoleRoutes, /->everyMinute\(\)/);
    assert.match(consoleRoutes, /->withoutOverlapping\(2\)/);
});

await test('the cPanel cron contract invokes the scheduler every minute', () => {
    assert.match(hostingOps, /\* \* \* \* \* .*artisan schedule:run/);
    assert.match(hostingOps, /QUEUE_CONNECTION=database/);
    assert.match(hostingOps, /SESSION_DRIVER=database/);
    assert.match(hostingOps, /Oracle MySQL 8\.x/);
    assert.match(hostingOps, /does not satisfy the exact MySQL 8 gate/);
    assert.match(hostingOps, /never target persistent UAT or production\/cPanel data with `migrate:fresh`, `db:wipe`, `RefreshDatabase`/);
});

await test('the rollback plan covers every layer and preserves financial data', () => {
    for (const section of [
        'A. Configuration/cache rollback',
        'B. Application/assets rollback',
        'C. Migration rollback',
        'Financial integrity checks',
        'Post-rollback validation',
    ]) {
        assert.ok(rollback.includes(section), `rollback plan missing: ${section}`);
    }
    assert.match(rollback, /must not erase valid orders, captured payments, refunds, inventory movements or customer uploads/);
    assert.match(rollback, /Keep deployment disabled until root cause, regression test and a new approved release checklist are complete/);
});

await test('the operations runbook retains the backup and restore-qualification rule', () => {
    assert.match(runbook, /## Release preflight/);
    assert.match(runbook, /## Backups/);
    assert.match(runbook, /A backup is not qualified until a restore test passes/);
    assert.match(runbook, /## Logs and monitoring/);
    assert.match(runbook, /human authorization required/i);
});

await test('production environment defaults are log- and queue-safe', () => {
    assert.match(envProduction, /^LOG_LEVEL=warning$/m);
    assert.match(envProduction, /^QUEUE_CONNECTION=database$/m);
    assert.match(envProduction, /^SESSION_DRIVER=database$/m);
    assert.match(loggingConfig, /'default' => env\('LOG_CHANNEL', 'stack'\)/);
});
