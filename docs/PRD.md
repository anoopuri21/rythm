# Product Requirements Document (PRD)

**Product:** Rhythm Exports (public brand: **Rythme Music Store**)  
**Codename:** SOUNDSCAPE  
**Document type:** Product Requirements Document  
**Version:** 1.0  
**Date:** 2026-09-12  
**Status:** Authoritative product definition aligned with current codebase  
**Repo:** `anoopuri21/rythm`  
**Store model:** **Single-brand ecommerce** — one store brand sells many products (not a multi-vendor marketplace)

---

## 1. Executive summary

Rhythm Exports is a **premium, single-brand ecommerce platform** for musical instruments and related gear. One commercial brand (**Rhythm Exports / Rythme**) owns the catalogue, pricing, fulfillment, payments, and customer relationship. Customers discover multiple products across categories (guitars, keyboards, drums, pro audio, accessories, and more), purchase through a secure checkout, and manage orders from a customer account.

The product is **not** a multi-seller marketplace. There is no third-party seller onboarding, commission split, or multi-store front. Brands such as Fender, Yamaha, or Roland appear only as **product manufacturer labels** inside one store catalogue.

### 1.1 One-line vision

> A cinematic, trustworthy online music store where musicians discover, compare, and buy instruments from a single trusted brand — with enterprise-grade commerce operations behind the scenes.

### 1.2 Problem statement

Musicians and music retailers need a branded digital storefront that:

1. Presents a curated instrument catalogue with truthful stock and pricing.
2. Supports modern discovery (search, filters, variants, reviews, Q&A).
3. Completes payment safely (Razorpay) with auditable order/payment ledgers.
4. Lets operations staff manage catalogue, orders, fulfillment, returns, and content without engineering intervention.
5. Feels premium and brand-owned — not a dense multi-vendor marketplace.

### 1.3 Solution

A Laravel-based monolithic ecommerce application with:

- **Storefront** — Blade + Livewire + Tailwind cinematic UX.
- **Admin** — Filament panel with role-based operations.
- **Commerce core** — cart, checkout, orders, inventory ledger, payments, refunds, shipments, returns.
- **Content/CMS** — homepage sections, pages, SEO, FAQs, merchandising.
- **Notifications** — email/commerce event delivery with preferences and retry.

---

## 2. Product positioning

| Dimension | Decision |
|---|---|
| **Business model** | Single brand / single seller DTC (direct-to-consumer) store |
| **Catalogue** | Many products, categories, manufacturer brands, variants, attributes |
| **Currency** | INR (₹) only |
| **Geography (initial)** | India (addresses, pincode, GST-oriented tax snapshots) |
| **Checkout** | Authenticated only (login forced); guest may browse and cart |
| **Payments** | Razorpay (card / UPI / netbanking / wallet); gateway behind interface |
| **UX style** | Premium Shopify-like editorial storefront (airy, large imagery), not marketplace-dense UI |
| **Reference inspiration** | bajaao.com (structure/catalogue inspiration only; all copy original) |
| **Out of scope** | Multi-vendor marketplace, seller portals, multi-currency, native mobile apps (v1) |

### 2.1 Single-brand rules (binding)

1. **One store identity** — logo, domain, legal entity, support contacts, and payment merchant account belong to Rhythm Exports.
2. **One price authority** — selling price, compare-at price, coupons, shipping, and tax are controlled by store admin/settings, not external sellers.
3. **One inventory authority** — stock lives on product or variant; only `InventoryService` mutates stock via an immutable movement ledger.
4. **Manufacturer “brands” ≠ store brand** — `Brand` model labels product makers (Fender, Yamaha…); they do not own storefronts or checkout.
5. **No seller splitting** — order revenue, refunds, and fulfillment are wholly owned by the single operator.

---

## 3. Goals and success metrics

### 3.1 Business goals

| Goal | Success signal |
|---|---|
| Launch a credible branded instrument store | Production storefront live with real catalogue and payments |
| Convert discovery to paid orders | Checkout completion rate; payment capture success rate |
| Reduce ops friction | Admin can publish products, process orders/shipments/refunds without deploys |
| Build trust | Verified-purchase reviews only; transparent order/payment status; policy pages |
| Protect margin and stock | No oversell under concurrency; audited refunds; coupon abuse controls |

### 3.2 Product KPIs (target instrumentation)

