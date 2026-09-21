# Flow verification plan — Product upload + Payment

**Roles lens:** CEO (business readiness) · SEO (discoverability) · Phase/delivery (gate status)  
**Date:** 2026-09-12  
**Scope:** Single-brand catalogue publish path + money path (cart → pay → ledger)  
**Method:** Code + phase evidence audit (not a live browser UAT in this sitting)  
**Authority cross-check:** `PHASES.md`, `MEMORY.md`, Phase 6/6A/4/8/16 evidence  

**Overall verdict**

| Flow | Code + phase status | Live/ops readiness |
|---|---|---|
| **Product upload / publish** | **DONE** (manual Filament + import/activation pipeline) | **PENDING owner** — media/content rights; real catalogue load on prod host |
| **Payment (test)** | **DONE** (gateway, callback, webhook, retry, refund, tests, owner UAT) | **PENDING owner** — live Razorpay keys + prod webhook + HTTPS |
| **End-to-end “sell live”** | Conditional GO only | **NOT LIVE** — 4 pre-live blockers |

---

## 1. CEO view — can we sell?

### 1.1 What is already bankable (product side)

Staff can run a single-brand catalogue without engineering:

| Capability | Where | Status |
|---|---|---|
| Create/edit product (name, slug, SKU, price, MRP, stock, flags) | Filament `ProductResource` | ✅ |
| Category + manufacturer brand attach | `CategoryResource`, `BrandResource` | ✅ |
| Gallery + OG + variant images | Spatie MediaLibrary on product/variants | ✅ |
| Variants (SKU, price override, stock) | Repeater on product form | ✅ |
| Manual publish (`is_active`) for non-imported | Toggle on form/table | ✅ |
| **Imported** publish only via reviewed activation | `approve_activate_import` + `ImportedProductActivationService` | ✅ |
| Activation gates: real stock + local gallery media + attestations | Service + Filament action checkboxes | ✅ |
| Bulk catalogue acquire/import commands | `AcquireCatalogue*`, `ImportCatalogue*` | ✅ |
| Merchandising / homepage placement rules | `ProductMerchandisingRuleResource` | ✅ |
| Shop + PDP consume active catalogue | `ProductQueryService`, shop/PDP | ✅ |
| Phase evidence | Phases **6, 6A, 3, 11** COMPLETE; UAT step 20 (edit price) PASS | ✅ |

### 1.2 What is already bankable (payment side)

| Capability | Where | Status |
|---|---|---|
| Guest cart → login merge → auth checkout | `CartService`, `CheckoutWizard` | ✅ |
| Server-side totals (subtotal, coupon, shipping, tax) | `OrderService` + settings | ✅ |
| Order + item/address **snapshots** | `createFromCheckout` | ✅ |
| Payment initiation + gateway order id | `recordPaymentInitiation` / Razorpay `createOrder` | ✅ |
| Razorpay JS checkout when keys present | `CheckoutWizard::placeOrder` | ✅ |
| Fake gateway when keys empty (local/dev) | `RazorpayGateway::resolve` / `FakePaymentGateway` | ✅ |
| Signature verify on callback | `RazorpayController` + gateway `verify` | ✅ |
| Webhook HMAC + event allow-list + dedupe | webhook + `PaymentEventService` | ✅ |
| Exactly-once **markPaid** + inventory capture | `OrderService::markPaid` + `InventoryService` | ✅ |
| Fail / authorize paths | `markFailed`, `markPaymentAuthorized` | ✅ |
| Payment retry (bounded) | `PaymentRetryService` + order retry route | ✅ |
| Cancel + refund_pending → Finance process | `RefundService` + Order Filament actions | ✅ |
| Read-only reconcile | `payments:reconcile` | ✅ |
| Phase evidence | **0A, 4, 8, 12, 16** (Razorpay **test** card UAT PASS) | ✅ |

### 1.3 CEO blockers (cannot claim “live store” yet)

| # | Blocker | Owner action |
|---|---|---|
| 1 | Razorpay **live** keys + production webhook URL verified | Dashboard + `.env` on host (not in git) |
| 2 | Legal wording (AS-H011) before sensitive public pages | Approve copy → then un-withhold |
| 3 | Catalogue **commercial media/content rights** | Clear Bajaao-derived / stock assets for sale |
| 4 | Host **HTTPS/TLS** on real domain | DNS + cert on cPanel/host |
| — | Phase 18 deploy | Explicit deploy command only |
| — | Returns/tax **values** still safe-default off | Business/legal enablement later |

