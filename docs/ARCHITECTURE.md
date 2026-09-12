# ARCHITECTURE — Always Read

> **One of five always-read files.** Others: `RULES.md` · `PHASES.md` · `DESIGN.md` · `MEMORY.md`  
> Full PRD: `docs/PRD.md` · Deep dive only if needed: `docs/architecture-overview.md`, `docs/domain-model.md`

**Product:** Rhythm Exports / Rythme Music Store  
**Model:** **Single-brand ecommerce** (one seller, many products) — NOT multi-vendor marketplace  
**Shape:** Modular Laravel monolith

---

## 1. Stack (locked)

| Layer | Choice |
|---|---|
| PHP / Laravel | 8.3+ / **13.24.0** |
| Storefront | Blade + Livewire 4 + Alpine + Tailwind 4 + Vite 7 |
| Motion | GSAP · Lenis · Swiper · CountUp |
| Admin | Filament panel `@ /admin` |
| Media | Spatie Media Library |
| Pay | Razorpay via `PaymentGateway` + `FakePaymentGateway` |
| DB | SQLite local/tests · **MySQL 8+ prod** |
| Host | Shared cPanel; cron drains queue (`queue:work --stop-when-empty`) |

**Forbidden storefront primary UI:** Next.js / React / Vue / jQuery.

---

## 2. Layers (binding boundaries)

```
L1 Presentation     Blade · Livewire · Filament · JS modules
L2 HTTP boundary    Controllers · Form Requests · Middleware · Policies · DTOs
L3 Domain services  app/Services/* · Payment/* · State machines
L4 Domain model     Eloquent models · Enums · Events/Listeners · Observers
L5 Data/IO          MySQL · Cache/Queue/Session · Razorpay · Mail · Artisan cmds
```

| Layer | May | Must not |
|---|---|---|
| L1 | Render, collect input, format display | Own money/stock authority; call gateway directly |
| L2 | Validate, authZ, throttle, map HTTP→service | Multi-step commerce rules; trust client totals |
| L3 | Transactions, locks, ledgers, gateway, notify | Render HTML; skip policies |
| L4 | Relations, casts, scopes, invariants | Orchestrate multi-aggregate workflows |
| L5 | Persist / external I/O | Business policy without service mediation |

**Rule of thumb:** thin controllers · fat services · snapshots on orders · orthogonal statuses.

---

## 3. Directory map (where code lives)

```
app/
  Http/Controllers/     storefront + auth + payments + account
  Http/Requests/        validation
  Livewire/             AddToCart, Cart*, CheckoutWizard, ShopIndex, Wishlist*, Review*, Q&A
  Services/             ALL business writes (33 services)
  Payment/              PaymentGateway, RazorpayGateway, FakePaymentGateway
  Models/               domain aggregates (~45)
  Enums/                OrderStatus, OrderPaymentStatus, PaymentStatus
  Filament/             Resources, Pages, Widgets
  Policies/             deny-by-default RBAC
  Events|Listeners|Mail|Notifications|Observers|Console/Commands
resources/views/        layouts, home, shop, product, cart, checkout, account, components
resources/css/app.css   @theme tokens (design source of truth)
resources/js/           app.js + modules (carousels, motion, ui, cinema…)
routes/web.php          storefront routes (admin via Filament)
config/rythme.php       brand/runtime fallbacks (live values often Site Settings)
database/migrations/    schema
docs/                   PRD + these five always-read files + deep docs
tasks/                  phase tracker, plans, evidence (delivery authority)
```

---

## 4. Domain aggregates (single-brand catalogue)

| Aggregate | Core entities | Authority |
|---|---|---|
| **Catalogue** | Category (tree), Brand *(manufacturer label only)*, Product, Variant, Attributes, ImportSource, MerchandisingRule | Catalogue services + activation gates |
| **Cart** | Cart (user\|session), CartItem (price snapshot, no stock reserve) | `CartService` |
| **Checkout** | `CheckoutData` DTO, Address book, Coupon | `OrderService` + coupon/address services |
| **Order** | Order, OrderItem *(immutable snapshots)*, OrderStatusHistory | `OrderService` + `OrderStateMachine` |
| **Payment** | Payment, PaymentEvent, Refund | Payment* + `RefundService` + gateway |
| **Inventory** | InventoryMovement ledger | **`InventoryService` only** |
| **Fulfillment** | Shipment, ShipmentItem, ShipmentEvent | `FulfillmentService` → sync via OrderService |
| **Returns** | ReturnRequest*, ReturnReason | `ReturnRequestService` |
| **Customer** | User, Wishlist, Review, Contact, Newsletter, BackInStock | respective services |
| **Notify** | CommerceEvent, NotificationDelivery, Preference | CommerceNotification* services |
| **Content** | Homepage*, HeroSlide, Faq, Page, SeoEntry, SiteSetting | Homepage/Seo/Settings services |
| **Admin gov** | AdminAuditLog, staff roles on User | `AdminAuditService` + policies |

**Single-brand invariants**

1. One store identity (logo, merchant, support, inventory owner).  
2. `Brand` model = manufacturer label, **not** a seller.  
3. Server recomputes all money; client amounts ignored.  
4. Order item/address = snapshots; address book is mutable separately.  
5. Order status ⊥ payment status ⊥ shipment status.  
6. Stock mutates only through inventory ledger + idempotency keys.