| KPI | Description |
|---|---|
| Conversion rate | Sessions → paid orders |
| AOV | Average order value (INR) |
| Cart abandonment | Carts created vs checkouts started vs paid |
| Payment success | Initiated → paid ratio; retry success |
| Catalogue health | % active SKUs with image, stock, price, SEO |
| Fulfillment SLA | Confirmed → shipped / delivered latency |
| Support load | Contact messages, return requests, failed notification retries |
| Performance | LCP/TTFB budgets per performance docs |
| Quality | Automated test suite green; zero critical security findings |

### 3.3 Non-goals (explicit)

- Multi-vendor marketplace or white-label multi-store SaaS.
- Guest checkout (browse/cart yes; place order no).
- Native iOS/Android apps in v1.
- Real-time multiplayer collaboration or social network features.
- Arbitrary head-script injection by marketing (security boundary).
- Claiming shipping/return/warranty policies publicly before owner legal approval.

---

## 4. Personas and actors

### 4.1 Customer (storefront)

| Persona | Needs |
|---|---|
| **Browser** | Explore homepage, categories, shop filters without account |
| **Shopper** | Compare products, add to cart/wishlist, read reviews/Q&A |
| **Buyer** | Login, checkout, pay, receive confirmation, track order, invoice |
| **Returning customer** | Reorder path, addresses, notifications, cancel/return when eligible, stock alerts |

### 4.2 Staff (Filament admin) — deny-by-default RBAC

| Role | Primary job |
|---|---|
| **Super Admin** | Full access including staff and settings |
| **Admin (legacy)** | Compatibility all-permissions alias; migrate to Super Admin |
| **Catalogue Manager** | Products, categories, manufacturer brands, imports/activation |
| **Order Manager** | Order status, fulfillment-oriented operations |
| **Support** | Interactions (reviews, Q&A, contact), inspect orders/customers |
| **Marketing** | Content, homepage, coupons, newsletter |
| **Finance** | Refunds, financial views; not fulfillment state |

`customer` is **not** a staff role and cannot access `/admin`.

---

## 5. Scope

### 5.1 In scope (current product)

1. Cinematic marketing homepage with admin-managed sections.
2. Shop listing: search, category tree, brands, price, attributes, rating, availability, sort, pagination.
3. Product detail: gallery, variants, price box, stock, reviews, Q&A, related/recently viewed, JSON-LD.
4. Guest + user cart; merge on login; drawer + full cart page.
5. Auth wishlist.
6. Auth checkout wizard (address → payment), coupons, shipping/tax breakdown.
7. Razorpay payment create/verify/webhook + fake gateway for tests.
8. Orders: snapshots, status history, cancel, payment retry, invoice, guest track-order lookup.
9. Inventory movement ledger; atomic stock capture/restore.
10. Shipments domain + admin resources; return/RMA workflow.
11. Refunds (partial-capable) with finance controls.
12. Notifications: commerce events, preferences, delivery evidence, retry/reconcile commands.
13. CMS pages, SEO entries, FAQs, contact, newsletter.
14. Filament admin for catalogue, commerce, content, staff, audit logs, settings.
15. Security headers, policies, throttling, signed links, admin MFA, audit trail.
16. Shared-hosting friendly deploy (cPanel), SQLite dev / MySQL 8 prod target.

### 5.2 Deferred / gated

| Item | Gate |
|---|---|
| Public shipping/returns/warranty/FAQ publication | Owner legal/business approval (`withheld_public_pages`) |
| Live Razorpay production keys | Staging E2E verification |
| Tax/HSN professional rules | Finance/legal sign-off |
| Media responsive conversions | Performance phase |
| Semantic/vector search | DB strategy (Postgres/pgvector) decision |
| Persistent queue workers / Redis | Hosting tier |
| Full CI/CD, backups, monitoring | Ops phase |

---

## 6. System architecture (layers)

Architecture is a **modular monolith**: one deployable Laravel app with clear layer boundaries. No separate microservices required for v1.

