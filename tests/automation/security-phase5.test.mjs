import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';

const read = (path) => readFileSync(new URL(`../../${path}`, import.meta.url), 'utf8');

const razorpayController = read('app/Http/Controllers/RazorpayController.php');
const paymentEvents = read('app/Services/PaymentEventService.php');
const orderService = read('app/Services/OrderService.php');
const gateway = read('app/Payment/RazorpayGateway.php');
const layout = read('resources/views/layouts/app.blade.php');
const config = read('config/services.php');

await test('Razorpay webhook authenticates the exact raw body before decoding JSON', () => {
    const raw = razorpayController.indexOf('$rawBody = $request->getContent()');
    const verify = razorpayController.indexOf('verifyWebhookSignature($rawBody, $signature)');
    const decode = razorpayController.indexOf('json_decode($rawBody');
    assert.ok(raw >= 0 && verify > raw && decode > verify);
    assert.match(gateway, /hash_hmac\('sha256', \$rawBody/);
    assert.match(gateway, /hash_equals/);
});

await test('payment-state webhook handling uses an explicit event allowlist', () => {
    assert.match(razorpayController, /\['payment\.authorized', 'payment\.captured', 'order\.paid'\]/);
    assert.match(razorpayController, /status' => 'ignored'/);
    assert.match(razorpayController, /STATUS_PROCESSED/);
});

await test('authorized payments do not enter the captured paid transition', () => {
    assert.match(razorpayController, /markPaymentAuthorized/);
    assert.match(paymentEvents, /verifyAuthorizedPayment/);
    const authorized = orderService.slice(
        orderService.indexOf('function markPaymentAuthorized'),
        orderService.indexOf('function markPaid'),
    );
    assert.match(authorized, /PAYMENT_AUTHORIZED/);
    assert.doesNotMatch(authorized, /PAYMENT_PAID|STATUS_CONFIRMED|inventory->capture/);
});

await test('captured webhook requires order, amount, currency, status and capture flag', () => {
    for (const contract of ['gateway_order_id', 'expectedAmount', 'currency', "'captured'", "entity['captured']"]) {
        assert.ok(paymentEvents.includes(contract), `missing ${contract}`);
    }
});

await test('Razorpay config has one environment-backed namespace', () => {
    assert.match(config, /'razorpay'/);
    assert.match(config, /env\('RAZORPAY_KEY_ID'\)/);
    assert.match(config, /env\('RAZORPAY_KEY_SECRET'\)/);
    assert.match(config, /env\('RAZORPAY_WEBHOOK_SECRET'\)/);
    assert.doesNotMatch(read('config/rythme.php'), /RAZORPAY|razorpay/);
});

await test('rich HTML uses a read/write sanitizer and raw head scripts are disabled', () => {
    const cast = read('app/Casts/SanitizedHtml.php');
    assert.match(cast, /HtmlSanitizer/);
    assert.match(cast, /function get/);
    assert.match(cast, /function set/);
    for (const model of ['Product', 'Page', 'HomepageSection', 'Faq']) {
        assert.match(read(`app/Models/${model}.php`), /SanitizedHtml::class/);
    }
    assert.doesNotMatch(layout, /head_scripts/);
    assert.match(layout, /JSON_HEX_TAG/);
});

await test('all admin media fields have bounded MIME, size and count rules', () => {
    for (const resource of ['Product', 'Brand', 'Category', 'HeroSlide', 'HomepageBlock']) {
        const source = read(`app/Filament/Resources/${resource}Resource.php`);
        const uploads = source.split('SpatieMediaLibraryFileUpload::make').slice(1);
        assert.ok(uploads.length > 0);
        for (const upload of uploads) {
            assert.match(upload, /acceptedFileTypes/);
            assert.match(upload, /maxSize/);
            assert.match(upload, /maxFiles/);
            assert.doesNotMatch(upload, /image\/svg\+xml/);
        }
    }
});

await test('Phase 5 security documents exist and cover operational controls', () => {
    for (const file of ['security-model.md', 'permissions-matrix.md', 'payment-security.md']) {
        const doc = read(`docs/${file}`);
        assert.ok(doc.length > 1000, `${file} is unexpectedly short`);
    }
});
