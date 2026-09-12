# Production priority plan — Upload · Buy path · Variants · No-demo · Client-owned details

**Date:** 2026-09-12  
**Posture:** Production-ready prospect — no placeholder/demo left as “good enough”  
**Model:** Single-brand store; client fills policy/tax/contact in admin when ready  
**Related:** `FLOW_VERIFY_PRODUCT_PAYMENT.md` · `CLIENT_HANDOVER_DETAILS.md` · five always-read files  

---

## 0. Owner priorities → workstreams

| # | Owner priority | Workstream ID | Intent |
|---|---|---|---|
| 1 | Admin product upload **flowless** | **W1** | Fast, clear, no dead-ends to publish SKUs |
| 2 | Wishlist/cart → **final payment** smooth + Razorpay setup guide | **W2** | Zero-friction buy path; owner can configure pay |
| 3 | **Multi-variant** own price/stock/images/color/specs | **W3** | True multi-SKU product ops + storefront |
| 4 | **Production-ready** — no placeholder/demo | **W4** | Strip demo content; truthful empty states |
| 5 | Policy/tax/etc. **client-filled**; empty = hidden; no bugs | **W5** | Optional fields; never block checkout/render |

**Rule:** Missing client legal/tax/policy data is **not** an engineering blocker and **must not** throw errors or show fake claims.

---

## 1. Current baseline (honest)

| Area | Already strong | Gap vs this priority list |
|---|---|---|
| Product CRUD + media + basic variants | Filament ProductResource | Variant **color/options/attributes** admin UX incomplete; upload UX polish |
| Import → activate | Services + gates | Day-to-day manual path should feel primary & flowless |
| Cart / wishlist / checkout / pay engine | Phases 4/8/16 | Smoothness QA + empty-key messaging; live keys = owner |
| Variant model | `options` JSON, attr pivot, `variant_gallery`, effectivePrice | Admin form lacks structured color/specs; PDP gallery may not swap per variant fully |
| Settings tax/shipping/returns toggles | Filament Settings + defaults off | Need full **hide-when-empty** audit on storefront chrome |
| Withheld pages | 404 if in config list | Client handover file + admin Pages path clarity |
| Demo/seed content | Seeders exist for dev | Prod posture: no demo SKUs/copy as default live content |

---

## 2. Definition of DONE (programme)

Programme is done when:

1. **W1** Staff can create category → brand → product → variants → media → active in one clear admin path under 5 minutes for a simple SKU.  
2. **W2** Guest add-to-cart → login → wishlist optional → checkout → Razorpay test pay → success/order with no dead UI; Razorpay guide followed once by owner.  
3. **W3** One product with ≥2 variants can each have distinct price, stock, images, color, and key specs; cart/checkout charge correct variant; stock per variant.  
4. **W4** Storefront/admin have no lorem/fake trust/demo-only blockers; empty catalogue shows clean empty state.  
5. **W5** Tax/shipping/returns/policy/contact/social: if unset or disabled, **not shown** and **no exception**; checkout still completes with ₹0 shipping/tax when configured so.  
6. MEMORY §A logged per shipped chunk; tests green for touched commerce paths.

---

## 3. Workstream breakdown

### W1 — Admin product upload (flowless)

**Goal:** Management path feels continuous: list → create → media → variants → SEO → save → visible on shop.

| Task | Priority | Type | Notes |
|---|---|---|---|
| W1.1 Admin upload runbook (screens + field meanings) | P0 | docs | For client + staff; Hindi/English short steps |
| W1.2 Product form UX pass: required vs optional labels, tab order Details→Variants→Media→SEO | P0 | admin UI | Reduce scroll confusion |
| W1.3 Category & Brand quick-create from product form (if missing) | P1 | admin UI | Avoid leaving product form mid-flow |
| W1.4 Bulk image tips + max size/error messages clear | P1 | admin UI | Flowless = failures explain next action |
| W1.5 List filters: active, OOS, no-image, has-variants | P1 | admin UI | Ops finds broken SKUs fast |
| W1.6 “Preview on storefront” link from Edit | P1 | admin UI | Confirm publish without guessing slug |
| W1.7 Import path secondary: document when to use Activate vs manual | P2 | docs | Manual is default for client |

**Exit criteria:** New staff member publishes 3 products using only admin + runbook, no engineer.

---

### W2 — Wishlist / cart → payment smoothness + Razorpay

**Goal:** Buy path never confuses; payment configurable by client.

