import test from 'node:test';
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';

const read = (path) => readFile(new URL(`../../${path}`, import.meta.url), 'utf8');

test('Razorpay webhooks allow only captured event families to mutate paid state', async () => {
    const [controller, service, gateway, routes] = await Promise.all([
        read('app/Http/Controllers/RazorpayController.php'),
        read('app/Services/PaymentEventService.php'),
        read('app/Payment/RazorpayGateway.php'),
        read('routes/web.php'),
    ]);
    assert.match(controller, /eventType === 'payment\.authorized'/);
    assert.match(controller, /\['payment\.captured', 'order\.paid'\]/);
    assert.match(controller, /payment_state' => 'authorized'/);
    assert.match(gateway, /verifyWebhookSignature/);
    assert.match(gateway, /hash_equals\(\$expected, \$signature\)/);
    assert.match(service, /Payment amount mismatch/);
    assert.match(service, /Payment currency mismatch/);
    assert.match(routes, /payment\/razorpay\/webhook[\s\S]{0,180}throttle:120,1/);
});

test('rich text has a centralized write-boundary allowlist and arbitrary CMS scripts are not rendered', async () => {
    const [sanitizer, observer, provider, layout, seo] = await Promise.all([
        read('app/Services/RichTextSanitizer.php'),
        read('app/Observers/SanitizeRichTextObserver.php'),
        read('app/Providers/AppServiceProvider.php'),
        read('resources/views/layouts/app.blade.php'),
        read('app/Filament/Components/SeoFields.php'),
    ]);
    assert.match(sanitizer, /private const TAGS/);
    assert.match(sanitizer, /https\?\:\/\//);
    assert.match(observer, /Product.*description/s);
    assert.match(observer, /Page.*content/s);
    assert.match(provider, /observe\(SanitizeRichTextObserver::class\)/);
    assert.match(layout, /JSON_HEX_TAG/);
    assert.doesNotMatch(layout, /seo\['head_scripts'\]/);
    assert.doesNotMatch(seo, /Textarea::make\('head_scripts'\)/);
});

test('all Filament media uploads have explicit MIME size count and fixed collections', async () => {
    const paths = ['Brand', 'Category', 'HeroSlide', 'HomepageBlock', 'Product'];
    for (const name of paths) {
        const source = await read(`app/Filament/Resources/${name}Resource.php`);
        const uploads = source.split('SpatieMediaLibraryFileUpload::make').slice(1);
        assert.ok(uploads.length > 0, `${name} has no upload contract`);
        for (const upload of uploads) {
            const chain = upload.slice(0, 600);
            assert.match(chain, /->collection\('/, `${name} upload lacks fixed collection`);
            assert.match(chain, /acceptedFileTypes\(\['image\/jpeg', 'image\/png', 'image\/webp'\]\)/, `${name} MIME rule missing`);
            assert.match(chain, /maxSize\(5120\)/, `${name} size rule missing`);
            assert.match(chain, /maxFiles\(/, `${name} count rule missing`);
        }
    }
});

test('payment secrets use one canonical environment namespace', async () => {
    const [gateway, config, services, example, production] = await Promise.all([
        read('app/Payment/RazorpayGateway.php'),
        read('config/rythme.php'),
        read('config/services.php'),
        read('.env.example'),
        read('.env.production.example'),
    ]);
    assert.match(gateway, /config\('rythme\.razorpay\.key_id'\)/);
    assert.match(gateway, /A real payment gateway is not configured\. Fake payments are disabled/);
    assert.match(gateway, /environment\('local'\).*allow_fake/);
    assert.match(config, /RYTHME_RAZORPAY_KEY_SECRET/);
    assert.doesNotMatch(services, /'razorpay'/);
    assert.match(example, /RYTHME_RAZORPAY_WEBHOOK_SECRET=/);
    assert.match(production, /RYTHME_RAZORPAY_WEBHOOK_SECRET=/);
});

test('permission-scoped operations dashboard and required Phase 5/6 runbooks exist', async () => {
    const [widget, security, permissions, payment, workflows, ops, reporting] = await Promise.all([
        read('app/Filament/Widgets/StatsOverviewWidget.php'),
        read('docs/security-model.md'),
        read('docs/permissions-matrix.md'),
        read('docs/payment-security.md'),
        read('docs/admin-workflows.md'),
        read('docs/ops-runbook.md'),
        read('docs/reporting-metrics.md'),
    ]);
    for (const permission of ['FINANCE_VIEW', 'ORDERS_VIEW', 'CUSTOMERS_VIEW', 'CATALOGUE_VIEW']) assert.ok(widget.includes(permission));
    for (const metric of ['Revenue (7d)', 'Payment attention', 'Orders (today)', 'Low stock', 'Product health']) assert.ok(widget.includes(metric));
    assert.match(security, /Trust boundaries/);
    assert.match(permissions, /deny-by-default/);
    assert.match(payment, /payment\.authorized/);
    assert.match(workflows, /pending.*processing.*shipped.*delivered/s);
    assert.match(ops, /schedule:run/);
    assert.match(reporting, /Payment success rate/);
});
