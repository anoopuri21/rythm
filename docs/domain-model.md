# Rythme Domain Model

**Phase:** 3  
**Target:** Laravel 12, PHP 8.3, MySQL 8  
**Rule:** models express relationships and invariants; services/actions own business writes; controllers and Livewire components coordinate HTTP/UI only.

## Catalogue aggregate

- `Category` is hierarchical and owns many products.
- `Brand` owns many products.
- `Product` is the catalogue root: merchandising copy, SEO/display metadata, base commercial price, base stock, publication state and media.
- `ProductVariant` belongs to one product and owns an optional price override and its own stock source.
- `ProductAttribute` / `ProductAttributeValue` normalize filterable specifications. Pivot tables map values to products or variants.
- `ProductImportSource` stores acquisition provenance and publication/commercial-use review, separate from customer-facing copy.
- Product soft deletion is retained because historical order items and audit/provenance must survive catalogue retirement. Deactivation remains the normal publication control.

### Catalogue invariants

1. SKU and slug are unique; variant SKU is unique.
2. Price is positive before imported-product activation.
3. Compare-at price has promotional meaning only when above selling price.
4. A cart line selects either base-product inventory or one variant inventory source.
5. Imported products cannot activate without reviewed provenance, locally managed approved media and verified stock.
6. SEO/display fields never provide authoritative price, inventory or payment data.

## Cart and checkout aggregate

- `Cart` belongs to a user or guest session and owns `CartItem` rows.
- `CartItem` references a product and optional variant; quantity is intent, never a stock reservation.
- `CheckoutData` is an immutable boundary DTO for address, currency, coupon, notes and idempotency identity.
- `CouponService`, site settings and current catalogue rows calculate discount, shipping, tax and total inside order creation.

## Order aggregate

- `Order` is the durable commerce root and owns items, payments, refunds, inventory movements, state history and shipments.
- `OrderItem` is an immutable purchase snapshot: product/variant references may become null, while name, SKU, options, quantity and money remain.
- Shipping and billing addresses are order snapshots, deliberately separate from mutable account `Address` records.
- `OrderStatusHistory` is append-only state evidence.
- `Shipment`, `ShipmentItem` and shipment events normalize fulfillment independently from the commercial order.
- Orders are not soft deleted: financial records require durable retention and authorization, not recoverable UI deletion.

## Payment/refund aggregate

- `Payment` belongs to one order; gateway order/payment identities are unique with gateway scope.
- `PaymentEvent` is the inbound provider-event ledger and deduplicates callbacks/webhooks.
- `Refund` belongs to an order and payment, uses an idempotency key, tracks approval and provider outcome, and supports bounded partial operations.
- Order payment summary (`orders.payment_status`) is a read-efficient aggregate state. Individual attempts remain normalized in `payments`.
- Gateway payloads are internal reconciliation evidence and must not be rendered as customer copy.

## Inventory aggregate

- Stock lives on exactly one sellable source for an order item: product or variant.
- `InventoryMovement` is the immutable ledger with source, order, delta, resulting balance, reason and unique idempotency key.
- `InventoryService` owns all order-driven decrement/restoration. Each operation starts or joins a transaction, locks the order, performs a conditional atomic stock update, and records the ledger before commit.
- No controller, view, callback or factory may decrement commerce inventory directly.

## Customer/support domain

- `User` owns addresses, carts, orders, wishlist entries, reviews, questions and notifications.
- `Wishlist` is a unique user/product relationship.
- `Review` publication requires moderation and verified-purchase evidence.
- `ProductQuestion` separates submitted questions from moderated answers.
- `ContactMessage` is a general support intake; it is not a return authorization or guaranteed-response system.

## Service boundaries

| Service | Responsibility |
|---|---|
| `CartService` | cart identity, merge and line mutation |
| `ProductQueryService` | bounded storefront reads, filters and recommendations |
| `OrderService` | order/payment orchestration and audited state transition |
| `OrderStateMachine` | legal order transitions only |
| `InventoryService` | atomic stock and movement ledger |
| `CouponService` | coupon validation and usage accounting |
| `RefundService` | refund request, approval, provider result and aggregate payment state |
| `PaymentEventService` | provider event identity and fact consistency |
| `PaymentRetryService` | bounded retry eligibility and attempt creation |
| `FulfillmentService` | shipment lifecycle; synchronizes orders through `OrderService` |

## Display versus internal commerce fields

Display fields include product name/descriptions, media, SEO metadata, homepage copy and formatted labels. Internal fields include raw decimal amounts, stock balances, idempotency keys, gateway IDs/signatures/payloads, approval timestamps and movement/event ledgers. Views may format internal values but may never write or recompute authoritative commerce state.
