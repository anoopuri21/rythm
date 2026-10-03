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
    assert.match(controller, /\['payment\.authorized', 'payment\.captured', 'order\.paid'\]/);
    assert.match(controller, /verifyAuthorizedPayment/);
    assert.match(controller, /markPaymentAuthorized/);
    assert.match(gateway, /verifyWebhookSignature/);
    assert.match(gateway, /hash_equals\(\$expected, \$signature\)/);
    assert.match(service, /Payment amount mismatch/);
    assert.match(service, /Payment currency mismatch/);
    assert.match(routes, /payment\/razorpay\/webhook[\s\S]{0,180}throttle:120,1/);
});

test('rich text has a centralized write-boundary allowlist and arbitrary CMS scripts are not rendered', async () => {
    const [sanitizer, product, page, layout, seo] = await Promise.all([
        read('app/Casts/SanitizedHtml.php'),
        read('app/Models/Product.php'),
        read('app/Models/Page.php'),
        read('resources/views/layouts/app.blade.php'),
        read('app/Filament/Components/SeoFields.php'),
    ]);
    assert.match(sanitizer, /HtmlSanitizerConfig/);
    assert.match(sanitizer, /allowSafeElements/);
    assert.match(product, /'description' => SanitizedHtml::class/);
    assert.match(page, /'content' => SanitizedHtml::class/);
    assert.match(layout, /JSON_HEX_TAG/);
    assert.doesNotMatch(layout, /seo\['head_scripts'\]/);
    assert.doesNotMatch(seo, /Textarea::make\('head_scripts'\)/);
});

test('all Filament media uploads go through the shared MediaUpload factory with explicit MIME size count and fixed collections', async () => {
    // The limits live in ONE place (so no field can forget them)...
    const factory = await read('app/Filament/Components/MediaUpload.php');
    for (const mime of ["'image/jpeg'", "'image/png'", "'image/webp'"]) {
        assert.ok(factory.includes(mime), `MediaUpload lacks ${mime}`);
    }
    assert.match(factory, /->acceptedFileTypes\(\$mimeTypes\)/);
    assert.match(factory, /->maxSize\(\$maxSizeKb\)/);
    assert.match(factory, /->maxFiles\(/);
    assert.match(factory, /->collection\(\$collection\)/);
    assert.doesNotMatch(factory, /image\/svg\+xml/);

    // ...and every resource must use it (never a raw SpatieMediaLibraryFileUpload).
    const paths = ['Brand', 'Category', 'HeroSlide', 'HomepageBlock', 'Product'];
    for (const name of paths) {
        const source = await read(`app/Filament/Resources/${name}Resource.php`);
        assert.doesNotMatch(source, /SpatieMediaLibraryFileUpload::make/, `${name} bypasses MediaUpload`);
        const uploads = source.split('MediaUpload::').slice(1);
        assert.ok(uploads.length > 0, `${name} has no upload contract`);
        for (const upload of uploads) {
            assert.match(
                upload.slice(0, 160),
                /^(single|gallery)\('[a-z_]+', '[a-z_]+', (maxSizeKb|maxFiles): [1-9][0-9]*/,
                `${name} upload lacks a fixed collection or numeric limit`,
            );
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
    assert.match(gateway, /config\('services\.razorpay\.key_id'\)/);
    assert.match(gateway, /A real payment gateway is not configured\. Fake payments are disabled/);
    assert.match(gateway, /environment\('local'\).*allow_fake/);
    assert.doesNotMatch(config, /razorpay/i);
    assert.match(services, /RAZORPAY_KEY_SECRET/);
    assert.match(example, /RAZORPAY_WEBHOOK_SECRET=/);
    assert.match(production, /RAZORPAY_WEBHOOK_SECRET=/);
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
    assert.match(permissions, /deny(?:-| )by default/i);
    assert.match(payment, /payment\.authorized/);
    assert.match(workflows, /pending.*processing.*shipped.*delivered/s);
    assert.match(ops, /schedule:run/);
    assert.match(reporting, /Payment success rate/);
});
