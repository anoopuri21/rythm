import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';

const read = (path) => readFileSync(new URL(`../../${path}`, import.meta.url), 'utf8');

test('Phase 11 search stays bounded, weighted and MySQL/shared-host safe', () => {
  const service = read('app/Services/ProductQueryService.php');
  const product = read('app/Models/Product.php');
  const migration = read('database/migrations/2026_08_30_000001_create_product_merchandising_rules_table.php');

  assert.match(service, /search_relevance/);
  assert.match(service, /orWhereHas\('variants'/);
  assert.match(product, /scopeWithAvailableVariantStock/);
  assert.match(product, /hasAvailableStock/);
  assert.match(service, /mb_substr\(\$term, 0, 80\)/);
  assert.match(service, /array_slice\(\$tokens, 0, 5\)/);
  assert.match(service, /MAX_BRAND_FILTERS/);
  assert.match(service, /MAX_ATTRIBUTE_FILTERS/);
  assert.match(service, /MAX_ATTRIBUTE_VALUES/);
  for (const field of ['products.name', 'products.sku', "orWhereHas('brand'", "orWhereHas('category'", "orWhereHas('attributeValues'"]) {
    assert.ok(service.includes(field), `missing search field: ${field}`);
  }
  assert.match(migration, /product_merchandising_rules_unique/);
  assert.match(migration, /product_merchandising_source_idx/);
  assert.doesNotMatch(service, /MeiliSearch|Typesense|persistent.*daemon/i);
});

test('Phase 11 merchandising rules are admin-managed and price-safe', () => {
  const model = read('app/Models/ProductMerchandisingRule.php');
  const resource = read('app/Filament/Resources/ProductMerchandisingRuleResource.php');
  const policy = read('app/Policies/MerchandisingRulePolicy.php');

  for (const type of ['TYPE_RELATED', 'TYPE_COMPLEMENTARY', 'TYPE_FREQUENTLY_BOUGHT_TOGETHER']) {
    assert.match(model, new RegExp(type));
  }
  assert.match(model, /A product cannot recommend itself/);
  assert.match(resource, /Only curated product links are shown/);
  assert.match(resource, /different\('source_product_id'\)/);
  assert.match(policy, /CATALOGUE_MANAGE/);
});

test('Phase 11 stock requests require verified consent and a bounded command', () => {
  const migration = read('database/migrations/2026_08_30_000002_create_back_in_stock_subscriptions_table.php');
  const service = read('app/Services/BackInStockSubscriptionService.php');
  const component = read('app/Livewire/AddToCart.php');
  const command = read('app/Console/Commands/NotifyBackInStock.php');
  const accountController = read('app/Http/Controllers/AccountController.php');
  const feature = read('tests/Feature/PhaseElevenCustomerExperienceTest.php');

  assert.match(migration, /back_in_stock_user_target_unique/);
  assert.match(migration, /consent_at/);
  assert.match(service, /Please confirm stock-availability email consent/);
  assert.match(service, /hasVerifiedEmail/);
  assert.match(service, /abort\(403\)/);
  assert.match(accountController, /->pending\(\)/);
  assert.match(accountController, /with\(\['product', 'variant'\]\)/);
  assert.match(service, /targetKey/);
  assert.match(component, /notifyConsent/);
  assert.match(component, /requestStockNotification/);
  assert.match(command, /--limit=100/);
  assert.match(command, /limit > 500/);
  assert.match(command, /! \$variant->is_active/);
  assert.match(component, /Please choose a valid option/);
  assert.match(feature, /test_notification_command_rejects_limits_outside_the_worker_bound/);
  assert.match(feature, /test_delivery_skips_a_customer_whose_email_is_no_longer_verified/);
  assert.match(feature, /test_search_ignores_inactive_variant_attributes/);
});

test('Phase 11 stock notifications use the central delivery ledger and mail only', () => {
  const listener = read('app/Listeners/HandleBackInStockNotification.php');
  const notification = read('app/Notifications/BackInStockNotification.php');
  const provider = read('app/Providers/AppServiceProvider.php');
  const retry = read('app/Services/NotificationRetryService.php');

  assert.match(listener, /recordEvent/);
  assert.match(listener, /hasVerifiedEmail/);
  assert.match(listener, /reserveDelivery/);
  assert.match(listener, /BackInStockNotification::class/);
  assert.match(notification, /return \['mail'\]/);
  assert.match(provider, /BackInStockNotificationRequested::class/);
  assert.match(retry, /HandleBackInStockNotification/);
  assert.doesNotMatch(notification, /database/);
});

test('Phase 11 product recommendations keep truthful empty states and current product pricing', () => {
  const controller = read('app/Http/Controllers/ProductController.php');
  const accountController = read('app/Http/Controllers/AccountController.php');
  const accountFeature = read('tests/Feature/AccountTest.php');
  const view = read('resources/views/product/show.blade.php');
  const addToCartView = read('resources/views/livewire/add-to-cart.blade.php');
  const shopCard = read('resources/views/components/shop-card.blade.php');
  const minimalCard = read('resources/views/components/minimal-product-card.blade.php');
  const wishlistView = read('resources/views/livewire/wishlist-page.blade.php');
  const accountView = read('resources/views/account/index.blade.php');
  const plan = read('tasks/PHASE_11_CUSTOMER_EXPERIENCE_PLAN.md');

  assert.match(controller, /TYPE_COMPLEMENTARY/);
  assert.match(controller, /TYPE_FREQUENTLY_BOUGHT_TOGETHER/);
  assert.match(view, /\$complementary->isNotEmpty\(\)/);
  assert.match(view, /\$frequentlyBought->isNotEmpty\(\)/);
  assert.match(view, /Prices, stock and availability/);
  assert.match(view, /\$hasAvailableStock/);
  assert.match(controller, /canonical_url/);
  assert.match(controller, /robots.*index, follow/s);
  assert.match(controller, /hasAvailableStock/);
  assert.match(addToCartView, /\$stock <= 0/);
  assert.match(shopCard, /compare_at_price > \(float\) \$product->price/);
  assert.match(shopCard, /hasAvailableStock\(\)/);
  assert.match(minimalCard, /\$stock > 0 \? 'In stock' : 'Out of stock'/);
  assert.match(wishlistView, /compare_at_price > \(float\) \$product->price/);
  assert.match(accountController, /cancelBackInStockAlert/);
  assert.match(accountController, /stockAlertCount/);
  assert.match(accountController, /paginate\(12, \['\*'\], 'stock_alert_page'\)/);
  assert.match(accountFeature, /test_account_paginates_stock_alerts_without_changing_the_total/);
  assert.match(accountView, /hasPages\(\)/);
  assert.match(accountView, /account\.stock-alerts\.destroy/);
  assert.match(accountView, /not a marketing subscription/i);
  assert.match(plan, /Gift cards.*abandoned-cart marketing.*price-drop alerts/is);
});