```
┌─────────────────────────────────────────────────────────────────┐
│                        CLIENTS                                   │
│  Browser (Storefront)          Browser (Filament Admin /admin)  │
└───────────────┬─────────────────────────────┬───────────────────┘
                │                             │
┌───────────────▼─────────────────────────────▼───────────────────┐
│ L1  PRESENTATION                                                 │
│  Blade layouts/components · Livewire UI · Filament Resources     │
│  Alpine/GSAP/Lenis/Swiper · Vite/Tailwind tokens                 │
└───────────────┬─────────────────────────────┬───────────────────┘
                │                             │
┌───────────────▼─────────────────────────────▼───────────────────┐
│ L2  HTTP / APPLICATION BOUNDARY                                  │
│  Controllers · Form Requests · Middleware · Routes · Policies    │
│  DTOs (CheckoutData, ShopFilters) · Livewire actions             │
└───────────────┬─────────────────────────────────────────────────┘
                │
┌───────────────▼─────────────────────────────────────────────────┐
│ L3  DOMAIN SERVICES (business writes & orchestration)            │
│  Cart · Order · Inventory · Checkout orchestration · Coupon      │
│  PaymentEvent · PaymentRetry · Refund · Fulfillment · Returns    │
│  ProductQuery · Catalogue import/review/activation               │
│  Notifications · Homepage · SEO · Reviews · Q&A · Audit          │
│  OrderStateMachine · PaymentGateway interface                    │
└───────────────┬─────────────────────────────────────────────────┘
                │
┌───────────────▼─────────────────────────────────────────────────┐
│ L4  DOMAIN MODEL (Eloquent aggregates)                           │
│  Catalogue · Cart · Order · Payment/Refund · Inventory           │
│  Shipment · Return · Customer · Content · Notification           │
│  Enums · Observers · Events/Listeners                            │
└───────────────┬─────────────────────────────────────────────────┘
                │
┌───────────────▼─────────────────────────────────────────────────┐
│ L5  DATA & INTEGRATIONS                                          │
│  MySQL 8 / SQLite · Spatie Media · Cache/Queue/Session           │
│  Razorpay SDK · Mail · Scheduler (shared-hosting worker drain)   │
│  Console commands (reconcile, retry, catalogue import)           │
└─────────────────────────────────────────────────────────────────┘
```

### 6.1 Layer responsibilities (binding)

| Layer | May do | Must not do |
|---|---|---|
| **L1 Presentation** | Render UI, collect input, call Livewire/controller methods, format display values | Recompute prices/stock as authority; call payment gateway directly; mutate inventory |
| **L2 HTTP boundary** | AuthZ, validate input, throttle, map HTTP ↔ DTO/service calls | Embed multi-step commerce business rules; trust client money fields |
| **L3 Services** | Own transactions, locks, state machines, ledgers, gateway calls, notifications | Render HTML; bypass policies for “convenience” |
| **L4 Models** | Relationships, casts, invariants helpers, query scopes | Orchestrate multi-aggregate workflows (that belongs in services) |
| **L5 Data/integrations** | Persist, queue, external I/O | Business policy decisions without service mediation |

### 6.2 Key architectural principles

1. **Thin controllers / fat services** — business writes live in `app/Services/*`.
2. **Snapshots over live joins** — order items and addresses freeze at purchase time.
3. **Orthogonal status axes** — order fulfillment status ≠ payment status ≠ shipment status.
4. **Idempotency** — checkout, payment events, inventory movements, refunds use unique operation keys.
5. **Deny-by-default authorization** — Filament `strictAuthorization` + model policies + service re-checks.
6. **Testability** — `PaymentGateway` interface with `FakePaymentGateway`; PHPUnit feature/unit suite.
7. **Shared-hosting realism** — scheduled `queue:work --stop-when-empty`; no assumed always-on worker.
8. **Truthful UX** — storefront never claims policies, stock alerts, or refund completion the system cannot prove.

---

## 7. Domain model (aggregates)

### 7.1 Catalogue aggregate (single-brand multi-product)

| Entity | Role |
|---|---|
| `Category` | Hierarchical tree (parent/children); navigation and filters |
| `Brand` | **Manufacturer label** only (not a store seller) |
| `Product` | Sellable root: copy, SEO, base price, base stock, media, flags |
| `ProductVariant` | Optional SKU split (finish/size/etc.) with price override + own stock |
| `ProductAttribute` / `ProductAttributeValue` | Filterable specs |
| `ProductImportSource` | Acquisition provenance + publication review (not customer copy) |
| `ProductMerchandisingRule` | Curated merchandising placement rules |

**Invariants**

- Unique product/variant SKUs and product slugs.
- Positive price before activation of imported products.
- Compare-at only meaningful when above selling price.
- Cart line picks either base product **or** one variant inventory source.
- Soft-delete products for history; deactivation is normal unpublish.
- Imported activation requires reviewed provenance, approved local media, verified stock.

### 7.2 Cart & checkout aggregate

| Entity | Role |
|---|---|
| `Cart` | User **or** guest `session_id` |
| `CartItem` | Product + optional variant; qty is intent, not reservation; `unit_price` snapshot |
| `CheckoutData` (DTO) | Immutable boundary for address, coupon, notes, idempotency |
| `Address` | Mutable customer address book (not order history) |
| `Coupon` | Fixed/percentage discounts with usage controls |

### 7.3 Order aggregate

