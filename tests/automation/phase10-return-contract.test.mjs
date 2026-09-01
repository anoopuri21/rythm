import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';

const read = (path) => readFileSync(new URL(`../../${path}`, import.meta.url), 'utf8');

test('return workflow stays disabled until explicit approved configuration', () => {
  const settings = read('app/Services/SiteSettingsService.php');
  const returns = read('app/Services/ReturnRequestService.php');
  const migration = read('database/migrations/2026_08_29_000007_create_return_request_domain.php');

  assert.match(settings, /'returns_enabled' => '0'/);
  assert.match(settings, /'return_window_days' => '0'/);
  const settingsPage = read('app/Filament/Pages/Settings.php');
  assert.doesNotMatch(settingsPage, /make\('data\./);
  assert.match(settingsPage, /saveAll\(\$this->form->getState\(\)\)/);
  assert.match(returns, /get\('returns_enabled', '0'\) !== '1'/);
  assert.match(returns, /return_window_days/);
  assert.match(returns, /whereNotIn\('status'/);
  assert.match(migration, /return_request_events/);
  assert.match(migration, /idempotency_key.*unique/);
});

test('return approval and provider refund remain separate outcomes', () => {
  const returns = read('app/Services/ReturnRequestService.php');
  const resource = read('app/Filament/Resources/ReturnRequestResource.php');

  assert.match(returns, /requestPendingRefund/);
  assert.match(returns, /STATUS_APPROVED, ReturnRequest::STATUS_RECEIVED/);
  assert.match(returns, /'return:'\.\$locked->id\.'\:refund'/);
  assert.doesNotMatch(returns, /->process\(/);
  assert.match(resource, /creates a pending Phase 8 refund only/);
  assert.match(resource, /FINANCE_MANAGE/);
});

test('customer return path enforces ownership and excludes internal audit reasons', () => {
  const controller = read('app/Http/Controllers/ReturnRequestController.php');
  const customerView = read('resources/views/orders/show.blade.php');
  const service = read('app/Services/ReturnRequestService.php');

  assert.match(controller, /\$order->user_id === \$customer->id/);
  assert.match(service, /Only the order owner can request a return/);
  assert.match(service, /Only the return request owner can cancel it/);
  assert.doesNotMatch(customerView, /returnRequest->events/);
  assert.doesNotMatch(customerView, /returnRequest->customer_note/);
});
