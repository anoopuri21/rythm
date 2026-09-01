import assert from 'node:assert/strict';
import { execFileSync } from 'node:child_process';
import { readFileSync } from 'node:fs';
import path from 'node:path';
import test from 'node:test';

const root = path.resolve(import.meta.dirname, '../..');
const read = (relative) => readFileSync(new URL(`../../${relative}`, import.meta.url), 'utf8');
const gitGrep = (pattern) => {
    try {
        return execFileSync('git', ['grep', '-nIE', pattern, '--', '.', ':!*.lock'], { cwd: root, encoding: 'utf8' });
    } catch (error) {
        assert.equal(error.status, 1, `git grep failed unexpectedly for ${pattern}`);
        return '';
    }
};

const headers = read('app/Http/Middleware/SecurityHeaders.php');
const appConfig = read('config/app.php');
const sessionConfig = read('config/session.php');
const envExample = read('.env.example');
const envProduction = read('.env.production.example');
const composerJson = read('composer.json');
const composerLock = read('composer.lock');
const packageLock = read('package-lock.json');

await test('security headers cover the OWASP baseline with production-only HSTS', () => {
    assert.match(headers, /'X-Content-Type-Options', 'nosniff'/);
    assert.match(headers, /'X-Frame-Options', 'SAMEORIGIN'/);
    assert.match(headers, /'Referrer-Policy', 'strict-origin-when-cross-origin'/);
    assert.match(headers, /'Permissions-Policy', 'camera=\(\), microphone=\(\), geolocation=\(\)'/);
    assert.match(headers, /if \(\$request->isSecure\(\)\) \{\s*\$response->headers->set\('Strict-Transport-Security'/s);
});

await test('CSP stays bounded to self plus the approved Razorpay/fonts/media origins', () => {
    for (const directive of [
        "default-src 'self'",
        "frame-ancestors 'self'",
        "object-src 'none'",
        "base-uri 'self'",
        "form-action 'self'",
        'frame-src https://checkout.razorpay.com',
    ]) {
        assert.ok(headers.includes(directive), `missing CSP directive: ${directive}`);
    }
    const scriptSrc = headers.match(/script-src[^"\n]*/)[0];
    const externalScripts = [...scriptSrc.matchAll(/https:\/\/[^\s]+/g)].map((match) => match[0]);
    assert.deepEqual(externalScripts, ['https://checkout.razorpay.com']);
    assert.doesNotMatch(headers, /cdn\.jsdelivr\.net|www\.google\.com|www\.gstatic\.com/);
});

await test('debug mode and app environment stay environment-driven with safe defaults', () => {
    assert.match(appConfig, /'debug' => \(bool\) env\('APP_DEBUG', false\)/);
    assert.match(appConfig, /'env' => env\('APP_ENV', 'production'\)/);
});

await test('session cookies keep secure-by-default attributes', () => {
    assert.match(sessionConfig, /'http_only' => env\('SESSION_HTTP_ONLY', true\)/);
    assert.match(sessionConfig, /'same_site' => env\('SESSION_SAME_SITE', 'lax'\)/);
    assert.match(sessionConfig, /'encrypt' => env\('SESSION_ENCRYPT', true\)/);
    assert.match(sessionConfig, /'secure' => env\('SESSION_SECURE_COOKIE'\)/);
});

await test('environment templates ship empty secrets and production-safe flags', () => {
    for (const key of ['APP_KEY', 'DB_PASSWORD', 'RAZORPAY_KEY_ID', 'RAZORPAY_KEY_SECRET', 'RAZORPAY_WEBHOOK_SECRET']) {
        assert.match(envExample, new RegExp(`^${key}=$`, 'm'));
    }
    assert.match(envProduction, /^APP_ENV=production$/m);
    assert.match(envProduction, /^APP_DEBUG=false$/m);
    assert.match(envProduction, /^SESSION_SECURE_COOKIE=true$/m);
    assert.doesNotMatch(envExample + envProduction, /rzp_(?:live|test)_[A-Za-z0-9]{8,}/);
});

await test('no secret material is committed to the tracked tree', () => {
    assert.equal(gitGrep('rzp_(live|test)_[A-Za-z0-9]{8,}'), '');
    assert.equal(gitGrep('sk_(live|test)_[A-Za-z0-9]{8,}'), '');
    assert.equal(gitGrep('AKIA[0-9A-Z]{16}'), '');
    const keyMarkers = gitGrep('BEGIN (RSA |EC |OPENSSH )?PRIVATE KEY');
    const fixtureAllowlist = ['supervisor-simulations.test.mjs', 'supervisor-state.test.mjs'];
    const unexpected = keyMarkers.split('\n').filter(Boolean)
        .filter((line) => !fixtureAllowlist.some((fixture) => line.includes(fixture)));
    assert.deepEqual(unexpected, []);
});

await test('dependency artifacts and environment files stay out of version control', () => {
    const tracked = execFileSync('git', ['ls-files'], { cwd: root, encoding: 'utf8' });
    assert.equal(/^vendor\//m.test(tracked), false);
    assert.equal(/^node_modules\//m.test(tracked), false);
    assert.equal(/(^|\n)\.env(\n|$)/.test(tracked), false);
    assert.match(read('.gitignore'), /^\/?vendor$/m);
    assert.match(read('.gitignore'), /^\.env$/m);
});

await test('locked stack pins are intact in dependency manifests', () => {
    assert.match(composerJson, /"laravel\/framework": "13\.24\.0"/);
    assert.match(composerJson, /"php": "\^8\.3"/);
    assert.match(composerLock, /"name": "laravel\/framework",\s*\n\s*"version": "v13\.24\.0"/);
    assert.match(packageLock, /"lockfileVersion": 3/);
});

await test('Phase 12 plan closes Chunk 2 and records environment-only requirements', () => {
    const plan = read('tasks/AUTO_MODE_PHASE_12_PLAN.md');
    assert.match(plan, /Chunk 2 — security configuration and dependency\/secret scan contract[\s\S]*?\*\*Status:\*\* COMPLETE/);
    assert.match(plan, /Environment-only production requirements/);
});
