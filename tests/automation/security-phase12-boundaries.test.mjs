import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';

const read = (path) => readFileSync(new URL(`../../${path}`, import.meta.url), 'utf8');

const web = read('routes/web.php');
const bootstrap = read('bootstrap/app.php');
const cartDrawer = read('app/Livewire/CartDrawer.php');
const cartPage = read('app/Livewire/CartPage.php');
const checkoutWizard = read('app/Livewire/CheckoutWizard.php');
const wishlistButton = read('app/Livewire/WishlistButton.php');
const wishlistPage = read('app/Livewire/WishlistPage.php');
const reviewSection = read('app/Livewire/ReviewSection.php');
const questionSection = read('app/Livewire/ProductQuestionSection.php');
const addToCart = read('app/Livewire/AddToCart.php');
const orderController = read('app/Http/Controllers/OrderController.php');
const notificationController = read('app/Http/Controllers/NotificationController.php');
const returnController = read('app/Http/Controllers/ReturnRequestController.php');
const addressService = read('app/Services/AddressService.php');

await test('every mutating web route carries an explicit throttle boundary', () => {
    const mutating = [...web.matchAll(/Route::(?:post|patch|delete)\(([\s\S]*?->name\('[^']+'\));/g)];
    assert.ok(mutating.length >= 15, `expected a representative route inventory, found ${mutating.length}`);
    for (const [, statement] of mutating) {
        assert.match(statement, /throttle:/, `mutating route missing throttle: ${statement.slice(0, 80)}`);
    }
});

await test('order mutation routes require auth in addition to throttle', () => {
    const retry = web.match(/Route::post\('\/orders\/\{order\}\/retry-payment'[^;]+;/s)[0];
    const cancel = web.match(/Route::post\('\/orders\/\{order\}\/cancel'[^;]+;/s)[0];
    assert.match(retry, /middleware\(\['auth', 'throttle:3,1'\]\)/);
    assert.match(cancel, /middleware\(\['auth', 'throttle:5,1'\]\)/);
});

await test('CSRF exceptions stay limited to the two cryptographically verified Razorpay endpoints', () => {
    const except = bootstrap.match(/validateCsrfTokens\(except: \[([\s\S]*?)\]\)/)[1];
    const entries = [...except.matchAll(/'([^']+)'/g)].map((entry) => entry[1]);
    assert.deepEqual(entries.sort(), ['payment/razorpay/callback', 'payment/razorpay/webhook']);
    assert.match(bootstrap, /prependToGroup\('web', \\App\\Http\\Middleware\\SecurityHeaders::class\)/);
});

await test('customer Livewire writes resolve the authenticated user before mutating', () => {
    assert.match(wishlistButton, /if \(\$user === null\) \{\s*\$this->redirect\(route\('login'\)\)/);
    assert.match(wishlistPage, /public function moveToCart[\s\S]*?\$user === null[\s\S]*?redirect\(route\('login'\)\)/);
    assert.match(reviewSection, /public function submit[\s\S]*?auth\(\)->guest\(\)[\s\S]*?guardRateLimit/);
    assert.match(questionSection, /public function submit[\s\S]*?auth\(\)->guest\(\)[\s\S]*?guardRateLimit/);
    assert.match(addToCart, /requestStockNotification[\s\S]*?! \$user instanceof User/);
});

await test('checkout wizard actions enforce authentication and address ownership', () => {
    assert.match(checkoutWizard, /public function selectAddress[\s\S]*?abort_unless\(auth\(\)->check\(\), 403\)/);
    assert.match(checkoutWizard, /forUser\(\(int\) auth\(\)->id\(\)\)->contains\('id', \$addressId\)/);
    assert.match(checkoutWizard, /public function applyCoupon[\s\S]*?abort_unless\(auth\(\)->check\(\), 403\)/);
    assert.match(checkoutWizard, /public function saveNewAddress[\s\S]*?abort_unless\(auth\(\)->check\(\), 403\)/);
});

await test('cart item mutations verify the item belongs to the current session/user cart', () => {
    for (const [name, source] of [['CartDrawer', cartDrawer], ['CartPage', cartPage]]) {
        assert.match(source, /\$item->cart_id !== \$cart->getOrCreateCart\(\)->id/, `${name} updateQty ownership`);
        assert.match(source, /\$item->cart_id === \$cart->getOrCreateCart\(\)->id/, `${name} remove ownership`);
    }
});

await test('order reads accept owner or valid signature while mutations stay owner-only', () => {
    assert.match(orderController, /auth\(\)->check\(\) && auth\(\)->id\(\) === \$order->user_id/);
    assert.match(orderController, /\$request->hasValidSignature\(\)/);
    assert.match(orderController, /public function retryPayment[\s\S]*?abort_unless\(auth\(\)->check\(\) && auth\(\)->id\(\) === \$order->user_id, 403\)/);
    assert.match(orderController, /public function cancel[\s\S]*?abort_unless\(auth\(\)->check\(\) && auth\(\)->id\(\) === \$order->user_id, 403\)/);
});

await test('guest order lookup pairs order number with email and returns a bounded signed redirect', () => {
    assert.match(orderController, /validate\(\[[\s\S]*?'order_number' => \['required', 'string', 'max:30'\][\s\S]*?'email' => \['required', 'string', 'email', 'max:254'\]/);
    assert.match(orderController, /->where\('order_number', \$validated\['order_number'\]\)\s*->where\('email', \$validated\['email'\]\)/);
    assert.match(orderController, /URL::temporarySignedRoute\(\s*'orders\.show',\s*now\(\)->addMinutes\(15\)/);
});

await test('notification mutations fetch the record through the authenticated user relation', () => {
    assert.match(notificationController, /\$request->user\(\)->notifications\(\)->whereKey\(\$id\)->firstOrFail\(\)/);
    assert.match(notificationController, /public function markRead[\s\S]*?\$this->owned\(\$request, \$notification\)/);
    assert.match(notificationController, /public function markUnread[\s\S]*?\$this->owned\(\$request, \$notification\)/);
});

await test('return requests stay owner-only on create, store and cancel', () => {
    const ownerChecks = returnController.match(/abort_unless\(\$customer instanceof User && \$(?:order|returnRequest)->user_id === \$customer->id, 403\)/g) ?? [];
    assert.ok(ownerChecks.length === 3, `expected 3 owner checks, found ${ownerChecks.length}`);
});

await test('address service owner-checks update, default and destroy actions', () => {
    const guards = addressService.match(/if \(\$address->user_id !== \$userId\) \{\s*abort\(403\);/g) ?? [];
    assert.ok(guards.length === 3, `expected 3 ownership guards, found ${guards.length}`);
});

await test('Phase 12 planning documents keep Chunk 1 closed with this contract as evidence', () => {
    const plan = read('tasks/AUTO_MODE_PHASE_12_PLAN.md');
    const matrix = read('docs/phase12-authorization-matrix.md');
    assert.match(plan, /Chunk 1 — safe authorization and input-boundary remediation[\s\S]*?\*\*Status:\*\* COMPLETE/);
    assert.match(matrix, /security-phase12-boundaries\.test\.mjs/);
});