| Entity | Role |
|---|---|
| `Order` | Durable commerce root; money totals; JSON address snapshots |
| `OrderItem` | Immutable name/SKU/options/qty/money snapshot |
| `OrderStatusHistory` | Append-only transition evidence |
| `OrderStatus` enum | `pending → confirmed → processing → shipped → delivered` (+ `cancelled`, legacy `refunded`) |
| `OrderPaymentStatus` enum | `unpaid | paid | failed | refund_pending | refunded` |

### 7.4 Payment & refund aggregate

| Entity | Role |
|---|---|
| `Payment` | Gateway attempt (initiated/paid/failed/refunded) |
| `PaymentEvent` | Deduplicated provider callback/webhook ledger |
| `Refund` | Partial-capable; approval + provider outcome; idempotency key |

### 7.5 Inventory aggregate

| Entity | Role |
|---|---|
| `InventoryMovement` | Immutable ledger: delta, balance, reason, unique op key |
| `InventoryService` | **Only** path for order-driven stock decrement/restore |

### 7.6 Fulfillment & returns

| Entity | Role |
|---|---|
| `Shipment` / `ShipmentItem` / `ShipmentEvent` | Allocation and carrier lifecycle independent of payment |
| `ReturnRequest` / items / events / `ReturnReason` | RMA workflow after delivery rules |

### 7.7 Customer & engagement

| Entity | Role |
|---|---|
| `User` | Customer or staff (role field); MFA for staff |
| `Wishlist` | Unique user↔product |
| `Review` | Moderated, verified-purchase gated public ratings |
| ~~`ProductQuestion`~~ | **Removed** — storefront/admin product Q&A retired; use Contact for product help |
| `ContactMessage` | Support intake (not RMA) |
| `NewsletterSubscriber` | Marketing opt-in |
| `BackInStockSubscription` | Explicit-consent stock alerts (ops gated) |
| `NotificationDelivery` / `NotificationPreference` / `CommerceEvent` | Notification architecture |

### 7.8 Content & SEO

| Entity | Role |
|---|---|
| `HomepageSection` / `HomepageBlock` / `HomepageCategoryRow` / `HeroSlide` | Homepage composition |
| `Faq` | FAQ + JSON-LD capable |
| `Page` | CMS pages (reserved slugs blocked) |
| `SeoEntry` | Polymorphic SEO metadata |
| `SiteSetting` | Runtime commercial settings (shipping, tax, contacts) |

### 7.9 Admin governance

| Entity | Role |
|---|---|
| `AdminAuditLog` | Sensitive change evidence |
| Staff via `User` roles + policies | RBAC matrix |

---

## 8. Feature requirements by surface

### 8.1 Storefront — discovery

| ID | Requirement | Priority |
|---|---|---|
| SF-01 | Homepage renders admin-driven cinematic sections (hero, categories, bestsellers, deals, FAQ, etc.) | P0 |
| SF-02 | `/shop` supports search, category, brand, price range, attributes, rating, availability, sale state, sort, pagination, active chips | P0 |
| SF-03 | Category drawer / nav from global layout (composer-fed tree) | P0 |
| SF-04 | `/product/{slug}` gallery, price box (MRP/% off), variants, qty, stock truth, related + recently viewed | P0 |
| SF-05 | Product JSON-LD, breadcrumbs, unique title/meta | P0 |
| SF-06 | Sitemap.xml + robots.txt | P0 |
| SF-07 | Empty/filter-empty/OOS states with recovery CTAs | P1 |
| SF-08 | Merchandising rules influence curated surfaces without breaking filter truth | P1 |

### 8.2 Storefront — cart & wishlist

| ID | Requirement | Priority |
|---|---|---|
| CT-01 | Guest and authenticated carts in DB; merge guest→user on login | P0 |
| CT-02 | Add/update/remove with server-side price snapshot and stock validation | P0 |
| CT-03 | Livewire cart drawer, cart page, header badge (zero full-page reload for mutations) | P0 |
| CT-04 | Wishlist auth-only; toggle on cards/PDP; move-to-cart | P0 |
| CT-05 | Cart mutations throttled; targets ≥ 44×44px; sticky mobile checkout CTA | P0 |

### 8.3 Storefront — auth & account

| ID | Requirement | Priority |
|---|---|---|
| AU-01 | Register, login, logout; throttle auth POSTs | P0 |
| AU-02 | Email verification (signed, throttled) | P0 |
| AU-03 | Forgot/reset password | P0 |
| AU-04 | Account: profile, password, addresses, stock alerts cancel | P0 |
| AU-05 | Notification center + preferences | P0 |
| AU-06 | Order list/detail via account; ownership enforced | P0 |

