# Rythme Commerce and Operations Flow Map

**Audit date:** 29 August 2026  
**Audited base:** `60b522cad70b249be6aebbbbadaa95ce812556ed`

## 1. Browse and discovery

1. `GET /` → `HomeController` → cached `HomepageDataService` → `home/index.blade.php`.
2. Homepage returns only active products for category discovery, arrivals, trending, category rows and truthful deals.
3. `GET /shop` → `ShopController` → `ShopIndex` Livewire.
4. `ProductQueryService` normalizes search/filter/sort inputs and queries active products with category, brand and attribute facets.
5. `GET /product/{product:slug}` → `ProductController`; inactive products return 404. Product view composes variants, gallery, cart, wishlist, reviews and Q&A.

## 2. Cart and wishlist

### Guest cart

Product `AddToCart` → `CartService::getOrCreateCart()` creates/loads a session-keyed cart → server validates active product/variant and stock → item snapshot is stored → `cart-updated` refreshes badge/drawer. `/cart` and the drawer work without login.

### Login merge

Login event reads the guest session cart → `CartService::mergeGuestCart()` merges into the authenticated cart with stock/identity controls → guest cart is retired. Wishlist routes/actions require authentication.

## 3. Checkout and order creation

1. Guest requesting `GET /checkout` is redirected to login.
2. `CheckoutWizard` loads owned addresses and server-side cart totals.
3. Address selection/save is ownership-scoped.
4. Coupon validation runs server-side.
5. `OrderService::createFromCheckout()` locks cart rows/products, validates active state/stock, recomputes prices, discount, shipping and tax, then creates immutable order/item/address snapshots.
6. Checkout UUID is the order idempotency key; unique storage resolves concurrent duplicate submissions.
7. A Razorpay order is created only after local order creation, and payment initiation is durably recorded.

## 4. Payment completion

### Browser callback

Razorpay posts to `/payment/razorpay/callback` (CSRF-exempt, throttled). The app finds the local payment by gateway order ID, verifies HMAC callback signature, fetches provider payment, verifies captured status, provider order, exact amount and currency, then calls `OrderService::markPaid()`.

### Webhook

Razorpay posts raw JSON plus signature to `/payment/razorpay/webhook`. Raw-body HMAC is verified. `PaymentEventService` records a unique/redacted event identity, rejects payload conflicts/replays, validates captured payment facts and finalizes once.

### Finalization

`markPaid()` locks order/payment, exits harmlessly if already paid, updates payment, captures inventory with ledger entries, confirms order and dispatches central commerce notification. Cart clears only after verified success. Success URL is signed and additionally checks order ownership.

## 5. Payment failure/retry

A failed verified attempt updates payment/order failure state and emits a notification. Owner-only retry reserves a new bounded payment attempt through `PaymentRetryService`; an assigned provider order cannot be silently replaced. Unknown provider outcomes are reconciled, not blindly retried.

## 6. Order access and processing

- Account order list/detail uses authenticated ownership.
- Guest tracking requires order number plus matching email, then issues a 15-minute signed detail URL.
- Invoice/detail accepts owner or valid signed URL; mutations still require authenticated ownership.
- Admin status actions require order-management permission, reason/confirmation and allowed state transitions.
- Status history and central notifications record customer-visible transitions.

## 7. Cancellation and refund

### Customer cancellation

Owner posts `/orders/{order}/cancel` → only pending/confirmed orders qualify → transaction locks order → status becomes cancelled. If unpaid, coupon usage is released. If paid, stock is restored and `RefundService::requestForCancellation()` creates exactly one full-value local pending refund with deterministic identity; no Razorpay call occurs from the customer request.

### Finance processing

Finance chooses **Process pending refund** → existing pending cancellation refund is locked and moved to processing before provider call → Razorpay receives the reserved amount against the captured payment → known success stores provider refund ID and completes aggregate payment/order state → provider-pending stays processing for reconciliation → known failure is recorded. Manual refund creation is hidden while pending/processing work exists.

### Partial refunds

Finance may reserve/process partial amounts only while aggregate pending + processing + refunded amounts do not exceed captured payment. Full aggregate completion marks payment/order refunded. Reconciliation is read-only.

## 8. Notifications

Typed commerce events are recorded after durable state. Deterministic event/channel/recipient identities prevent duplicate sends. Email/database deliveries track queued/sent/failed outcomes with redacted errors. Customer inbox supports owned read state and optional preferences; mandatory transactions remain enabled. Known failed delivery retries are capped; reconciliation is read-only.

## 9. Catalogue acquisition/import/activation

Bounded PHP commands read approved public catalogue endpoints, normalize content, download approved-host media to disposable external storage and produce reports. Import is dry-run/commit controlled and records provenance. Imported products stay inactive with zero inferred stock. Catalogue-authorized staff must enter real stock/price, verify content and local-media rights, provide a reason and use **Approve & activate**. Direct activation is blocked by UI and model guard.

## 10. Fulfillment

`FulfillmentService` permits Order Manager/Super Admin only. A paid active order is locked. Shipment item quantities must belong to the order and cannot exceed remaining unallocated quantity. Shipment identity replay accepts only the same item map. States are draft → ready → dispatched → delivered, with cancellation before dispatch. Carrier reference is required for dispatch. Order becomes delivered only when all ordered units are allocated and every active shipment is delivered. Filament/customer workflow presentation is pending.

## 11. Admin governance

`/admin` uses Filament auth plus model policies and `AdminAccess`. Role permissions separate catalogue, orders, customers, interactions, content, marketing, finance, settings, staff, audit and notification evidence. Staff MFA is required. Sensitive model changes pass through audit observers; financial/catalogue actions require explicit confirmation/reason.

## 12. Scheduler/queue flow

Hosting cron → `php artisan schedule:run` each minute → overlap-protected queue worker drains current jobs and exits within 50 seconds. Transactional notification work does not depend on a persistent daemon. Failed notification/payment evidence is inspected with bounded commands.

## 13. Error and recovery flow

404/500 views use the global layout. Navbar category composition checks for the categories table and returns an empty list when the selected database is unmigrated, avoiding recursive error rendering. This does not repair a wrong `.env`: the owner must select `rhythm_db`, clear config/cache and verify migration status.
