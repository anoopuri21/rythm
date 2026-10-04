import test from 'node:test';
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';

const read = (path) => readFile(new URL(`../../${path}`, import.meta.url), 'utf8');

/**
 * NO-HARDCODE rule (docs/NO_HARDCODE_PLAN.md): every storefront value a
 * merchant might change must live in the database and be editable from
 * Admin → Settings. These tests fail the moment a literal creeps back in.
 */

/** Keys that are intentionally not admin-editable form fields. */
const NON_EDITABLE_DEFAULTS = [
  'mail_from_verified_at',
  'mail_from_pending_address',
  'mail_from_pending_token',
  'mail_from_pending_sent_at',
  'mail_from_status_display',
];

function defaultsKeys(source) {
  const block = source.slice(source.indexOf('public const DEFAULTS = ['));
  return [...block.slice(0, block.indexOf('];')).matchAll(/^\s{8}'([a-z0-9_]+)' =>/gm)].map((m) => m[1]);
}

function formFields(source) {
  return new Set([...source.matchAll(/(?:TextInput|Textarea|Select|Toggle)::make\('([a-z0-9_]+)'\)/g)].map((m) => m[1]));
}

test('every site setting default has a matching admin form field', async () => {
  const [service, settingsPage] = await Promise.all([
    read('app/Services/SiteSettingsService.php'),
    read('app/Filament/Pages/Settings.php'),
  ]);

  const keys = defaultsKeys(service);
  assert.ok(keys.length >= 40, `expected a full DEFAULTS table, got ${keys.length}`);

  const fields = formFields(settingsPage);
  const missing = keys.filter((k) => !NON_EDITABLE_DEFAULTS.includes(k) && !fields.has(k));

  assert.deepEqual(missing, [], `settings with no admin field: ${missing.join(', ')}`);
});