**CEO one-liner:** *Engine is built and test-proven; live money + legal catalogue rights are the remaining business gates—not missing CRUD screens.*

---

## 2. Product upload flow — step map (as implemented)

```
A. MANUAL (day-to-day ops)
   /admin → Products → Create
     → name, slug, sku, category, brand (manufacturer)
     → price, compare_at, stock, low_stock_threshold
     → description, SEO, HSN/tax fields (optional snapshots)
     → gallery / og media
     → optional variants (+ stock/images)
     → is_active ON  (only if NOT import-sourced)
   → appears on /shop + /product/{slug} when active + in stock rules

B. IMPORT PIPELINE (bulk / acquisition)
   acquire commands → run directory payloads
   → CatalogueImportService::import (validate, hash, create inactive product + importSource)
   → staff reviews in admin
   → Action "Approve & activate"
        requires: price/stock verified attestation, commercial-use approval,
                  gallery media on local disk, stock > 0 (product or active variant)
   → ImportedProductActivationService::approveAndActivate
   → is_active true + audit log

C. AFTER PUBLISH (storefront)
   ProductQueryService filters active catalogue
   → ShopIndex / PDP / merchandising / homepage blocks
```

### 2.1 Product upload — DONE checklist

- [x] Admin CRUD products/categories/brands  
- [x] Media upload (Spatie)  
- [x] Variants + per-variant stock/price  
- [x] Import provenance table + inactive-by-default imports  
- [x] Activation service + bulk activate  
- [x] RBAC: catalogue.manage for mutations  
- [x] Storefront listing/detail for active products  
- [x] Automated tests: catalogue import/acquisition/expansion/commerce catalog/product page  
- [x] Owner UAT: admin price edit reflects on storefront  

### 2.2 Product upload — PENDING / residual

| Item | Priority | Notes |
|---|---|---|
| **Commercial rights clearance** for images/copy used at scale | **P0 launch** | Pre-live #3 |
| Production MySQL catalogue load (real SKUs, not only seed/UAT) | **P0 launch** | Ops on deploy host |
| Media responsive conversions / LCP optimization | P2 | Known perf backlog |
| Rich attribute/filter completeness per category | P2 | Works; polish residual |
| Search relevance/perf residual (tracker once PARTIAL) | P2 | Weighted search exists; not a upload blocker |
| Continuous scrape/acquire in production | P3 | Pipeline exists; rights + rate-limit policy |
| Owner runbook one-pager “how to add first 20 SKUs” | P1 | Ops clarity (optional doc) |

**Not pending as features:** multi-vendor upload, seller portal, client-side price authority.

---

## 3. Payment flow — step map (as implemented)

```
1. Browse/PDP → AddToCart (Livewire) → CartService
2. Cart drawer/page → Checkout (auth required)
3. CheckoutWizard
     address step → AddressService
     coupon optional → CouponService (server validate)
     placeOrder → OrderService::createFromCheckout (snapshots, unpaid)
                 → Payment initiation + RazorpayGateway::createOrder
4a. Keys configured → Razorpay checkout modal (JS)
4b. Keys missing → Fake gateway path (dev only; prod must set ALLOW_FAKE=false)
5. Success paths:
     - JS confirmPayment → gateway.verify → OrderService::markPaid
     - POST /payment/razorpay/callback (signature)
     - POST /payment/razorpay/webhook (HMAC, payment.authorized|captured|order.paid)
6. markPaid (exactly once): payment paid, order confirmed, InventoryService::capture per line,
   cart clear, notifications queued
7. Failure: markFailed; customer may retry via PaymentRetryService (bounded)
8. Cancel paid: stock restore + Refund request pending → Finance process_pending_refund
9. Ops: payments:reconcile (read-only)
```

### 3.1 Payment — DONE checklist

