# RYTHME — Commerce Architecture Plan (Shop · Product · Cart · Wishlist · Checkout)

> Status: **PLAN — awaiting approval before implementation**
> Applies to tasks: `shop-list`, `product-detail`, `cart-checkout-payment`, `wishlist-about-contact`
> UX references: **Amazon** (product page, cart drawer, wishlist, checkout) · **Flipkart** (price box, filters, 2-column cart, address→payment checkout)
> Stack constraints: Laravel 13.24 · Blade · Livewire 3.8 · Alpine.js · Filament v3.3.54 · Spatie MediaLibrary · Razorpay ^2.9 (installed). **No React/Vue/jQuery/Next.js.**

---

## 1. Goals & Principles

| Principle | What it means here |
|---|---|
| **Testable** | Every flow (cart math, order totals, payment verify) has unit/feature tests; payments behind a `PaymentGateway` interface with a fake for tests |
| **Scalable** | Products decoupled from orders via **price/name snapshots**; carts support guests + users; filters are query-driven, cacheable |
| **Maintainable** | Thin controllers → **Service layer** for business logic → Eloquent models. No business logic in Blade or controllers |
| **Secure** | Server-side price & stock validation at every step; Razorpay signature verification + webhook HMAC; never trust client data |
| **Consistent** | Reuses existing conventions: `config/rythme.php`, kebab-case views, `_partial` naming, section kicker styling, Filament TextInput/TiptapEditor/MediaLibrary rules |

---

## 2. UX Reference Map (Amazon / Flipkart → what we adopt)

| Flow | Amazon | Flipkart | Rythme adopts |
|---|---|---|---|
| Shop listing | Left filter sidebar, sort bar, cards grid | Left category tree, price/brand filters, sort | Filter sidebar + sort bar + grid (reuse `product-card`) |
| Category nav | Mega drawer menu | Left tree | **Amazon-style drawer menu** (already planned in `shop-list`) |
| Product page | Left gallery + right buy-box, bullets, specs, related | Left gallery, right **price box** (MRP strikethrough, % off), offers, pincode | Gallery + Flipkart-style **price box** + variant selector + qty + Add-to-Cart/Buy-Now |
| Cart | Header badge, slide-over drawer, cart page with qty stepper | 2-column cart (items | price details), sticky checkout | **Drawer** (quick) + **cart page** (full) + header badge |
| Wishlist | Heart on cards, list page, "move to cart" | Heart, saved items page | Heart toggle + wishlist page + move-to-cart |
| Checkout | 1-click + multi-step: address → payment → review | Login/guest → address → payment (UPI/card/NB/wallet) → summary | Guest-ok **2-step wizard**: address → payment (Razorpay), review before pay |
| Trust | Free shipping, delivery estimate, secure badges | EMI, free shipping strip, seller assurance | Free-shipping + EMI + warranty badges in price box & cart |

### 2.1 Visual language — Shopify-like premium (NOT marketplace-dense)

UX follows a **premium Shopify theme** feel — not Amazon/Flipkart's dense marketplace UI:

- **Airy, editorial layouts** — generous whitespace, large imagery, restrained chrome; no cluttered sidebar stacks, no busy blue buttons
- **Cards** — `rounded-2xl`, soft shadows, white cards on paper, subtle hover lift (`card-hover-lift`, `img-zoom-hover`)
- **Buttons** — pill/rounded, brand red primary (`bg-brand`), outline secondary; targets ≥ 44×44px
- **Filters** — clean accordion/checkbox groups + pill sort row (not a deep Amazon tree)
- **Typography-driven** — Poppins everywhere; `section-title` scale; `em` accent in `brand-dark`
- **Micro-interactions** — only existing cinematic system (GSAP/Lenis `data-reveal`, hover lifts)
- **Trust, subtle** — delivery/EMI/warranty as small badges/lines in price box & cart, not banners
- **Design system** — ALL colors/fonts from `docs/architecture/02-design-system.md` + `@theme` tokens in `resources/css/app.css` (homepage "Rythme Red" theme). New pages use semantic tokens (`bg-brand`, `bg-paper`, `text-ink`…); legacy `gold*`/`rythme*` classes stay valid aliases on existing sections.

