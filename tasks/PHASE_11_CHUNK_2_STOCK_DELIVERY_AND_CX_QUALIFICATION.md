# Phase 11 Chunk 2 — Stock delivery and customer-experience qualification

**Status:** QA GATE OPEN — implementation is pushed; owner-runtime and rendered evidence remain
**Branch:** `rhythm-uat`
**Deployment:** inactive; Phase 18 and Agent 10 remain inactive

## Scope

This chunk qualifies the Phase 11 implementation delivered in Chunk 1 and the
customer-managed stock-alert slice:

- weighted, bounded MySQL-safe product search;
- admin-managed related, complementary and frequently-bought-together placements;
- authenticated, verified-email, explicit-consent stock-availability requests;
- one central, idempotent, mail-only stock notification delivery;
- Account listing of active requests and ownership-checked cancellation;
- truthful empty states, current product prices/stock and product-page SEO metadata.

A stock-availability request is not a marketing subscription. Guest email collection,
price-drop alerts, abandoned-cart marketing, gift cards, persistent workers and
unapproved legal/tax/shipping/warranty promises remain outside this gate.

## Candidate implementation evidence

- `app/Services/ProductQueryService.php`
- `app/Services/BackInStockSubscriptionService.php`
- `app/Console/Commands/NotifyBackInStock.php`
- `app/Listeners/HandleBackInStockNotification.php`
- `app/Http/Controllers/AccountController.php`
- `resources/views/product/show.blade.php`
- `resources/views/account/index.blade.php`
- `tests/Feature/PhaseElevenCustomerExperienceTest.php`
- `tests/Feature/AccountTest.php`
- `tests/automation/phase11-customer-experience.test.mjs`

The customer-management slice was introduced at `29bbad1`; the current hardened
candidate is pushed at `00c08f8` with supervisor checkpoint `b716b01`. Product pages
now provide a self-canonical URL and variant-aware availability metadata, with focused
PHP coverage added for the rendered canonical and variant-availability output. Account
stock-alert results use a separate bounded paginator, while shared cards suppress false
sale prices and expose non-positive stock as unavailable.

## Arena-local evidence

The following checks passed locally without PHP or database access:

- `git diff --check`
- `npm run test:automation` — **109 passed, 0 failed**
- `npm run build` — Vite production build passed
- supervisor state validation — passed with deployment disabled

The PHP feature tests, migration execution, MySQL query evidence and rendered browser
checks are not claimed as Arena-local results because PHP, Composer and MySQL are not
available in this workspace.

## Owner-runtime qualification

Run these steps against an isolated test database or disposable restored copy. Do not
run `RefreshDatabase`, migrations, or the full PHP suite against persistent UAT or
production data. Do not share credentials or customer data in the evidence.

1. Confirm the active checkout is on `rhythm-uat` at or after `a4fa52f`, then install
   the normal project dependencies in the owner environment.
2. Point the test process at an isolated MySQL 8 database and verify the engine with
   `SELECT VERSION(), @@version_comment;`.
3. Run the Phase 11 and account tests:

   ```text
   php artisan test tests/Feature/PhaseElevenCustomerExperienceTest.php tests/Feature/AccountTest.php
   ```

4. Run the complete PHP regression suite in the isolated environment:

   ```text
   php artisan test
   ```

5. Verify migrations and routes without changing the persistent project database:

   ```text
   php artisan migrate:status
   php artisan route:list --path=account/stock-alerts
   ```

6. Exercise the bounded worker with a non-sending mail configuration or notification
   fake. Confirm invalid limits below 1 and above 500 are rejected, inactive products
   and inactive variants are skipped even when a variant still has stock, and repeated
   handling creates only one commerce event, one delivery reservation and one
   `notified_at` transition per request.
7. Confirm a request without consent, an unverified customer and an invalid/inactive
   variant are rejected. A tampered or stale Livewire variant selection must not be
   silently converted into a product-level request. Confirm a verified customer can request one active target,
   view it in Account, cancel it, and request it again only through the explicit
   consent flow.
8. Confirm a customer cannot cancel another customer's subscription: the request must
   return HTTP 403 and leave the other customer's row pending.

## Rendered responsive and accessibility review

Use real browser sessions at **1440×900**, **768×1024**, **390×844** and **360×800**.
For the product page and Account page, record only pass/fail summaries and viewport
sizes; do not attach customer PII.

- Product search, no-result, out-of-stock, stock-request success/error and variant
  states have no horizontal overflow, clipping or overlapping controls.
- Complementary and frequently-bought sections render only when their collections
  are non-empty; each card uses its current product price and stock state.
- Account Stock alerts is a distinct tab from notification/marketing preferences;
  active requests show the product/variant target and a usable cancellation form.
- Guest product visitors see a login path rather than an email field; consent copy
  says one stock-availability email and no marketing messages.
- Tabs, variant controls, cancellation forms and stock-request feedback are keyboard
  operable, labelled and exposed with useful status/error semantics.
- Product pages have one meaningful H1, breadcrumb navigation, canonical metadata and
  valid Product JSON-LD; no stale legal, warranty, tax or shipping promise appears.
- Run axe or the project accessibility checks at each viewport and confirm zero
  critical/serious violations, zero console errors and zero broken same-site links.

## Search and temporary realistic-catalogue evidence

Use the existing isolated-catalogue qualification only in a disposable test database.
The evidence must include:

- a catalogue above 500 products;
- bounded pagination and no unbounded export/query loop;
- search coverage for name, SKU, brand, category and normalized attributes;
- exact matches ranked ahead of contains/typo-stem matches;
- recorded query count and elapsed time for a shop request, with the environment and
  dataset size stated;
- no persistent search daemon, Redis requirement or third-party search credentials.

This is qualification evidence, not permission to add infrastructure or to claim a
production latency SLO before Phase 13 profiling.

## Acceptance boundary

Chunk 2 can be accepted only after the owner supplies the focused/full PHP result,
isolated MySQL migration/status result, notification idempotency result and rendered
responsive/accessibility/UAT summary. Until then Phase 11 remains **IN PROGRESS**.

Passing local Node/build checks or an implementation diff does not close the owner
runtime gates. Phase 12 cannot begin as a completed phase, and Phase 18/deployment
must remain inactive.
