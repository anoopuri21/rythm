import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';

const read = (path) => readFileSync(new URL(`../../${path}`, import.meta.url), 'utf8');

test('tax calculation stays disabled until explicit professional enablement', () => {
  const settings = read('app/Services/SiteSettingsService.php');
  const orders = read('app/Services/OrderService.php');
  const page = read('app/Filament/Pages/Settings.php');

  assert.match(settings, /'tax_rules_enabled' => '0'/);
  assert.match(orders, /get\('tax_rules_enabled', '0'\) === '1'/);
  assert.match(page, /Enable only after professional approval/);
  assert.match(page, /No rate is assumed/);
});

test('optional product classification is copied into immutable order-line snapshots', () => {
  const migration = read('database/migrations/2026_08_29_000008_add_optional_tax_classification_snapshots.php');
  const product = read('app/Models/Product.php');
  const line = read('app/Models/OrderItem.php');
  const orders = read('app/Services/OrderService.php');

  for (const field of ['hsn_code', 'tax_classification', 'tax_rate']) {
    assert.match(migration, new RegExp(`['\"]${field}['\"]`));
    assert.match(product, new RegExp(`['\"]${field}['\"]`));
  }
  for (const field of [
    'hsn_code_snapshot',
    'tax_classification_snapshot',
    'tax_rate_snapshot',
    'taxable_amount_snapshot',
    'tax_amount_snapshot',
    'tax_calculation_enabled_snapshot',
    'tax_destination_region_snapshot',
  ]) {
    assert.match(migration, new RegExp(field));
    assert.match(line, new RegExp(field));
    assert.match(orders, new RegExp(field));
  }
  assert.match(line, /tax snapshots are immutable after checkout/);
});

test('tax framework does not claim invoice numbering or jurisdictional component treatment', () => {
  const migration = read('database/migrations/2026_08_29_000008_add_optional_tax_classification_snapshots.php');
  const orders = read('app/Services/OrderService.php');

  assert.doesNotMatch(migration, /invoice_number|credit_note_number|cgst|sgst|igst/i);
  assert.doesNotMatch(orders, /cgst|sgst|igst/iu);
  assert.match(orders, /tax_destination_region_snapshot/);
});
