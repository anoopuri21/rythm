# Rythme Current-State Architecture Overview

**Audit date:** 29 August 2026  
**Branch / audited base:** `rhythm-uat` / `60b522cad70b249be6aebbbbadaa95ce812556ed`  
**Authority:** Agent 0  
**Audit mode:** read-only inventory of the existing application; this does not reset the canonical delivery sequence.

## 1. Platform

- Laravel 13.24.0, PHP 8.3, Livewire 4 and Filament 5.
- Blade/custom CSS/Vanilla JS storefront; Vite 7 and Tailwind 4 build pipeline.
- MySQL 8.x production target; PHPUnit uses in-memory SQLite.
- Razorpay SDK behind `PaymentGateway`; fake gateway for isolated tests.
- Spatie Media Library for managed media.
- Database-backed cache, sessions/queues as environment-configured; shared-hosting scheduler drains queue with a bounded worker.

Frontend entry point is `resources/js/app.js`. It imports Swiper styles and initializes `carousels.js`, `motion.js`, `ui.js`, `cinema.js` and `categories-pin.js`. Dependencies include Swiper, GSAP, Lenis and CountUp.

## 2. HTTP surface

### Public/controllers

- Home: `HomeController`.
- Shop/search: `ShopController` plus `ShopIndex` Livewire.
- Product: `ProductController` plus add-to-cart, wishlist, review and Q&A Livewire components.
- Cart: `CartController`; guest and account carts are supported.
- CMS/contact/newsletter: `PageController`, `ContactController`, `NewsletterSubscriptionController`.
- Order lookup/detail/invoice: `OrderController`, with ownership or bounded signed-link checks.
- Sitemap/robots: `SitemapController`.
- Razorpay callback/webhook: `RazorpayController`.

### Auth/account

Auth controllers cover register, login/logout, password reset and verification. `AccountController` manages profile/password/address book. `NotificationController` owns the customer notification center and preferences.

### Livewire inventory

`AddToCart`, `CartBadge`, `CartDrawer`, `CartPage`, `CheckoutWizard`, `ShopIndex`, `WishlistBadge`, `WishlistButton`, `WishlistPage`, `ReviewSection`, and `ProductQuestionSection`.

Checkout is route-authenticated and also checks the authenticated user in the component before order creation. Guest carts merge on login.

## 3. Filament inventory

Resources: Admin Audit Logs, Brands, Categories, Contact Messages, Coupons, Customers, FAQs, Hero Slides, Homepage Blocks, Homepage Category Rows, Homepage Sections, Newsletter Subscribers, Notification Deliveries, Orders, Pages, Product Questions, Products, Reviews and Staff. Settings is a dedicated Filament page. Dashboard widgets expose latest orders and guarded stats.

Orders are checkout-created only. Product imports require reviewed activation. Financial actions are Finance-only. Notification delivery evidence is read-only for its approved role.

## 4. Blade/view architecture

- Global shell: `resources/views/layouts/app.blade.php`.
- Global components: navbar, footer, category drawer, product cards, order list and decorative components.
- Homepage is split into hero, USP, categories, new arrivals, trending, category rows, promos, advantages, best deals, category banners, recently launched and brands.
- Dedicated views exist for auth, account, notifications, cart, checkout, product, shop, wishlist, orders/invoice/tracking, CMS pages, mail and errors.
- Navbar category data is supplied by a global view composer.

## 5. Domain/data inventory

### Catalogue/content

`Category` parent/children/products/attributes/homepage row; `Brand` products; `Product` category/brand/variants/attributes/media/import source/reviews/questions/cart/wishlist/inventory; `ProductVariant`; `ProductAttribute`; `ProductAttributeValue`; `ProductImportSource`; `HomepageSection`; `HomepageCategoryRow`; `HomepageBlock`; `HeroSlide`; `Faq`; `Page`; `SeoEntry`.

### Customer/engagement

`User` addresses/orders/reviews/questions/notification deliveries/preferences; `Address`; `Wishlist`; `Review`; `ProductQuestion`; `ContactMessage`; `NewsletterSubscriber`.

### Commerce/finance

`Cart` items/user; `CartItem` cart/product/variant; `Order` user/items/payments/refunds/status history/inventory movements/payment events/shipments; `OrderItem`; `Payment`; `PaymentEvent`; `Refund`; `InventoryMovement`; `Coupon`.

### Notifications/fulfillment

`CommerceEvent` deliveries; `NotificationDelivery` event/user; `NotificationPreference`; `Shipment` order/creator/items/events; `ShipmentItem`; `ShipmentEvent`.

## 6. Migrations and seeders