### 8.4 Checkout & payments

| ID | Requirement | Priority |
|---|---|---|
| CK-01 | Checkout requires auth; intended URL redirect from login | P0 |
| CK-02 | Two-step wizard: address → payment review | P0 |
| CK-03 | Server recomputes subtotal, discount, shipping, tax, total; ignore client money | P0 |
| CK-04 | Coupon validation via `CouponService` | P0 |
| CK-05 | Stock lock (`lockForUpdate`) at place-order; no oversell | P0 |
| CK-06 | Create order `pending`/`unpaid` + Razorpay order; client checkout modal | P0 |
| CK-07 | Callback signature verify + amount match; webhook HMAC + event dedupe | P0 |
| CK-08 | On paid: confirm order, inventory capture, clear cart, queue notifications | P0 |
| CK-09 | Signed success URL | P0 |
| CK-10 | Bounded payment retry for eligible unpaid/failed states | P0 |
| CK-11 | Fake gateway for automated tests | P0 |

### 8.5 Orders, fulfillment, returns

| ID | Requirement | Priority |
|---|---|---|
| OR-01 | Immutable item/address snapshots; append-only status history | P0 |
| OR-02 | Customer cancel when state machine allows; restore stock; paid → refund_pending path | P0 |
| OR-03 | Printable invoice; guest track-order lookup (throttled) | P0 |
| OR-04 | Shipment lifecycle draft→ready→dispatched→delivered; sync order via OrderService | P0 |
| OR-05 | Return request create/cancel for eligible delivered orders | P1 |
| OR-06 | Admin return reasons and return request handling | P1 |

### 8.6 Trust & engagement

| ID | Requirement | Priority |
|---|---|---|
| TR-01 | Reviews: verified purchase, moderation, rating aggregate | P0 |
| TR-02 | ~~Product Q&A moderated~~ **REMOVED** (contact + reviews remain) | — |
| TR-03 | Contact form throttled + honeypot patterns where applicable | P0 |
| TR-04 | Newsletter subscribe throttled | P0 |
| TR-05 | Back-in-stock explicit consent; notify only when ops command scheduled | P1 |
| TR-06 | Policy pages may exist in CMS but stay withheld until owner approval | P0 |

### 8.7 Admin (Filament)

| ID | Requirement | Priority |
|---|---|---|
| AD-01 | Role-gated panel at `/admin` | P0 |
| AD-02 | Catalogue CRUD: products (variants, media, SEO), categories, brands | P0 |
| AD-03 | Orders view + guarded status transitions; shipments | P0 |
| AD-04 | Finance-only refunds | P0 |
| AD-05 | Content: homepage sections/blocks/rows, hero, FAQs, pages | P0 |
| AD-06 | Marketing: coupons, newsletter | P0 |
| AD-07 | Support: reviews, questions, contact messages | P0 |
| AD-08 | Customers list; staff manage; audit log read | P0 |
| AD-09 | Settings page (site commercial config) | P0 |
| AD-10 | Dashboard stats + latest orders widgets | P1 |
| AD-11 | Import provenance review + activation guards | P1 |
| AD-12 | Staff MFA for elevated accounts | P0 |
| AD-13 | Notification delivery inspection (read-only per role) | P1 |

### 8.8 Notifications & ops commands

| ID | Requirement | Priority |
|---|---|---|
| NT-01 | Order confirmation and status mails queued | P0 |
| NT-02 | Commerce notification service + delivery records | P0 |
| NT-03 | Retry failed notifications (bounded) | P0 |
| NT-04 | Reconcile payments/notifications (read-oriented + bounded mutate) | P0 |
| NT-05 | Back-in-stock notify command | P1 |
| NT-06 | Catalogue acquire/import expansion commands | P2 |

---

## 9. Critical user journeys

### 9.1 Primary purchase journey

```
Home → Shop (filter/search) → Product detail → Add to cart
  → Cart drawer/page → Login (if guest) → Checkout (address → pay)
  → Razorpay → Callback/Webhook verify → Success (signed)
  → Account orders / track-order → Shipment updates → Delivered
```

### 9.2 Returning customer

- Wishlist save/move-to-cart  
- Saved addresses at checkout  
- Notification center  
- Cancel eligible order / request return  
- Payment retry on failed attempt  
- Invoice download  

### 9.3 Catalogue ops journey (single brand)

```
Admin creates/imports product under store catalogue
  → attach category + manufacturer brand + media + stock + price
  → review provenance if imported → activate
  → appears on shop/PDP/homepage merchandising rules
```