- [x] Gateway interface + Razorpay + Fake  
- [x] Env keys: `RAZORPAY_KEY_ID|SECRET|WEBHOOK_SECRET`, `RAZORPAY_ALLOW_FAKE_PAYMENTS`  
- [x] Callback + webhook controllers, throttles, CSRF exemptions only where required  
- [x] Signature/HMAC verification before state change  
- [x] Payment event ledger / dedupe  
- [x] markPaid inventory + status history  
- [x] Retry, refund (partial-capable), reconcile command  
- [x] Admin order view: status actions, shipment create, process refund  
- [x] Tests: Checkout, PaymentEvent, PaymentRetry, RefundOperations, Cart, OrderTracking  
- [x] Owner UAT Phase 16: test card pay success, order in account + admin  

### 3.2 Payment — PENDING / residual

| Item | Priority | Notes |
|---|---|---|
| **Live mode keys** on production `.env` | **P0** | Pre-live #1 |
| **Webhook URL** registered in Razorpay dashboard → `https://{domain}/payment/razorpay/webhook` | **P0** | Must be public HTTPS |
| Staging re-verify after each deploy | P0 | Don’t assume UAT host = prod host |
| `RAZORPAY_ALLOW_FAKE_PAYMENTS=false` enforced on prod | **P0** | Already in production.example |
| Mail delivery (order confirmation) on real SMTP | P1 | Phase 9 built; host mail creds |
| Cron/queue drain on host so mails/jobs run | P1 | Phase 14 pattern |
| Settlement/payout ops outside app (Razorpay dashboard) | Ops | Not in-app |
| EMI display / more methods UX polish | P3 | Gateway supports methods Razorpay enables |

---

## 4. SEO lens — product upload impact on findability

| SEO concern | Status | Gap |
|---|---|---|
| Product URL `/product/{slug}` | ✅ | Keep slugs stable after publish |
| Unique title/meta via product + SeoEntry | ✅ pattern | Fill SEO fields on every SKU at upload |
| Product JSON-LD on PDP | ✅ implemented in phase work | Re-verify after theme tweaks |
| Sitemap includes products | ✅ `SitemapController` | Regenerate/cache after large imports |
| Inactive/imported-not-activated not indexed as live | ✅ query bounds | Don’t force-index drafts |
| Image `alt` / compressed media | Partial | Upload discipline + future conversions |
| Thin/duplicate copy from imports | Risk | Human rewrite before activate (activation is technical, not SEO QA) |
| Withheld policy pages | Intentional | No fake shipping/returns SEO until legal OK |
| Canonical shop filters | ✅ | Avoid indexing infinite facet combos (robots/canonical discipline) |

**SEO upload SOP (pending process, not code):**

1. Unique slug + title + meta description at create/activate.  
2. One clear H1 (product name).  
3. Original description (no verbatim competitor paste).  
4. ≥1 gallery image with meaningful alt.  
5. Correct category breadcrumb.  
6. Activate only when price/stock truthful.  
7. After bulk import: ping sitemap / wait for next crawl.

---

## 5. Phase lens — which gates already cover these flows

| Phase | Product upload | Payment |
|---|---|---|
| 0A | — | Safety: server money, replay, inventory |
| 2 | Schema products/variants/media/orders/payments | Same |
| 3–4 | Storefront consume catalogue | Cart/checkout/orders |
| 6 / 6A | Import + expansion + activation | — |
| 7 | Catalogue RBAC / audit on activate | Order/finance separation |
| 8 | — | Events, retry, refunds, reconcile |
| 9 | — | Paid → notifications |
| 10 | — | Fulfillment after paid (defaults careful) |
| 12 | Content boundaries | Core payment/inventory safety |
| 16 | Admin product price edit UAT | **Test** Razorpay full path UAT |
| 17 | Conditional GO | Live keys still open |
| 18 | Deploy catalogue to host | Deploy webhooks/HTTPS |

**Do not re-open phases 0–17 for “build payment/upload”.** Remaining work is **owner ops + launch**, unless a **bug** is found on critical path.

---

## 6. Unified DONE vs PENDING matrix