Migration history creates users/cache/jobs, newsletter, media, catalogue, cart, wishlist, addresses, orders/items/payments/history, contact, homepage/CMS/SEO, reviews/coupons/settings, hero/blocks/FAQs, shop fields, roles, hardened orders/payments, refunds, attributes, inventory ledger, payment events/idempotency, interaction moderation, truthful content, import provenance, audit logs, staff MFA, publication review, homepage category rows, partial-refund operations, notification architecture and fulfillment domain.

Seeders: `DatabaseSeeder`, `CategorySeeder`, `BrandSeeder`, `ProductSeeder`, `HomepageDataSeeder`, `HomepageSectionSeeder`, and `PageSeeder`. Seed data is development/demo input, not production stock or legal approval.

## 7. Service boundaries

- Catalogue/query/import: ProductQuery, Category, Brand, acquisition, expansion manifest, import, publication review and imported activation services.
- Commerce: Cart, Wishlist, Address, Coupon, Order and Inventory services.
- Finance: PaymentEvent, PaymentRetry, Refund and read-only FinancialReconciliation services.
- Notifications: CommerceNotification, preferences, retry and reconciliation services/listeners.
- Fulfillment: `FulfillmentService` provides locked allocation and bounded shipment transitions; admin/customer presentation remains next work.
- Content/support: HomepageData, SiteSettings, SEO, Review, ProductQuestion, Contact and AdminAudit services.

## 8. Media/storage

Spatie's `media` table manages Product `gallery`/single `og`, Category `icon`, Brand `logo`, Hero desktop/mobile images and HomepageBlock image. Product imagery first uses managed gallery media, then committed `public/images/products/{slug}.jpg`, then a caller fallback. Imported activation requires at least one local gallery image and records commercial-use approval metadata.

Default private disk is `storage/app/private`; public disk is `storage/app/public` served via `/storage`. `public/storage` must link to the public storage directory. S3 is configurable but not assumed. No image conversions are registered; original uploads are served, so responsive conversion/optimization remains open work.

## 9. Authorization/security

- Storefront uses Laravel web auth; guest-only auth forms and auth-only account/checkout/wishlist routes.
- Email verification links are signed and throttled.
- Order detail/invoice permit owner or valid bounded signed link; mutating cancel/retry operations require owner login.
- Razorpay callback/webhook are CSRF-exempt because provider requests cannot carry CSRF tokens; both require cryptographic/provider verification before state mutation.
- SecurityHeaders middleware prepends CSP and browser security headers.
- Admin roles: Super Admin, legacy Admin, Catalogue Manager, Order Manager, Support, Marketing and Finance.
- `AdminAccess`, model policies and strict Filament authorization implement deny-by-default role permissions.
- Staff MFA/TOTP and audited sensitive changes are present.
- Transactions, row locks, unique identities and aggregate bounds protect checkout, inventory, payments, refunds, notifications and fulfillment.

## 10. Operational architecture

Shared hosting runs `schedule:run` every minute. The schedule starts `queue:work --stop-when-empty --max-time=50 --tries=3 --timeout=45` with overlap protection; no persistent worker is assumed. Reconciliation commands are read-only; retry commands are bounded. Environment secrets stay outside Git. Production setup requires the correct `.env`, config/cache build, migrations, storage link, writable storage/bootstrap cache, cron, queue and log rotation.

## 11. Current bottlenecks/findings

- Homepage data is cached and bounded, but representative Popular Category imagery can issue one bounded query per category on cache rebuild.
- Navbar category composition runs a schema check and cache lookup globally; it now degrades on an unmigrated database but remains repeated layout work.
- No media conversions/responsive derivatives are configured; large originals may harm LCP/bandwidth.
- GSAP, Lenis and Swiper share the main entry bundle; route-level/lazy loading is not implemented.
- Product/category eager loading exists on key listings; final query profiling and MySQL explain evidence remain Phase 13 work.
- Fulfillment domain exists, but operational admin/customer shipment UI is incomplete.
- Tax/HSN, returns/RMA, legal rules and invoice numbering require professional approval and later Phase 10 chunks.
- Current owner environment was observed pointing `rythm.test` at unrelated `maverick_academy`; it must use `rhythm_db` before reliable UAT.

## 12. Dependency conclusion

No major application module is unclassified in this static inventory. Runtime dependencies and unavoidable gates remain explicit: MySQL 8, PHP extensions/Composer dependencies, Node build dependencies, writable storage/link, cron/queue, Razorpay and mail credentials, DNS, professional tax/legal decisions, backup/monitoring services and owner UAT. These are known dependencies, not hidden assumptions.