### 9.4 Finance journey

```
Paid order → (cancel or return) → refund_pending
  → Finance approves → provider refund → refunded ledger
  → order payment_status updated; fulfillment status remains orthogonal
```

---

## 10. State machines (normative)

### 10.1 Order status

| From | Allowed next |
|---|---|
| pending | confirmed, cancelled |
| confirmed | processing, shipped, cancelled |
| processing | shipped, cancelled |
| shipped | delivered, cancelled* |
| delivered | — (terminal) |
| cancelled | — (terminal) |
| refunded | — (legacy terminal label) |

\*Exceptional cancellation after ship requires approved ops path.

Illegal transitions rejected by `OrderStateMachine`; mutations only via `OrderService::changeStatus()`.

### 10.2 Payment summary (order-level)

`unpaid` → `paid` | `failed` → (`refund_pending` → `refunded`)

### 10.3 Payment attempt

`initiated` → `paid` | `failed`; `paid` → `refunded` after completed refund accounting. Terminal attempts are never reused.

### 10.4 Shipment

`draft` → `ready` | `cancelled` → `dispatched` → `delivered`.

### 10.5 Inventory operation

Lock order → check idempotency → conditional atomic stock update → append movement → commit. Duplicates no-op; failures roll back.

---

## 11. API / route surface (storefront)

| Area | Routes (representative) |
|---|---|
| Home/CMS | `GET /`, `GET /{slug}`, `GET /contact`, `POST /contact` |
| Catalogue | `GET /shop`, `GET /product/{product:slug}` |
| Cart | `GET /cart` (+ Livewire mutations) |
| Wishlist | `GET /wishlist` (auth) |
| Auth | login/register/password/verify/logout |
| Checkout | `GET /checkout`, `GET /checkout/success/{order}` (auth + signed success) |
| Payments | `POST /payment/razorpay/callback`, `POST /payment/razorpay/webhook` (CSRF exempt, verified) |
| Orders | show, invoice, cancel, retry-payment, track-order lookup |
| Returns | create/store/cancel (auth) |
| Account | profile, password, addresses, notifications, stock-alerts |
| SEO | `/sitemap.xml`, `/robots.txt` |
| Newsletter | `POST /newsletter` |

Admin surface: Filament panel under `/admin` (not public REST).

---

## 12. Technology stack (locked)

| Layer | Choice |
|---|---|
| Language | PHP 8.3+ (platform lock may pin 8.3.30) |
| Framework | Laravel **13.24.0** |
| Storefront UI | Blade · Livewire 4 · Alpine.js · Tailwind 4 · Vite 7 |
| Motion | GSAP · Lenis · Swiper · CountUp |
| Admin | Filament 5.x panel |
| Media | Spatie Media Library |
| Payments | Razorpay SDK + `PaymentGateway` abstraction |
| DB | SQLite (local/tests), **MySQL 8+** production target |
| Cache/queue/session | Environment-configured; DB-backed acceptable on shared hosting |
| Mail | Laravel notifications/mailables (queued) |
| Tests | PHPUnit feature + unit |
| Hosting target | Shared cPanel (MilesWeb-class) + cron schedule |

**Explicitly forbidden for v1 storefront:** Next.js, React, Vue, jQuery as primary UI frameworks.

---

## 13. Design system requirements

| Token | Role |
|---|---|
| Brand red (semantic `brand`) | Primary CTAs, focus, badges |
| `ink` | Text / dark sections |
| `paper` / `paper-dark` | Surfaces |
| `muted` / `border` | Secondary text and dividers |
| Typography | Inter (current approved) / historical Poppins notes in archive — **use live `@theme` tokens in `resources/css/app.css` as source of truth** |

Rules:

- Semantic tokens only in new UI (no ad-hoc hex).
- Premium airy layouts; pill buttons; cards with restrained shadow.
- WCAG AA contrast for text pairs; red never sole status indicator.
- `prefers-reduced-motion` respected for cinematic effects.
- Touch targets ≥ 44×44px.

---

## 14. Non-functional requirements

### 14.1 Security

- CSRF on storefront POSTs (except verified payment provider endpoints).
- Server-side validation (Form Requests); mass-assignment via `$fillable`.
- Blade escaping; sanitized HTML cast where rich text stored.
- Rate limits on auth, checkout, contact, newsletter, payment callbacks.
- Razorpay signature/HMAC verification before state mutation.
- Security headers / CSP middleware.
- Policies for order ownership; signed URLs for success/invoice where designed.
- Admin MFA; audit logs for sensitive changes.
- Secrets only in environment — never commit.