test('storefront counts and business rules come from settings, not literals', async () => {
  const [service, homepage, shop, footer, marquee, popup, confidence, hero, brands, banners, promos] =
    await Promise.all([
      read('app/Services/SiteSettingsService.php'),
      read('app/Services/HomepageDataService.php'),
      read('resources/views/livewire/shop-index.blade.php'),
      read('resources/views/components/footer.blade.php'),
      read('resources/views/home/_offer-marquee.blade.php'),
      read('resources/views/home/_offer-popup.blade.php'),
      read('resources/views/home/_confidence.blade.php'),
      read('resources/views/home/_hero.blade.php'),
      read('resources/views/home/_brands.blade.php'),
      read('resources/views/home/_category-banners.blade.php'),
      read('resources/views/home/_promo-banners.blade.php'),
    ]);

  // No literal row size anywhere in the audited surfaces.
  assert.doesNotMatch(homepage, /->limit\(\d+\)/);
  assert.doesNotMatch(shop, /array_slice\(\$categories, 0, \d+\)/);
  assert.doesNotMatch(footer, /array_slice\(\$cats, 0, \d+\)|->take\(\d+\)/);
  for (const [name, view] of [['confidence', confidence], ['hero', hero], ['brands', brands],
    ['category-banners', banners], ['promos', promos], ['marquee', marquee]]) {
    assert.doesNotMatch(view, /->take\(\d+\)/, `${name} still has a literal take()`);
  }

  // Offer eligibility window is a setting in BOTH offer surfaces.
  for (const [name, view] of [['marquee', marquee], ['popup', popup]]) {
    assert.doesNotMatch(view, /discount.*>= 10|discount.*<= 50/, `${name} hardcodes the 10-50% window`);
    assert.match(view, /\$minDiscount/);
    assert.match(view, /\$maxDiscount/);
  }
  assert.match(popup, /use \(\$minDiscount, \$maxDiscount\)/);

  // Every count reads through the clamped accessor.
  for (const key of ['home_bestsellers_limit', 'home_new_arrivals_limit', 'home_trending_limit',
    'home_deals_limit', 'home_brands_limit']) {
    assert.match(homepage, new RegExp(`getCount\\('${key}'\\)`), `${key} is not used`);
  }
  assert.match(homepage, /slugList\('recently_launched_slugs'\)/);
  assert.doesNotMatch(homepage, /'roland-fp-30x-digital-piano'/);
  assert.match(shop, /getCount\('shop_category_shortcuts'/);
  assert.match(service, /'recently_launched_slugs' =>/);
});

test('brand and media resolve from settings with config only as fallback', async () => {
  const [service, footer, navbar, hero] = await Promise.all([
    read('app/Services/SiteSettingsService.php'),
    read('resources/views/components/footer.blade.php'),
    read('resources/views/components/navbar.blade.php'),
    read('resources/views/home/_hero.blade.php'),
  ]);

  for (const view of [footer, navbar, hero]) {
    assert.doesNotMatch(view, /config\('rythme\.brand_name'\)/);
    assert.doesNotMatch(view, /config\('rythme\.logo_url'\)/);
  }
  assert.match(footer, /brandOrMedia\('brand_name'\)/);
  assert.match(footer, /brandOrMedia\('brand_logo_url'\)/);
  assert.match(hero, /brandOrMedia\('brand_name'\)/);

  // config/rythme.php stays reachable only through the fallback map.
  assert.match(service, /private const CONFIG_FALLBACKS/);
  assert.match(service, /'brand_logo_url' => 'rythme\.logo_url'/);
});

test('currency symbol is one admin setting rendered through @currency', async () => {
  const [provider, service] = await Promise.all([
    read('app/Providers/AppServiceProvider.php'),
    read('app/Services/SiteSettingsService.php'),
  ]);

  assert.match(provider, /Blade::directive\('currency'/);
  assert.match(provider, /currencySymbol\(\)/);
  assert.match(service, /public function currencySymbol\(\): string/);
  assert.match(service, /'currency_symbol' =>/);

  // The only literal symbol left anywhere in Blade must sit inside a comment.
  const { glob } = await import('node:fs/promises');
  const offenders = [];

  for await (const file of glob('resources/views/**/*.blade.php', { cwd: new URL('../..', import.meta.url) })) {
    const text = await read(file);
    const withoutComments = text.replace(/\{\{--[\s\S]*?--\}\}/g, '');
    if (withoutComments.includes('₹')) offenders.push(file);
    // A directive preceded by a word character would not compile.
    assert.doesNotMatch(withoutComments, /[A-Za-z0-9_]@currency/, `${file}: @currency needs a non-word char before it`);
  }

  assert.deepEqual(offenders, [], `literal ₹ outside Blade comments: ${offenders.join(', ')}`);
});

test('USP strip renders admin content instead of hardcoded markup', async () => {
  const [strip, seeder, resource, model, migration] = await Promise.all([
    read('resources/views/home/_usp-strip.blade.php'),
    read('database/seeders/HomepageDataSeeder.php'),
    read('app/Filament/Resources/HomepageBlockResource.php'),
    read('app/Models/HomepageBlock.php'),
    read('database/migrations/2026_10_04_000001_add_icon_to_homepage_blocks_table.php'),
  ]);

  assert.match(strip, /\$homepage\['usps'\]/);
  assert.match(strip, /@foreach\(\$usps as \$usp\)/);
  assert.match(strip, /@if\(\$usps->isNotEmpty\(\)\)/);
  // No copy and no fixed item count left in the partial.
  assert.doesNotMatch(strip, /Instrument-first|order tracking|Category-led|Secure checkout|Stock-aware/);
  assert.equal((strip.match(/usp-strip__item/g) ?? []).length, 1);

  // The icon is data too: column + admin select + glyph component.
  assert.match(migration, /string\('icon', 40\)->nullable\(\)/);
  assert.match(model, /'icon'/);
  assert.match(resource, /Select::make\('icon'\)/);
  assert.match(strip, /<x-ui\.icon :name="\$usp->icon" \/>/);

  // Defaults are seeded, and superseded rows are deactivated not destroyed.
  assert.equal((seeder.match(/'section_key' => 'usp'/g) ?? []).length, 5);
  assert.match(seeder, /->update\(\['is_active' => false\]\)/);
  assert.doesNotMatch(seeder, /->where\('section_key', 'usp'\)[\s\S]{0,240}->delete\(\)/);
});

test('changing settings rebuilds the cached storefront payloads', async () => {
  const settingsPage = await read('app/Filament/Pages/Settings.php');

  assert.match(settingsPage, /HomepageDataObserver::flush\(\)/);
  assert.match(settingsPage, /app\(CategoryService::class\)->flush\(\)/);
  assert.match(settingsPage, /Section::make\('Storefront'\)/);
  assert.match(settingsPage, /Section::make\('Brand & media'\)/);
});