---

## 3. Page & Route Map

```php
// Storefront (web.php)
Route::get('/shop',                         [ShopController::class, 'index'])->name('shop.index');
// category filter via ?category=slug (keeps one URL shape; drawer menu deep-links to /shop?category=acoustic-guitars)
Route::get('/product/{product:slug}',       [ProductController::class, 'show'])->name('product.show');

// Cart (DB-backed, guest + user)
Route::post('/cart/items',                  [CartController::class, 'add'])->middleware('throttle:20,1')->name('cart.add');
Route::get('/cart',                         [CartController::class, 'index'])->name('cart.index');
Route::patch('/cart/items/{item}',          [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/items/{item}',         [CartController::class, 'remove'])->name('cart.remove');

// Wishlist (auth required)
Route::middleware('auth')->group(function () {
    Route::get('/wishlist',                 [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/toggle',         [WishlistController::class, 'toggle'])->name('wishlist.toggle');
});

// Checkout + payment — LOGIN FORCED (no guest checkout; redirect to login with ?intended)
Route::middleware(['auth', 'throttle:5,1'])->group(function () {
    Route::get('/checkout',                 [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/place',          [CheckoutController::class, 'store'])->name('checkout.store');
});
Route::get('/checkout/success/{order}',     [CheckoutController::class, 'success'])
      ->middleware(['auth', 'signed'])->name('checkout.success');
// Payment callbacks (no CSRF — Razorpay posts; CSRF excluded in bootstrap/app.php for these)
Route::post('/payment/razorpay/callback',   [RazorpayController::class, 'callback'])->name('payment.razorpay.callback');
Route::post('/payment/razorpay/webhook',    [RazorpayController::class, 'webhook'])->name('payment.razorpay.webhook');
```

**Cart is guest-friendly** (session-backed, `session_id` column) — users can browse & fill a cart before login; login merges guest cart into user cart; checkout is where login is forced.

**Interactive parts = Livewire** (already installed 3.8.3): cart drawer, header badge, wishlist button, shop filters, checkout wizard, qty steppers. Static/SEO parts = Blade.

---

## 4. Data Model (migrations — 10 new tables)

```
categories            brands
├─ id                 ├─ id
├─ parent_id (self FK, nullable → 2-level tree)   ├─ name, slug
├─ name, slug         ├─ logo (MediaLibrary)      ├─ is_active
├─ description        └─ sort_order
├─ icon (MediaLibrary)
├─ sort_order, is_active
└─ seo_title, seo_description

products                                  product_variants
├─ id                                     ├─ id
├─ category_id FK, brand_id FK            ├─ product_id FK
├─ name, slug, sku (unique)               ├─ name            e.g. "Sunburst · 6-string"
├─ short_description                      ├─ options (JSON)  e.g. {"finish":"Sunburst","strings":6}
├─ description (Tiptap HTML)              ├─ sku (unique)
├─ price (INR)                            ├─ price_override (nullable)
├─ compare_at_price (nullable = MRP → % off) ├─ stock, is_active
├─ stock, low_stock_threshold             └─ unique(product_id, name)
├─ is_active, is_featured
├─ meta_title, meta_description           images → Spatie MediaLibrary on Product
├─ timestamps, softDeletes                collection: 'gallery' (+ 'og')
└─ indexes: (category_id, is_active), (slug)

carts                     cart_items
├─ id                     ├─ id
├─ user_id FK nullable    ├─ cart_id FK
├─ session_id nullable    ├─ product_id FK
├─ unique per user/session├─ product_variant_id FK nullable
└─ timestamps             ├─ qty
                          ├─ unit_price        ← snapshot at add
                          └─ unique(cart_id, product_id, variant_id)

wishlists                 addresses
├─ id                     ├─ id
├─ user_id FK             ├─ user_id FK
├─ product_id FK          ├─ type: shipping|billing
└─ unique(user_id, product_id)  ├─ name, phone, email
                            ├─ line1, line2, city, state, pincode
                            ├─ country (default IN)
                            ├─ is_default
                            └─ timestamps

orders                                        order_items
├─ id                                        ├─ id
├─ order_number (unique, e.g. RYM-2026-000123) ├─ order_id FK
├─ user_id FK nullable (guest orders!)       ├─ product_id FK nullable (soft-delete safe)
├─ email (guest required)                    ├─ product_variant_id FK nullable
├─ status: pending|confirmed|processing|     ├─ name, sku, options JSON   ← snapshot
│           shipped|delivered|cancelled|     ├─ unit_price, qty, total    ← snapshot
│           refunded                         └─ timestamps
├─ payment_status: unpaid|paid|failed|refunded
├─ subtotal, discount, shipping_fee, tax, total
├─ currency (INR)
├─ shipping_address JSON   ← full snapshot (address book edits must not alter placed orders)
├─ billing_address JSON
├─ notes, placed_at
└─ timestamps

payments                        order_status_history
├─ id                           ├─ id
├─ order_id FK                  ├─ order_id FK
├─ gateway (razorpay)           ├─ from, to (statuses)
├─ method: card|upi|netbanking|wallet|emi ├─ note, actor
├─ gateway_order_id, gateway_payment_id, gateway_signature
├─ amount, currency, status (initiated|paid|failed|refunded)
├─ payload JSON
└─ timestamps
```