### 14.2 Reliability & correctness

- DB transactions + row locks on checkout/inventory/refunds/fulfillment.
- Idempotent payment event and inventory operations.
- State machines reject illegal transitions.
- Reconciliation commands for payments and notifications.

### 14.3 Performance

- Eager-load catalogue relations on list/detail.
- Homepage data caching with observer invalidation.
- Bounded queries; indexes on storefront/admin list paths.
- Image optimization/conversions tracked as open performance work.
- Respect documented performance budget.

### 14.4 Accessibility

- Semantic headings (one `h1` per page).
- Keyboard operable drawers/modals (Esc, focus trap).
- Visible focus rings; non-color status cues.
- Phase accessibility baseline as minimum bar.

### 14.5 Operability

- `.env.example` / staging / production examples without secrets.
- Deploy scripts for cPanel.
- Scheduler drains queue in bounded windows.
- Runbooks: ops, rollback, release checklist, risk register.
- Logging via standard Laravel channels; no PII in casual logs.

### 14.6 Privacy

- Collect only data needed for commerce and support.
- Notification preferences honored.
- Privacy/data map documents field purpose; legal pages gated on approval.

---

## 15. Service catalogue (implementation map)

| Service | Responsibility |
|---|---|
| `ProductQueryService` | Storefront reads, filters, related |
| `CategoryService` / `BrandService` | Catalogue structure helpers |
| `CartService` | Cart identity, lines, merge, totals helpers |
| `WishlistService` | Toggle / move-to-cart |
| `AddressService` | Address book + snapshots |
| `CouponService` | Validate and account usage |
| `OrderService` | Create order, status changes, cancel orchestration |
| `OrderStateMachine` | Legal transitions only |
| `InventoryService` | Atomic stock + movement ledger |
| `PaymentEventService` | Provider event identity/consistency |
| `PaymentRetryService` | Bounded retry eligibility |
| `RefundService` | Request, approve, provider result, aggregate payment state |
| `FulfillmentService` | Shipments; sync order via OrderService |
| `ReturnRequestService` | RMA lifecycle |
| `CommerceNotificationService` + retry/reconcile/preferences | Notification pipeline |
| `HomepageDataService` / `SeoService` / `SiteSettingsService` | Content & config |
| `ReviewService` / `ContactService` | Engagement |
| `Catalogue* / ImportedProductActivationService` | Import → review → activate |
| `AdminAuditService` | Audit writes |
| `BackInStockSubscriptionService` | Stock alert subscriptions |
| `FinancialReconciliationService` | Read-oriented finance checks |
| `PaymentGateway` (`RazorpayGateway`, `FakePaymentGateway`) | Payment I/O |

---

## 16. Data & media requirements

1. Money fields use a consistently tested decimal (or paise) strategy; display formatting is non-authoritative.
2. Media via Spatie collections (`gallery`, `og`, category `icon`, brand `logo`, hero images, homepage blocks).
3. Product image resolution order: managed media → committed `public/images/products/{slug}.jpg` → fallback.
4. Public disk served at `/storage` via storage link.
5. No assumption of S3 unless configured.
6. Seeders are **demo only** — not production stock or legal consent.

---

## 17. Testing requirements

| Type | Coverage expectations |
|---|---|
| Unit | Totals, state machine guards, inventory idempotency, signature verify fixtures |
| Feature | Shop filters, cart guest/auth/merge, wishlist auth gate, checkout happy path with fake gateway, stock guard, webhook reject, cancel/retry, admin policy boundaries |
| UI build | `npm run build` production bundle |
| Regression | Full `php artisan test` green before release candidates |

Quality bar: existing suite is large (dozens of test files / 200+ methods historically); new features must add tests for money, stock, authz, and payment edges.

---

## 18. Release & environments

| Environment | Purpose |
|---|---|
| Local | SQLite, fake/test keys, seed data |
| Staging | MySQL 8, Razorpay test mode, mail sink/provider test |
| Production | MySQL 8, live secrets, HTTPS, cron, backups |

Release gates (summary):

1. Migrations clean on target DB.  
2. Tests + frontend build pass.  
3. Dependency audits reviewed.  
4. Config/cache/storage link/cron verified.  
5. Payment webhook reachable and verified.  
6. Owner UAT on critical journeys.  
7. Rollback plan understood.

---

## 19. Risks and mitigations

