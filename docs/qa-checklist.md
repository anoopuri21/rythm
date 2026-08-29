# Phase 7 QA checklist

Use this as the release evidence sheet. Record environment, commit, tester, date, result and evidence link for every run. Never test against an unrelated or persistent production database and never copy secrets into evidence.

## Entry gate

- [ ] Candidate commit is the approved `rhythm-uat` commit and the tree is clean.
- [ ] PHP 8.3, Laravel 12, MySQL 8, Node and browser versions are recorded.
- [ ] QA host points to an isolated Rythme database (`rhythm_db` or a disposable clone), not `maverick_academy`.
- [ ] `APP_ENV` is non-production, debug output is not public, Razorpay uses test mode, mail uses a safe staging recipient, and fake payments are disabled for browser UAT.
- [ ] Database and media snapshots exist before stateful tests.
- [ ] Migrations complete and `php artisan about`, `config:show session`, `route:list` and `schedule:list` show expected configuration.

## Automated release gate

Run from a disposable external QA copy with dependencies outside the Arena workspace constraint:

```text
composer validate --strict
composer audit
php artisan test
npm ci --no-audit --no-fund
npm audit --omit=dev
npm run build
npm run test:automation
```

Required focused suites:

```text
php artisan test tests/Feature/LaunchSmokeTest.php
php artisan test tests/Feature/CheckoutTest.php tests/Feature/PaymentEventLedgerTest.php tests/Feature/PaymentRetryTest.php tests/Feature/RefundOperationsTest.php
php artisan test tests/Feature/AdminGovernanceTest.php tests/Feature/AdminOpsTest.php
php artisan test tests/Feature/CatalogueImportTest.php tests/Feature/ShopLargeCatalogueQualificationTest.php
```

- [ ] All tests pass with no skipped release-critical case.
- [ ] Vite manifest and hashed assets exist; browser console has no missing asset error.
- [ ] Dependency audit findings are zero critical/high or have owner-approved remediation evidence.

## Checkout paths

Use unique test order identities and reconcile every attempt in Razorpay and the local payment ledger.

- [ ] Guest cart persists, login is required for checkout, and guest cart merges once after login.
- [ ] Existing address and new valid address both proceed; invalid phone, PIN and required fields are rejected.
- [ ] Empty cart, inactive product, inactive variant, insufficient stock and stock changed after cart add fail without creating an invalid order.
- [ ] Coupon valid, invalid, expired, usage-limited, minimum-order and maximum-discount behavior matches server totals.
- [ ] Shipping, discount, tax and grand total match cart, order, payment paise amount, confirmation, account page and invoice.
- [ ] Happy-path Razorpay test capture confirms exactly one order, one stock movement, one notification request and one signed success page.
- [ ] Direct/unsigned success URL is forbidden; another user cannot view or confirm the order.
- [ ] Browser refresh/back/repeated click does not duplicate order, payment, coupon use or stock decrement.
- [ ] Misconfigured real gateway fails closed before order/inventory reservation; fake payment mode is not available in UAT/production.

## Payment, replay and refund matrix

- [ ] Invalid callback signature and unknown gateway order cannot mutate payment/order state.
- [ ] Valid callback verifies provider payment, captured status, gateway order, payment ID, amount and currency.
- [ ] Duplicate valid callback is idempotent: no duplicate payment, status history, inventory movement or notification.
- [ ] Invalid webhook signature returns 400 and creates no trusted event.
- [ ] `payment.authorized` records authorized state only; order remains pending and stock is not captured.
- [ ] `payment.captured` and `order.paid` with full invariant evidence transition to paid once.
- [ ] Signed unrelated webhook receives fast 200/ignored without commerce mutation.
- [ ] Replayed processed/failed event receives fast 2XX; reused event ID with different payload is rejected as a conflict.
- [ ] Signed malformed/mismatched amount, currency, order, status or capture flag creates failed operational evidence, returns the documented acknowledgement and never grants paid state.
- [ ] Retry payment is owner-only, bounded/rate-limited and cannot create duplicate active attempts.
- [ ] Partial refund, remaining/full refund and paid cancellation refund use distinct idempotent records and never exceed captured value.
- [ ] Duplicate refund request returns/reuses the same reservation; uncertain provider outcome is reconciled before retry.
- [ ] Non-Finance roles cannot request/process refunds; Finance cannot alter fulfilment status.