**Key decisions (ADR-lite):**
1. **DB cart, not cookie cart** — merge-on-login, analytics, cross-device (user), recoverable. Guest = `session_id` column.
2. **Snapshot prices on `cart_items.unit_price` AND re-validate at checkout** — price changes never silently hit the customer.
3. **Order stores JSON address + item copies** — immutable history; address book is editable, orders are not.
4. **Variants optional** — instruments are mostly single-SKU today; variants (finish/size/strings) supported without breaking simple products (`variant_id` nullable).
5. **Wishlist = auth-only** (Amazon/Flipkart both require login) — no guest wishlist table.
6. **Soft deletes on products** — order history keeps `name`/`sku` snapshots anyway; cart items of soft-deleted products get flagged inactive.
7. **`orders.status` vs `payment_status` separate** — payment may succeed while fulfillment is pending; status machine audited in `order_status_history`.

---

## 5. Application Layer (Laravel best practice)

```
app/
├── Models/            Product, Category, Brand, ProductVariant, Cart, CartItem,
│                      Wishlist, Address, Order, OrderItem, Payment, OrderStatusHistory
├── Services/          ProductQueryService, CartService, WishlistService,
│                      AddressService, OrderService, CheckoutService
├── Payment/           PaymentGateway (interface), RazorpayGateway,
│                      FakePaymentGateway (tests)
├── DTOs/              CartItemData, CheckoutData, PaymentResult (readonly classes)
├── Http/
│   ├── Controllers/   ShopController, ProductController, CartController,
│   │                  WishlistController, CheckoutController, RazorpayController
│   └── Requests/      AddToCartRequest, UpdateCartItemRequest, CheckoutRequest,
│                      WishlistToggleRequest, StoreAddressRequest
├── Livewire/          CartDrawer, CartBadge, WishlistButton, ShopFilters,
│                      CheckoutWizard, CartPage, AddToCart
├── Events/            CartChanged, OrderPlaced, PaymentSucceeded, PaymentFailed
├── Listeners/         SendOrderConfirmation (queue), DecrementStock,
│                      NotifyAdminOnOrder, RefreshCartBadge
├── Policies/          OrderPolicy (owner/admin), WishlistPolicy, AddressPolicy
└── Filament/Resources/ ProductResource, CategoryResource, BrandResource,
                        OrderResource, CustomerResource, AddressResource(ro)
```

**Service responsibilities (no business logic in controllers):**