| Risk | Mitigation |
|---|---|
| Oversell under concurrent checkout | Row locks + conditional stock update + movement ledger |
| Double charge / replay webhooks | Payment event dedupe + signature checks + idempotent order updates |
| Admin privilege abuse | RBAC matrix, MFA, audit log, finance/order separation |
| Shared hosting queue starvation | Scheduled bounded workers; critical path sync where required |
| Unapproved legal claims on storefront | `withheld_public_pages`; truthful copy rules |
| Stale docs vs code | This PRD + architecture overview + master tracker as living authorities |
| Large unoptimized images | Media conversion roadmap; lazy loading |
| Tax/regulatory error | Snapshot framework + professional approval gate |

---

## 20. Roadmap alignment (high level)

| Phase theme | Outcome |
|---|---|
| Foundation | Laravel app, design system, homepage, CMS |
| Catalogue & shop | Multi-product single-brand catalogue, PDP, filters |
| Commerce core | Cart, wishlist, checkout, orders, coupons |
| Payments ops | Events, retry, refunds, reconciliation |
| Notifications | Deliveries, preferences, retries |
| Fulfillment & returns | Shipments, RMA, tax snapshots |
| Customer experience | Account polish, stock alerts, trust surfaces |
| Security & privacy hardening | Threat model, authz matrix, accessibility |
| Performance & ops | Indexes, budgets, deploy, monitoring |
| Owner UAT & launch | Real credentials, legal pages, go-live |

Detailed phase contracts live under `tasks/` (canonical sequence and master tracker). This PRD defines **what** the product is; phase plans define **when/how** increments ship.

---

## 21. Open decisions (human gates)

1. Final legal copy for shipping, returns, warranty, privacy, terms.  
2. Production Razorpay mode switch after staging E2E.  
3. Final GST/HSN and invoice numbering policy.  
4. Whether to introduce Redis/object storage beyond shared hosting defaults.  
5. Vector/semantic search database strategy.  
6. Public enablement of back-in-stock notification scheduling.  
7. Retirement of legacy `admin` role after Super Admin migration.

---

## 22. Document authority and maintenance

### Always-read set (AI session front door — read these first)

| Document | Role |
|---|---|
| `docs/ARCHITECTURE.md` | Condensed layers, domain, services, routes |
| `docs/RULES.md` | Binding product/tech/security/workflow rules |
| `docs/PHASES.md` | Condensed delivery status + launch gates |
| `docs/DESIGN.md` | Tokens, UI patterns, a11y, change protocol |
| `docs/MEMORY.md` | Living facts + session log (update every change) |

### Product + deep reference

| Document | Role |
|---|---|
| **`docs/PRD.md` (this file)** | Full product definition: single-brand multi-product ecommerce |
| `docs/architecture-overview.md` | Current-state technical inventory |
| `docs/architecture/01-commerce-architecture.md` | Commerce design decisions |
| `docs/domain-model.md` | Aggregate invariants |
| `docs/state-machine.md` | Transition tables |
| `docs/permissions-matrix.md` | RBAC |
| `tasks/MASTER_PROJECT_TRACKER.md` / canonical phase sequence | Delivery status authority |
| `config/rythme.php` + Site Settings | Runtime brand/config |

**Change protocol:** When product scope changes (e.g., multi-vendor, new market, guest checkout), update this PRD first, then the five always-read files (`ARCHITECTURE` / `RULES` / `PHASES` / `DESIGN` / `MEMORY`), then deep docs, then code.

---

## 23. Glossary

| Term | Meaning |
|---|---|
| **Store brand** | Rhythm Exports / Rythme — the single seller operating the site |
| **Manufacturer brand** | `Brand` model entry (e.g., Yamaha) labeling products inside the store |
| **Snapshot** | Frozen commercial data on orders/items/addresses at purchase time |
| **Ledger** | Append-only evidence (inventory movements, payment events, status history) |
| **Activation** | Publishing an imported/draft product for sale after review gates |
| **Orthogonal status** | Independent axes (order vs payment vs shipment) that must not be collapsed into one field |

---

## 24. Acceptance summary (product-level DoD)

The product is considered requirement-complete for a **single-brand, multi-product ecommerce launch candidate** when:

1. Customers can discover multiple products under one brand identity and complete authenticated paid checkout.  
2. Stock, money, and payment state remain correct under concurrency and provider retries.  
3. Staff can operate catalogue, orders, fulfillment, refunds, and content within RBAC.  
4. Notifications and audit evidence exist for critical commerce events.  
5. Security, privacy, and truthful-UX constraints hold.  
6. Automated tests and production build pass; staging payment E2E is evidenced.  
7. Owner-approved legal pages and production secrets are in place.  

---

*End of PRD v1.0 — Rhythm Exports single-brand ecommerce platform.*