## Mobile and responsive UI

Test real browsers at 360×800, 390×844, 768×1024 and desktop 1440×900. Include iOS Safari and Android Chrome where available.

For home, shop/search, product, cart, login, checkout, account/order tracking and order success:

- [ ] No horizontal scrolling, clipped text, overlapping fixed controls or inaccessible content at 200% zoom.
- [ ] Navigation/category drawer, search, filters, carousel controls, accordions and modals are keyboard/touch operable and dismissible.
- [ ] Product images preserve aspect ratio, lazy images load, hero/mobile crops are correct and missing media has a stable fallback.
- [ ] Add-to-cart, quantity controls, variant selection, coupon and checkout primary action have clear loading/disabled/error/success states.
- [ ] Forms use visible labels, useful validation, correct mobile input types and touch targets of approximately 44px.
- [ ] Order totals and payment button remain readable without obscuring content; rotation and browser back do not corrupt state.
- [ ] Console has no JavaScript error; network panel has no runtime hotlink dependency for product media.

## Admin permissions and workflows

Run with a separate account for every role; do not only inspect hidden navigation—open direct resource URLs and attempt allowed/denied actions.

- [ ] `customer` cannot enter Filament.
- [ ] `super_admin` can perform all approved operations and inspect audit history.
- [ ] Legacy `admin` compatibility is confirmed, no new account is assigned it, and migration owner is recorded.
- [ ] Catalogue Manager can manage products/categories/brands/media but cannot access orders, finance, settings, staff or audit.
- [ ] Order Manager can view/manage fulfilment and customers but cannot refund or change catalogue/settings.
- [ ] Support can view orders/customers and moderate interactions but cannot mutate orders, finance, content or catalogue.
- [ ] Marketing can manage content/coupons/newsletter and view catalogue but cannot access orders/customers/finance/settings.
- [ ] Finance can view orders/customers and process refunds but cannot change fulfilment/catalogue/content/settings.
- [ ] Required MFA works, direct forbidden routes return 403, the final Super Admin cannot be demoted, and deletion/role/content/financial actions are audit-evidenced.
- [ ] Dashboard cards and latest-order data appear only to roles with the corresponding permission.

## Product import, upload and rendering

- [ ] Acquisition/import begins with dry-run report; malformed manifest, duplicate provenance/SKU and missing media are rejected or reported.
- [ ] Commit rerun is idempotent; imported products remain inactive with zero/unverified stock until review.
- [ ] Activation requires content, price, real stock and local commercial-media attestations; bulk activation remains capped.
- [ ] JPEG, PNG, WebP and explicitly supported AVIF paths work within each field limit.
- [ ] SVG, executable/polyglot, wrong MIME, oversized and too-many-file uploads are rejected.
- [ ] Product gallery, OG, hero desktop/mobile, category icon, brand logo and homepage media render from local managed storage.
- [ ] Replacing/deleting media cleans its database/storage relation without deleting media owned by another model.
- [ ] Thumbnail/WebP conversion queue drains under the bounded worker and original fallback renders if conversion is pending/failed.

## 500+ catalogue qualification

Automated SQLite coverage creates 520 active products and checks filters, sort, 12-item pagination, 44 pages and bounded query count. Production qualification must use MySQL 8 with representative relations/media.

- [ ] Seed/import at least 500 products into isolated QA; record exact active/inactive/media totals.
- [ ] Cold and warm home, shop, search, category, brand, price, stock and sort requests return correct results.
- [ ] Record p50/p95 response time, SQL query count and peak memory for each critical page.
- [ ] Run `EXPLAIN ANALYZE` for slow shop/search/admin queries; no unexplained full scan or filesort remains.
- [ ] Pagination URLs and filters remain stable through first/middle/last pages; admin list/search remains usable.
- [ ] Queue conversion/import workload remains bounded and does not exhaust shared-host memory/time limits.

## Exit evidence

- [ ] Core journeys, payment/refund matrix, every role, import/media and 500+ catalogue checks pass.
- [ ] No unresolved critical/high defect; medium/low defects have owner, target and accepted launch impact.
- [ ] `docs/release-checklist.md` and `docs/rollback-plan.md` are reviewed in a tabletop exercise.
- [ ] Agent 0 records technical sign-off; owner separately authorizes deployment/Phase 18.