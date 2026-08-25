# Phase 2 — MySQL Schema and Domain Architecture

**Date:** 25 August 2026
**Status:** INDEPENDENT IMPLEMENTATION COMPLETE / MYSQL FORWARD-MIGRATION EVIDENCE PENDING
**Target:** MySQL Community Server 8.4.3 with Laravel 13.24.0

## 1. Locked Invariants

1. Persistent `rhythm_db` receives forward migrations only.
2. Destructive tests and sample seeders never target persistent/UAT/production data.
3. Money remains `DECIMAL(12,2)` with three-character ISO currency codes; application float removal is a later controlled refactor, not an implicit schema conversion.
4. A product or variant stock change must be atomic, non-negative and traceable.
5. First paid transition changes inventory once; replay remains a no-op.
6. Paid cancellation restores the same inventory source once and creates a pending refund request without claiming gateway completion.
7. Gateway event payloads are not stored blindly; event identity, hash, processing state and redacted metadata are the safe foundation.
8. Reference design facets are data-driven by category; “color” is not universal across musical instruments.

## 2. Existing Strengths

- Unique product/variant SKU and product/category/brand slugs.
- Foreign keys with explicit delete behavior in core commerce tables.
- Unique gateway order/payment references.
- Locked transactional paid transition and variant-aware stock mutation.
- Durable pending refund table and order/payment state separation.
- Decimal money columns and immutable order item/address snapshots.

## 3. Immediate Phase 2 Structures

### Catalog attributes

- `product_attributes`: semantic definition (`type`, unit, filterable, variant-defining, sort order).
- `product_attribute_values`: normalized selectable values with optional color swatch and metadata.
- `category_product_attribute`: which facets apply to each category and whether required/filterable.
- `product_attribute_value_product`: product-level assignments.
- `product_attribute_value_product_variant`: variant-level assignments.

This supports guitar body type/pickup configuration, keyboard key count/action, drum configuration, microphone pattern, connectivity, finish/color and other category-specific facets without schema changes per category.

### Inventory movement ledger

- Signed quantity delta and resulting balance.
- Product or variant source, optional order and actor.
- Movement type, reason, metadata and occurrence timestamp.
- Globally unique idempotency key per business effect.
- Stock remains the fast current balance on product/variant; ledger is the immutable audit trail.

### Payment event foundation

- Gateway + gateway event ID unique.
- Event type, processing status, optional order/payment links.
- Payload SHA-256 hash and explicitly redacted metadata only.
- Received/processed timestamps and bounded failure message.
- No raw webhook secrets, signatures, cards or unrestricted PII payload.

### Order idempotency foundation

A nullable unique `idempotency_key` is added for Phase 4 checkout integration. Existing orders remain valid and no synthetic keys are backfilled.

## 4. Explicit Deferrals

| Area | Reason | Target phase |
|---|---|---|
| Shipping zones/rates/shipments | Carrier, PIN-code and partial-shipment rules require business approval | Phase 4/8 commerce operations |
| GST/HSN and invoice numbering | Requires qualified accounting approval | Phase 4/8 compliance |
| Partial refunds and credit notes | Requires gateway and business policy plus current one-refund service refactor | Phase 4/8 payments |
| Q&A/comments | Product-only vs broader scope unresolved | Phase 5 |
| Full RBAC permission matrix | Staff roles and 2FA policy require operational approval | Phase 7 |
| Notification preferences/database notifications | Event matrix and consent policy belong to notification phase | Phase 4/8 |
| Import staging tables | Scraper language/source schema and content rights unresolved | Phase 6 |

## 5. Index and Delete Policy

- Facet reads index category/attribute sort order and attribute/value sort order.
- Assignment pivots use composite primary keys to prevent duplicates.
- Deleting a catalog attribute cascades its values/applicability/assignments.
- Deleting a product/variant cascades its attribute assignments but inventory movement source IDs become null so the audit record survives.
- Deleting an order sets the optional ledger/event order reference null; commerce history should normally be retained, not deleted.
- Payment events survive payment deletion with nullable references; gateway identity remains unique.
- Custom index names stay below MySQL's 64-character identifier limit.

## 6. Rollout and Rollback

Forward rollout creates independent tables first, then adds nullable columns. It does not rewrite existing rows or lock large tables for backfill. Rollback drops nullable additions and new tables in reverse dependency order. Production rollback still requires backup/change-window review; migration `down()` support is not permission for destructive owner action.

## 7. Acceptance Evidence Required

- Isolated clean migration and seed.
- Schema and relationship tests.
- Paid/replay/cancellation inventory-ledger tests.
- Full regression and formatting/syntax gates.
- Owner-reported successful `php artisan migrate --force` on persistent MySQL 8.4.3 `rhythm_db`.


## 8. Independent Verification — 25 August 2026

- Three forward/rollback migrations passed on isolated SQLite.
- Clean isolated migration and sample seed passed; no persistent/UAT data was touched.
- Targeted schema/checkout/cancellation suite: **43 tests / 144 assertions passed**.
- Full regression: **225 tests / 753 assertions passed**.
- Changed PHP syntax and Pint: passed.
- Composer audit: no security advisories.
- Exact MySQL identifier review: after the owner's first run exposed an overlong framework-generated pivot foreign-key name, every catalog foreign key was explicitly named; the longest catalog identifier is now 31 characters.
- Failed-DDL recovery: migration `000004` safely removes only its own new, unlogged partial catalog tables before retrying, preserving every pre-existing application table and row.

### Remaining gate

The owner must run only `php artisan migrate --force` followed by `php artisan migrate:status` against persistent MySQL 8.4.3 `rhythm_db`. Do not run seeders or destructive test commands. Agent 0 cannot mark Phase 2 complete until that forward-migration output is supplied.