| ID | Item | State | Owner |
|---|---|---|---|
| PU-01 | Filament product/category/brand CRUD | **DONE** | — |
| PU-02 | Media + variants | **DONE** | — |
| PU-03 | Import + review + activate gates | **DONE** | — |
| PU-04 | Storefront shows active products | **DONE** | — |
| PU-05 | Automated catalogue tests | **DONE** | — |
| PU-06 | Commercial rights for sellable assets | **PENDING** | Owner/legal |
| PU-07 | Production catalogue population | **PENDING** | Owner + deploy |
| PU-08 | SEO field discipline SOP at upload | **PENDING process** | Ops |
| PY-01 | Checkout + server totals + snapshots | **DONE** | — |
| PY-02 | Razorpay test integration + fake fallback | **DONE** | — |
| PY-03 | Callback/webhook crypto + idempotent paid | **DONE** | — |
| PY-04 | Inventory on paid / restore on cancel | **DONE** | — |
| PY-05 | Retry + refunds + reconcile | **DONE** | — |
| PY-06 | Phase 16 test payment UAT | **DONE** | — |
| PY-07 | Live keys + prod webhook | **PENDING** | Owner |
| PY-08 | HTTPS on payment host | **PENDING** | Owner/host |
| PY-09 | Prod fake-pay disabled + mail/cron | **PENDING** | Owner on deploy |
| LV-01 | Site live taking real money | **PENDING** | Phase 18 + 4 blockers |

---

## 7. Recommended execution plan (forward only)

### Track A — Verify on current environment (engineering, short)

1. Smoke admin: create one **manual** test product with image → active → visible on `/shop`.  
2. Smoke import path (if fixtures present): inactive import → activate with stock/media → visible.  
3. Smoke pay: with **test** keys, one checkout; confirm order `paid`, stock decremented, admin shows payment.  
4. Without keys: confirm fake path only where allowed; prod example keeps fake **false**.  
5. Log results in `MEMORY.md` (no phase reopen).

### Track B — Owner launch blockers (business)

1. Clear media/copy rights for SKUs you will sell.  
2. Razorpay live account: keys + webhook + test→live switch plan.  
3. Domain HTTPS.  
4. Legal pages approval if you need them public.  
5. Explicit **Phase 18** deploy command when ready.

### Track C — Optional hardening (not launch-blocking)

1. One-page ops runbook: “Add product” + “Import activate”.  
2. SEO checklist baked into admin helper text (title/meta reminders).  
3. Image conversion pipeline.  
4. Post-deploy payment monitoring (reconcile cron + failed payment alert).

---

## 8. Risk register (CEO)

| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| Go live with fake payments on | Med if misconfigured | Critical | `RAZORPAY_ALLOW_FAKE_PAYMENTS=false`; config check in release checklist |
| Webhook unreachable → paid only via browser callback | Med | High | Register webhook; monitor `payment_events` |
| Activate imports without rights | Med | Legal | Pre-live #3; human review checkboxes already force attestation |
| Oversell | Low (code locks) | High | Keep markPaid + InventoryService path; no bypass admin stock hacks |
| SEO thin content from bulk import | Med | Med | Rewrite before activate; PU-08 SOP |

---

## 9. Answers in one page

**Q: Product upload ho gaya?**  
**A:** Haan — code + admin + import/activation + tests + phase gates. **Live sellable catalogue** abhi owner rights + prod data pe depend karta hai.

**Q: Payment ho gaya?**  
**A:** Haan — poora test-mode engine (checkout→Razorpay→verify→paid→stock→refund/retry). **Live payment** keys/webhook/HTTPS pending.

**Q: Kya naya development phase chahiye?**  
**A:** Nahi for core flows. Sirf **ops verification**, **owner blockers**, aur agar bug mile to **surgical fix**—phases 4/6/8 dubara mat kholo.

**Q: Next best move?**  
1. Track A smoke on your UAT/demo host.  
2. Parallel Track B owner checklist.  
3. Phase 18 only after conditional GO items close.

---

## 10. Code pointer index

| Concern | Path |
|---|---|
| Admin product form | `app/Filament/Resources/ProductResource.php` |
| Activation | `app/Services/ImportedProductActivationService.php` |
| Import | `app/Services/CatalogueImportService.php` |
| Checkout UI | `app/Livewire/CheckoutWizard.php` |
| Orders/paid | `app/Services/OrderService.php` |
| Gateway | `app/Payment/RazorpayGateway.php` |
| HTTP pay | `app/Http/Controllers/RazorpayController.php` |
| Events | `app/Services/PaymentEventService.php` |
| Refunds | `app/Services/RefundService.php` |
| UAT script | `tasks/PHASE_16_OWNER_UAT_SCRIPT.md` |

---

*This plan is verification/planning only. No phase status change. Implementation work starts only on owner command for a chosen track.*