---

## 5. State machines (normative short form)

**Order:** `pending → confirmed → processing → shipped → delivered` (+ `cancelled`; legacy `refunded` label). Illegal paths rejected by `OrderStateMachine`.

**Order payment summary:** `unpaid | paid | failed | refund_pending | refunded`

**Payment attempt:** `initiated → paid|failed`; `paid → refunded` after refund accounting. Never reuse terminal attempts.

**Shipment:** `draft → ready → dispatched → delivered` (+ cancel paths). Full dispatch/deliver syncs order via OrderService.

**Inventory op:** lock order → idempotency check → atomic stock update → append movement → commit.

---

## 6. HTTP surface (storefront)

| Area | Routes |
|---|---|
| Home/CMS | `GET /`, `GET /{slug}`, contact GET/POST |
| Catalogue | `GET /shop`, `GET /product/{slug}` |
| Cart | `GET /cart` + Livewire mutations (guest+user) |
| Wishlist | auth `GET /wishlist` |
| Auth | login/register/password/verify/logout |
| Checkout | auth `GET /checkout`, signed success |
| Pay | `POST .../razorpay/callback|webhook` (CSRF exempt, crypto-verified) |
| Orders | show, invoice, cancel, retry-payment, track-order |
| Returns | auth create/store/cancel |
| Account | profile, password, addresses, notifications, stock-alerts |
| SEO | `/sitemap.xml`, `/robots.txt` |
| Newsletter | `POST /newsletter` |

Admin: Filament `/admin` only (no public REST admin API).

---

## 7. Service catalogue (call these, not controllers)

`CartService` · `WishlistService` · `AddressService` · `CouponService` · `OrderService` · `OrderStateMachine` · `InventoryService` · `ProductQueryService` · `CategoryService` · `BrandService` · `PaymentEventService` · `PaymentRetryService` · `RefundService` · `FulfillmentService` · `ReturnRequestService` · `CommerceNotificationService` · `NotificationRetryService` · `NotificationReconciliationService` · `NotificationPreferenceService` · `HomepageDataService` · `SeoService` · `SiteSettingsService` · `ReviewService` · `ContactService` · `CatalogueAcquisitionService` · `CatalogueImportService` · `CataloguePublicationReviewService` · `ImportedProductActivationService` · `CatalogueExpansionManifestService` · `AdminAuditService` · `BackInStockSubscriptionService` · `FinancialReconciliationService`

---

## 8. AuthZ model

- Storefront: Laravel web auth; checkout/wishlist/account auth-gated; guest cart OK.  
- Orders: owner **or** bounded signed link for read/invoice; mutate = owner login.  
- Admin roles (deny-by-default): Super Admin · Admin(legacy) · Catalogue Manager · Order Manager · Support · Marketing · Finance.  
- Finance ≠ fulfillment; Support cannot mutate orders/catalogue/finance.  
- `customer` never enters Filament.  
- Matrix: `docs/permissions-matrix.md`.

---

## 9. Data & media

- Money: consistent decimal strategy; display formatting non-authoritative.  
- Media collections: product `gallery`/`og`, category `icon`, brand `logo`, hero, homepage blocks.  
- Image resolve: MediaLibrary → `public/images/products/{slug}.jpg` → fallback.  
- Seeds = demo only, not production stock/legal consent.  
- `config/rythme.php` + Site Settings for brand/contact/shipping fallbacks.  
- `withheld_public_pages`: shipping, returns, warranty, faqs until owner legal OK.

---

## 10. Cross-cutting patterns

| Concern | Pattern |
|---|---|
| Concurrency | DB transactions + `lockForUpdate` on checkout/stock |
| Idempotency | checkout keys, payment_events, inventory movements, refunds |
| Caching | Homepage/category caches; observers invalidate |
| Jobs | Queued mail/notifications; shared-host cron worker drain |
| Security | CSRF, throttles, signed URLs, SecurityHeaders/CSP, Razorpay HMAC |
| Tests | PHPUnit Feature/Unit; FakePaymentGateway; build via `npm run build` |

---

## 11. What NOT to build without PRD change

- Multi-vendor / seller portal / commission split  
- Guest checkout (browse+cart only)  
- Second storefront framework (React/Next)  
- Direct inventory writes outside `InventoryService`  
- Client-trusted prices/discounts  
- Publishing withheld legal pages without owner approval  
- Live Razorpay prod keys without staging E2E + owner command  

---

## 12. Flow verification pointer

Product upload + payment end-to-end **done vs pending** plan:  
`docs/FLOW_VERIFY_PRODUCT_PAYMENT.md` (CEO / SEO / phase lens).

## 13. Production priority programme

| Doc | Role |
|---|---|
| `docs/PRODUCTION_PRIORITY_PLAN.md` | W1 upload · W2 buy path · W3 variants · W4 no-demo · W5 client-owned optional details |
| `docs/CLIENT_HANDOVER_DETAILS.md` | What client fills later; empty = hidden |
| `docs/RAZORPAY_SETUP_GUIDE.md` | Test → live payment setup |

**Invariant:** Missing policy/tax/contact never blocks checkout; never display unfilled client details.

**Public visibility helper:** `App\Support\PublicContent` — pageHref / withheld / returns/tax flags; PageObserver clears slug cache.

---

*Keep this file short. Deep detail → PRD / domain-model / commerce architecture docs.*