| Task | Priority | Type | Notes |
|---|---|---|---|
| W2.1 Critical-path smoke script (cart→pay) as owner checklist | P0 | docs/QA | Extend Phase 16 style, production posture |
| W2.2 Empty cart / empty wishlist / OOS / login wall copy — production tone | P0 | storefront | No “demo” language |
| W2.3 Checkout: shipping/tax lines **only if** enabled & >0 or clearly “Included/Free” per settings | P0 | storefront + W5 | |
| W2.4 Razorpay not configured: safe message + no fake success in prod (`ALLOW_FAKE=false`) | P0 | commerce | Dev may fake; prod must not |
| W2.5 Cart line shows variant name/options; price matches variant | P0 | storefront | Ties to W3 |
| W2.6 Wishlist → move to cart keeps variant if stored (or product-only if model is product-level) | P1 | verify/fix | Confirm model: wishlist is product-level today |
| W2.7 Payment failure + retry UX copy | P1 | storefront | |
| W2.8 Razorpay setup — owner guide (also in chat / `docs/RAZORPAY_SETUP_GUIDE.md`) | P0 | docs | Test then live |
| W2.9 Webhook + callback URL checklist on deploy host | P0 | ops | |

**Exit criteria:** One test payment on staging with real test keys; zero console errors; order paid in admin.

---

### W3 — Multi-variant depth (price, stock, images, color, specs)

**Goal:** Variants are first-class sellable rows.

| Task | Priority | Type | Notes |
|---|---|---|---|
| W3.1 Admin variant fields: name, sku, price_override, stock, is_active, images (**done** base) | — | exists | |
| W3.2 Admin **color** UX: structured option (hex and/or attribute Color) on variant | P0 | admin + model use | `options` JSON and/or attributeValues |
| W3.3 Admin **specs/options** editor (e.g. finish, size, scale) → `options` JSON or attributes | P0 | admin | |
| W3.4 Attach product-level attributes/specs for PDP “Specs” tab | P1 | admin | Attribute tables exist; Filament attach may be missing |
| W3.5 PDP: variant switch updates **price, stock, availability, images** | P0 | Livewire/PDP | Gallery swap if variant has images else product gallery |
| W3.6 Cart/checkout/inventory use selected variant stock & price only | P0 | verify | Mostly done — regression tests |
| W3.7 OOS variant hidden or disabled; product OOS if all variants OOS | P0 | storefront | |
| W3.8 Admin validation: unique SKUs; price ≥ 0; stock int; color format | P1 | admin | |
| W3.9 Feature tests: two variants different price → cart line correct | P0 | test | |

**Exit criteria:** Guitar “Sunburst” vs “Black” different ₹, stock, image, color swatch; pay captures correct line.

---

### W4 — Production-ready / no placeholder / no demo

**Goal:** What ships looks like a real single-brand store waiting for **client’s** catalogue—not a theme demo.

| Task | Priority | Type | Notes |
|---|---|---|---|
| W4.1 Audit pass: lorem, fake testimonials, “demo”, TODO visible strings | P0 | content | Fix or remove |
| W4.2 Homepage blocks: empty sections **collapse/hide** when no products/content | P0 | storefront | No empty carousels of ghosts |
| W4.3 Seed data policy: local-only; production migrate **without** demo catalogue (or explicit `--seed` never on prod) | P0 | ops/docs | |
| W4.4 Replace Bajaao-helper admin copy that sounds temporary | P1 | admin copy | Neutral “product images” language |
| W4.5 Trust badges / EMI / free shipping text only if settings/policies justify | P0 | W5 overlap | |
| W4.6 Error pages 404/500 production tone | P1 | views | |
| W4.7 Remove or gate any “sample” Razorpay success without keys in production | P0 | W2 | |

**Exit criteria:** Fresh prod DB + client products only; no demo SKU; no lorem on home.

---

### W5 — Client-owned details (policy, tax, contact) — no blockers

**Goal:** Client fills admin when ready. Empty/disabled ⇒ hidden. Site never crashes.

| Task | Priority | Type | Notes |
|---|---|---|---|
| W5.1 **Client handover file** listing every field they may fill | P0 | docs | `CLIENT_HANDOVER_DETAILS.md` |
| W5.2 Storefront audit: contact phone/email/address/social — render only if non-empty | P0 | views | |
| W5.3 Shipping fee line: hide or “Free” when flat 0 and free_above logic | P0 | checkout/cart | |
| W5.4 Tax line: only if `tax_rules_enabled` and rate meaningful | P0 | checkout | Already partly gated in OrderService |
| W5.5 Returns CTA/links: only if `returns_enabled` | P0 | account/order | Partially gated |
| W5.6 Policy nav links (shipping/returns/warranty/faqs): hide if withheld or page inactive/empty | P0 | layout | Don’t 404 from footer clicks |
| W5.7 CMS Page empty body: don’t publish broken page | P1 | cms | |
| W5.8 No hard-coded fake GST% or “7-day return” in blades | P0 | content audit | |
| W5.9 Checkout remains completable with all optional settings at default zero/off | P0 | test | **Critical acceptance** |
| W5.10 Admin Settings labels: “Optional — leave blank until approved” | P1 | admin | |

