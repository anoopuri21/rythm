# Phase 11 — Customer experience, search and merchandising

**Status:** IN PROGRESS — implementation chunk 1
**Owner:** Agent 0
**Activated:** 30 August 2026
**Deployment:** inactive; Phase 18 and Agent 10 remain inactive

## Boundary and safety contract

Phase 11 improves discovery and customer experience without changing the approved
fulfillment, refund, return or tax behavior. Returns and tax values remain disabled;
no legal, warranty, shipping or price-drop promise is introduced. The baseline is
MySQL 8/shared-host safe: bounded SQL, no persistent search daemon, no Redis or
external search dependency, and no unbounded catalogue export.

Excluded from this phase unless separately approved: gift cards, store credit,
abandoned-cart marketing, price-drop alerts, competitor content/media, and any
commercial claim not supported by an approved source.

## Chunk 1 implementation delivered

- **Weighted search:** exact name/SKU matches rank above contains matches; featured
  rank remains the deterministic tie-breaker.
- **Search coverage:** product name, SKU, brand, category slug/name, normalized
  attribute values and active variant attributes are searchable.
- **Bounded typo tolerance:** a five-token/80-character input limit and a one-character
  stem fallback cover common trailing-key typos without a daemon or broad fuzzy index.
- **Admin-managed merchandising:** `product_merchandising_rules` supports related,
  complementary and frequently-bought-together links, priority, activation and
  optional start/end windows. Curated related products precede the existing safe
  category fallback; product price and stock are never copied into a rule.
- **Least-privilege administration:** the new Filament resource is governed by the
  existing catalogue permission and audit observer. Catalogue managers can curate
  links without accessing finance, returns or tax controls.
- **Consent-safe stock requests:** authenticated customers can explicitly opt in to a
  stock-availability email request. Only the user/product/variant target and consent
  timestamp are stored; no guest email collection or marketing subscription is added.
  A bounded `back-in-stock:notify --limit=...` command uses the central notification
  ledger and idempotent delivery reservation. Enabling its cPanel schedule remains
  an operations/release qualification gate.

## Verification plan

1. Run the focused Phase 11 PHP feature test for search coverage, typo fallback,
   curated ordering, consent/idempotency and authenticated UI behavior.
2. Run the full PHP suite after every migration change.
3. Run `npm run test:automation` and `npm run build` after storefront changes.
4. On owner runtime, execute the migration/status checks against an isolated MySQL 8
   database and inspect the admin resource with a catalogue-manager account.
5. Qualify responsive/keyboard states for search, empty results, out-of-stock and
   consent feedback before Phase 11 can become `QA` or `COMPLETE`.

## Current verification record

- `npm ci --no-audit --no-fund`: passed in the disposable workspace dependency directory.
- `npm run test:automation`: **104 passed, 0 failed**.
- `npm run build`: passed; Vite emitted a valid production manifest.
- `git diff --check`: passed.
- PHP/Composer/MySQL execution is not available in Arena, so the new migration and focused PHP feature test remain owner-runtime gates and are not claimed as locally passed.

## Remaining Phase 11 chunks

- Add complementary/frequently-bought-together storefront placements with truthful
  empty states and product-level visibility checks.
- Add responsive/SEO qualification evidence for search and recommendation states.
- Add temporary realistic-catalog query/performance evidence and authorization review.
- Reconcile owner-reported external evidence without presenting it as local execution.

## Exit gate

Phase 11 may be marked `COMPLETE` only after the implementation, focused/full PHP
suite, automation/build checks, isolated MySQL migration/status checks, rendered
responsive/accessibility review and owner-side conversion UAT are recorded. Until
then it remains `IN PROGRESS`/`QA` as appropriate.
