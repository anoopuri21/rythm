# Database Optimization and Write-Safety Plan

**Database target:** MySQL 8.x. Persistent UAT must not be used for destructive qualification.

## Index inventory

Existing schema already provides unique identities for product/variant SKUs, slugs, order numbers, checkout idempotency, gateway identities, payment events, refunds and inventory movements. Foreign keys cover aggregate ownership.

Phase 2 adds storefront composites for active product price, stock, creation time and featured order. Phase 3 adds:

| Index | Query supported |
|---|---|
| `orders_customer_timeline_idx (user_id, placed_at, id)` | account order history |
| `orders_payment_operations_idx (payment_status, placed_at, id)` | finance/reconciliation queues |
| `payments_order_state_idx (order_id, status, created_at)` | latest/eligible payment attempt |
| `variants_product_stock_idx (product_id, is_active, stock)` | variant availability and activation checks |

Run `EXPLAIN ANALYZE` with representative data before adding further indexes. Every index increases import/write cost; do not index low-cardinality columns alone without a demonstrated query.

## Atomic write rules

- Checkout order, snapshots and initial history share one transaction.
- Checkout idempotency uses a unique key; duplicate-key reconciliation returns only the same owner’s winning order.
- Payment capture locks order and payment, writes capture, decrements stock, appends movements, updates aggregate states and writes history in one transaction.
- Inventory service operations are independently transaction-safe and idempotent, even when invoked outside an existing order transaction.
- Coupons lock usage counters and release at most once.
- Refund and fulfillment operations use durable idempotency identities and row locks.
- External provider calls must not be retried until durable state is reconciled.

## Normalization decisions

- Product attributes are normalized; variant `options` remains a purchase/display snapshot for compatibility.
- Base and variant inventory remain separate sellable sources; do not duplicate a variant quantity into product stock.
- Order items and addresses are denormalized intentionally as immutable purchase snapshots.
- Payment attempts, events and refunds remain separate tables; `orders.payment_status` is an aggregate read model.
- SEO and homepage content remain outside financial tables.

## Soft-delete policy

- Products: retained to support catalogue retirement while preserving historical references.
- Financial/order/payment/refund/inventory/event tables: no soft delete; retention and access policy applies.
- Ephemeral carts and content can be physically removed where existing foreign-key rules permit.
- Never add soft delete merely to hide an invalid state transition.

## Bulk import validation

Validation should be staged for speed:

1. Parse and cap input before database access.
2. Normalize SKU, slug, money, booleans and stock in memory.
3. Reject duplicate identities within the batch.
4. Resolve category/brand/attribute identities in bounded bulk queries.
5. Validate positive price and non-negative integer stock.
6. Persist in bounded chunks with transaction and deterministic import identity.
7. Keep imported products inactive until provenance, media, stock and publication review pass.
8. Never perform per-row remote media requests inside a database transaction.

## Seeder/factory qualification

- `DatabaseSeeder` now refuses production execution because it contains known local-development credentials and demonstration catalogue data.
- Product factories guarantee compare-at price is either null or above selling price.
- Order factories produce internally consistent subtotal/shipping/tax/total values.
- Factories are test fixtures, not stock, accounting or legal evidence.
- Seeded marketing copy must not be treated as production-approved content.

## Required runtime evidence

1. Back up and select `rhythm_db`; clear cached Laravel configuration.
2. Run migrations non-destructively.
3. Run focused inventory race/idempotency, order transition, payment replay, refund and fulfillment tests in an external disposable dependency runtime.
4. Run `EXPLAIN ANALYZE` for shop filters, account timeline, payment reconciliation and variant availability with 500+ products.
5. Record before/after row counts, index list and query plans without credentials or customer data.