**Exit criteria:** Automated test: defaults-off checkout still places order; footer has no dead policy links; tax/shipping rows absent when off.

---

## 4. Suggested delivery sequence (chunks)

Do **not** reopen Phases 0–17. Ship as **production hardening chunks** on current branch.

| Chunk | Scope | Depends | Est. focus |
|---|---|---|---|
| **C0** | Docs lock: this plan + client handover + Razorpay guide | — | Done with this delivery |
| **C1** | W5 hide-when-empty + checkout defaults-off test | C0 | Highest safety for “no blocker” |
| **C2** | W3 variant admin (color/options) + PDP swap + tests | C0 | Core catalogue power |
| **C3** | W1 admin flowless polish (preview, filters, labels) | C2 | Ops speed |
| **C4** | W2 buy-path copy/QA + fake-pay prod guard | C1 | Conversion path |
| **C5** | W4 demo/placeholder purge + homepage empty sections | C1–C4 | Launch look |
| **C6** | Owner Razorpay test setup (guide) + optional staging smoke | C4 | Owner |
| **C7** | Live keys / deploy only on explicit Phase 18 command | C6 + pre-live | Owner |

Parallel OK: C1 ∥ early C2 docs; C3 after C2 solid.

---

## 5. Acceptance tests (must pass before “production prospect”)

### Automated / feature

- [ ] Checkout with tax off, shipping 0, returns off → order created.  
- [ ] Product with 2 variants → add each → different `unit_price` / stock paths.  
- [ ] markPaid decrements **variant** stock when variant line.  
- [ ] PageController withheld slugs 404; footer does not link them when withheld.  
- [ ] Settings social/contact empty → no empty `<a href="">`.  

### Manual

- [ ] Admin create product + 2 colored variants + images → shop → PDP swatches.  
- [ ] Wishlist → cart → checkout → Razorpay **test** pay.  
- [ ] Fail payment once → retry.  
- [ ] Admin order shows variant name on line items.  

---

## 6. Explicit non-goals (this programme)

- Multi-vendor marketplace  
- Guest checkout  
- Inventing legal policy text for the client  
- Enabling live Razorpay without owner  
- Rebuilding homepage cinematic system from scratch  
- Vector search / heavy re-platform  

---

## 7. Status board (live this doc as you ship)

| Workstream | Status | Notes |
|---|---|---|
| W1 Upload flowless | **C3 COMPLETE (code)** | Preview, filters, category/brand quick-create, runbook |
| W2 Buy path + Razorpay guide | **C4 COMPLETE (code)** | PaymentAvailability; empty states; guest checkout CTA; smoke checklist |
| W3 Multi-variant depth | **C2 COMPLETE (code)** | Admin color/specs; PDP gallery swap; `VariantDepthTest` |
| W4 No demo/placeholder | **C5 COMPLETE (code)** | Recent-buy demo purged; contact seed empty; seed policy doc |
| W5 Client details / hide empty | **C1 COMPLETE** | `PublicContent`; footer+**navbar** gated; tax gated |
| C0 Docs | **COMPLETE** | Plan + handover + Razorpay guide |
| C1 Hide-when-empty | **COMPLETE** | Navbar about fix 2026-09-12 |
| C2 Variant depth | **COMPLETE (code)** | `docs/C2_VARIANT_DEPTH_PLAN.md` |
| C3 Admin upload flowless | **COMPLETE (code)** | `docs/ADMIN_PRODUCT_UPLOAD_RUNBOOK.md` |
| C4 Buy-path smoothness | **COMPLETE (code)** | `docs/BUY_PATH_SMOKE_CHECKLIST.md` · `PaymentAvailability` |
| C5 Demo/placeholder purge | **COMPLETE (code)** | `docs/SEED_DATA_POLICY.md` · `DemoContentPurgeTest` |

Update this table when chunks merge; mirror summary in `MEMORY.md`.

---

## 8. File outputs for this planning drop

| File | Purpose |
|---|---|
| `docs/PRODUCTION_PRIORITY_PLAN.md` | This plan |
| `docs/CLIENT_HANDOVER_DETAILS.md` | What client fills later (no eng blocker) |
| `docs/RAZORPAY_SETUP_GUIDE.md` | Step-by-step payment setup |

---

*Engineering starts on owner “go” for chunk C1 (recommended first) or C2 if catalogue variants are the immediate pain.*