| Service | Key methods | Notes |
|---|---|---|
| `CartService` | `getOrCreateCart()`, `addItem()`, `updateQty()`, `removeItem()`, `clear()`, `mergeGuestCart()`, `totals()`, `validateStock()` | Guest via session; merge on `login` event; emits `CartChanged` |
| `WishlistService` | `toggle()`, `items()`, `contains()`, `moveToCart()` | |
| `ProductQueryService` | `shopQuery(array $filters)`, `related()`, `bestsellers()`, `categoryTree()` | Eager loads + cached; filters: category, brand, price min/max, sort (popularity/price/new), in-stock |
| `AddressService` | `listForUser()`, `store()`, `update()`, `setDefault()`, `snapshot()` | |
| `OrderService` | `createFromCheckout(CheckoutData)`, `placeOrder()`, `transitionStatus()`, `cancel()` | Creates order + items + address snapshots; computes totals server-side |
| `CheckoutService` | `begin()`, `availableSteps()`, `place(CheckoutData)` | Orchestrates cart → order → payment |
| `PaymentGateway` | `createOrder(Order)`, `verify(PaymentResult)`, `handleWebhook()` | Interface → `RazorpayGateway` (test keys); `FakePaymentGateway` in tests |

**Checkout sequence (Flipkart-style, Razorpay test, login forced):**

```
/cart → login (if guest; intended=/checkout, guest cart auto-merges) → /checkout
  1. Address step   → saved addresses (radio) + "Add new address" (Livewire form, StoreAddressRequest)
  2. Payment step   → POST /checkout/place (CheckoutRequest)
                       ├─ server: validate cart + stock (lockForUpdate) + recompute totals
                       ├─ OrderService::createFromCheckout → order status=pending, payment_status=unpaid
                       ├─ RazorpayGateway::createOrder → gateway_order_id
                       ├─ client: Razorpay.checkout modal (test keys, UPI/card/NB/wallet)
  3. Callback       → POST /payment/razorpay/callback (signature verify!)
                       ├─ valid  → payment_status=paid, order=confirmed, DecrementStock,
                       │           SendOrderConfirmation (queued), cart cleared, NotifyAdmin
                       └─ invalid → payment_status=failed, order=pending (retry allowed)
  4. Webhook        → POST /payment/razorpay/webhook (HMAC; no CSRF) — async reconciliation
  5. Success page   → /checkout/success/{order} (signed URL), order number + summary
```

Stock guard: `Product::whereKey($id)->where('stock','>=',$qty)->lockForUpdate()->first()` — never oversell under concurrency.

---

## 6. Admin (Filament v3.3.54 — new Resources)

```
SHOP group:
  ProductResource    → TextInput(name/slug/sku/price/compare_at_price/stock), TiptapEditor(description),
                       SpatieMediaLibraryFileUpload(gallery), Repeater(product_variants),
                       Select(category/brand), Toggle(is_active/is_featured), SEO tabs
  CategoryResource   → TextInput, Select(parent, self), Toggle, sort_order
  BrandResource      → TextInput, logo upload, Toggle
COMMERCE group:
  OrderResource      → View-only tables (items, addresses), Badge column status,
                       Actions: mark processing/shipped/delivered/cancelled (transitionStatus + history)
  CustomerResource   → read-only users + addresses relationship
  (future) CouponResource, ReviewResource
```

Rules honoured: heading/title → `TextInput`; body → `TiptapEditor`; images → `SpatieMediaLibrary`; multi-group sidebar.

---

## 7. Frontend Structure

```
resources/views/
├── shop/
│   ├── index.blade.php        ← filters sidebar + sort bar + grid + pagination
│   ├── _filters.blade.php     ← category tree, brand checkboxes, price range, in-stock toggle
│   ├── _sort-bar.blade.php
│   └── _breadcrumbs.blade.php
├── product/
│   ├── show.blade.php         ← gallery | price box | variant + qty + CTA | tabs | related
│   ├── _gallery.blade.php     ← Swiper thumbnails
│   ├── _price-box.blade.php   ← MRP strikethrough, % off, EMI line, badges
│   └── _specs.blade.php
├── cart/index.blade.php       ← 2-column (items | price details + sticky checkout) — Flipkart style
├── wishlist/index.blade.php
├── checkout/
│   ├── index.blade.php        ← Livewire CheckoutWizard (steps indicator)
│   ├── _address-step.blade.php
│   ├── _payment-step.blade.php
│   └── success.blade.php      ← order confirmation + JSON-LD Order
├── livewire/                  ← cart-drawer, cart-badge, wishlist-button, add-to-cart, shop-filters, checkout-wizard
└── components/product-card.blade.php  ← upgraded: image, brand, name, price box, quick-add, wishlist heart
```

- **Cart drawer** — Amazon-style slide-over right panel (backdrop blur, Esc close, focus trap, aria), listing items + qty steppers + subtotal + "Proceed to checkout".
- **Header badge** — Livewire `CartBadge` listens `cart-updated` event; count animates.
- **All CTAs ≥ 44×44px, sticky mobile checkout button, cart count visible in header on every page** (cart UX checklist 2026).
- Micro-interactions stay Alpine/GSAP; anything touching cart/wishlist state = Livewire.

---

## 8. Caching · SEO · Security · Performance

**Caching** (model observers invalidate):
- Category tree → `cache()->rememberForever('categories.tree')`
- Shop query pages → tag-based (`products`, per-category), short TTL
- Product detail + related → per-product tags
- Homepage bestsellers/new-arrivals → same pattern as existing sections

**SEO:**
- `shop` → meta title/desc, canonical, breadcrumb JSON-LD, `?page=` rel prev/next
- `product` → `Product` JSON-LD (name, image, brand, offers/price/availability), breadcrumb, unique title/desc, single `h1`
- Slugs URL-driven (`/product/{slug}`, `?category=`), 404 + redirect for soft-deleted

**Security checklist:**
- [ ] Server-side totals only (client values ignored)
- [ ] Stock optimistic lock at place-order
- [ ] Razorpay signature verify on callback + webhook HMAC + amount match
- [ ] CSRF on all storefront POSTs (except Razorpay webhook/callback — excluded in `bootstrap/app.php`)
- [ ] `throttle` on cart.add (20/min) and checkout.place (5/min)
- [ ] Form Requests everywhere; `$fillable`/casts tight
- [ ] Policies: order/wishlist/address ownership
- [ ] Signed URL for success page

**Performance:** eager loading (images via MediaLibrary conversions), cursor/simple pagination on shop grid, `loading="lazy"` images, images preloaded on product page only.

---

## 9. Testing Strategy (extends current 26 tests)

- **Unit:** CartService totals/qty rules · OrderService totals + snapshot integrity · signature verify (fixtures) · transitionStatus guards
- **Feature:** shop filters (category/brand/price/sort) · add-to-cart guest + auth · merge guest→user cart on login · qty clamp to stock · wishlist toggle/unauth redirect · checkout happy path with `FakePaymentGateway` · stock guard (qty > stock rejected) · webhook signature reject · guest checkout creates order with email
- **Filament:** resource can list/create Product (admin)
- Every phase must keep `php artisan test` green + `npm run build` green (AGENT_RULES_STRICT §7).

---

## 10. Build Phases (each = own commit(s) + tests + push, PR at end)

| Phase | Scope | DoD (definition of done) |
|---|---|---|
| **A. Domain foundation** | 10 migrations, models+relations, factories, **seeders from Bajaao catalog** (categories 10+, brands 8+, products 30+ with variants — names/features/prices referenced from bajaao.com, **copy uniquely rewritten**, images = Bajaao product shots with source comment per image rules; **no Amazon sourcing**), Filament Product/Category/Brand resources | `migrate:fresh --seed` clean; admin CRUD works; tests green |
| **B. Shop list** | Routes, ShopController, ProductQueryService, filters sidebar, sort, pagination, grid, breadcrumbs, **Amazon drawer menu** in navbar | /shop renders, filters work, mobile responsive, tests green |
| **C. Product detail** | Gallery (Swiper), price box (MRP/%), variant selector, qty, Add-to-Cart (Livewire), tabs (desc/specs), related, JSON-LD | /product renders, add-to-cart works, tests green |
| **D. Cart** | CartService + tables, drawer (Livewire), cart page (2-col), badge, merge-on-login | drawer/page/badge all sync; tests green |
| **E. Wishlist** | Table + toggle on cards/detail + page + move-to-cart | auth flows work; tests green |
| **F. Checkout + Razorpay** | Address CRUD, CheckoutWizard, OrderService, RazorpayGateway (test), callback verify, webhook, success page, queued emails, stock decrement | full happy path with fake + real test keys; tests green |
| **G. Admin commerce** | OrderResource (status actions), CustomerResource | admin manages orders; tests green |

Dependencies: A → B → C → D → F (needs D) ; E independent (needs A). G last.

---

## 11. Future-ready (not in scope now, schema already accommodates)

- Reviews/ratings (`reviews` table later — products already linkable)
- Coupons (discount column + `coupons` table later)
- Shipping methods/rates (interface in OrderService; flat fee now)
- Razorpay EMI display (compare_at_price already drives % off + EMI line)
- Order tracking (status history table ready)
- Wishlist share/public lists (wishlists table extensible)

---

## 12. Decisions (approved 2026-08-13)

| # | Question | Decision |
|---|---|---|
| 1 | Guest checkout? | **NO — login forced** at checkout (and wishlist). Guests browse + cart freely; checkout redirects to login with `intended` URL; guest cart merges on login. |
| 2 | Seed data source | **Bajaao only** (bajaao.com) — product names/features/prices as reference; images = Bajaao product shots (AGENT_RULES image rule 1); all copy uniquely rewritten (rule 3). **No Amazon sourcing.** |
| 3 | Currency | **INR only** (₹). |
| 4 | UX design | **Shopify-like premium theme** (§2.1) — NOT Amazon/Flipkart dense UI. Colors/fonts = homepage "Rythme Red" design system (§13 + `02-design-system.md`). |
| 5 | Design-system upkeep | Future color/font change → agent updates design system tokens + docs itself (protocol §6 in `02-design-system.md`), then migrates components. |

## 13. Design System (site-wide, mandatory)

Homepage theme = design system for the whole site (shop, product, cart, wishlist, checkout, auth, admin).

- **Source of truth:** `resources/css/app.css` `@theme` tokens + `tailwind.config.js` (mirror) + `docs/architecture/02-design-system.md`
- **Tokens:** `brand` `#D50808` · `brand-dark` `#A30404` · `brand-light` `#FF5252` · `brand-soft` `#FF6B6B` · `ink` `#0A0A0A` · `paper` `#FFFDF7` · `paper-dark` `#F5F5F5` · `muted` `#6B6B6B` · font: **Poppins only**
- Every new page uses semantic utilities (`bg-brand`, `text-ink`, `bg-paper`, `font-sans`) — legacy `gold*`/`rythme*` aliases remain valid.
- Change protocol: see `02-design-system.md` §6.

---

## 14. Enterprise rules compliance — Sections 5–6 (applied 2026-08-13)

### Section 5 — UI/UX (binding for Phase B+ storefront)
- Mobile-first responsive Tailwind everywhere; dark sections per design system.
- **Zero-refresh workflows**: cart drawer, cart badge, wishlist buttons, shop
  filters, qty steppers — ALL Livewire 3 (no full page reloads).
- Network feedback mandatory: `wire:loading` spinners, skeleton cards on
  shop grid load, disabled submit state during Razorpay payment execution.
- Targets ≥ 44×44px; sticky mobile checkout button (cart UX checklist).

### Section 6 — Smart e-commerce (deferred — infra decisions needed)
- **Laravel AI SDK (`laravel/ai`)**: not installed. Add ONLY when a
  recommendations / support / search task begins (user approval).
- **Semantic search `whereVectorSimilarTo()`**: verified present in Laravel
  13.24 Builder, but requires **PostgreSQL + pgvector**. Project DB is
  SQLite (dev) / MySQL (prod plan) — decision required: migrate to Postgres
  or use keyword search fallback. NOT implementable on SQLite.
- Razorpay webhook signature verification (crypto check before acting) —
  binding when Phase F lands.
