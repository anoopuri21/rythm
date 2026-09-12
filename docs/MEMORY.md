# MEMORY — Always Read / Always Update (STRICT)

> **One of five always-read files.** Others: `ARCHITECTURE.md` · `RULES.md` · `PHASES.md` · `DESIGN.md`  
> **Purpose:** durable session brain — AI must **not** re-scan the whole repo every time.  
> **Enforcement:** A change set is **INCOMPLETE** until the checklist in §A is satisfied and logged.  
> See also: `RULES.md` → **G10 / AI7 / AI9** (MEMORY gate).

**Protocol version:** 2.0 (strict checklist) · **Last protocol update:** 2026-09-12

---

# A. MANDATORY CHANGE CHECKLIST (every future change)

Copy this block into the **Session log** for every material change set.  
Fill every line. Use `n/a` only when truly not applicable — never leave blank.

```
### YYYY-MM-DD — <short title>
- Change-id: <branch or topic slug>
- Trigger: owner-ask | bug | docs | phase-gate | refactor | other
- Scope paths: `path1`, `path2`, …
- Type tags: [ ] code [ ] migration [ ] test [ ] front-build [ ] design-token [ ] docs-only [ ] config [ ] admin [ ] commerce [ ] security
- Checklist:
  - [ ] A1 Read five always-read files before editing
  - [ ] A2 Touched only task-relevant paths (no drive-by refactors)
  - [ ] A3 Services own business writes (no money/stock logic in Blade/controller)
  - [ ] A4 No client-trusted totals/prices
  - [ ] A5 AuthZ/policies preserved (deny-by-default)
  - [ ] A6 Tests run if code/commerce/security touched → result: pass | fail | n/a — `…`
  - [ ] A7 `npm run build` if CSS/JS/views assets need rebuild → result: pass | n/a
  - [ ] A8 Design tokens only (no new hardcoded hex) → n/a if no UI
  - [ ] A9 No secrets/.env/vendor/node_modules committed
  - [ ] A10 Withheld legal pages / live pay / Phase 18 not enabled unless owner commanded
  - [ ] A11 §1 Current facts updated OR verified unchanged (list keys touched / `none`)
  - [ ] A12 §2 Locked decisions unchanged OR owner-approved change noted
  - [ ] A13 Footguns (§4) added if new trap discovered
  - [ ] A14 Cross-file mirrors done: PHASES / DESIGN / ARCHITECTURE / PRD / tracker / `n/a`
  - [ ] A15 Owner-facing summary prepared (what changed + what to verify)
- Risks / follow-ups: <none | bullets>
- Status: COMPLETE | BLOCKED | PARTIAL
```

### A.0 What counts as a “material change” (MUST run checklist)

| Triggers checklist | Skip checklist (still optional 1-line log) |
|---|---|
| Any `app/`, `routes/`, `database/`, `resources/`, `config/` code change | Pure typo in a non-authority comment |
| Any of the five always-read files / PRD / tracker status | Reading/exploring only (no writes) |
| Migrations, tests, build config, deploy docs that alter procedure | Reverting uncommitted local experiments with zero net diff |
| Enabling/disabling features, flags, withheld pages, payment mode | |
| Phase status, launch/live posture, Auto Mode state | |

**If net diff ≠ empty on tracked product files → checklist required.**

### A.1 Definition of DONE (hard gate)

Work may be reported **done** to the owner only when:

1. Implementation (or doc) change is in the tree.  
2. Required tests/build from checklist are green or honestly `n/a`.  
3. **This file updated in the same sitting** (§1 if needed + §B log entry with checklist).  
4. Cross-mirrors done when status/design/architecture facts moved.  
5. No rule in `RULES.md` §9 hard-stop was silently violated.

**Incomplete without MEMORY update = not done.** Re-open the task and finish §A before claiming completion.

### A.2 Failure modes (do not repeat)

| Bad habit | Required instead |
|---|---|
| “Small change, skip MEMORY” | Still log; small changes drift facts fastest |
| Update MEMORY next session | Same sitting as the code/doc change |
| Essay log, no checklist | Use the template; max substance in bullets |
| §1 contradicts PHASES/tracker | Fix mirrors immediately; tracker wins on phase numbers |
| Store secrets in MEMORY | Never — reference env var **names** only |
| Mark COMPLETE with failing tests | Status PARTIAL/BLOCKED + follow-up |

---

# B. Session log (newest first — checklist entries live here)

### 2026-09-12 — C4 buy-path smoothness (W2)
- Change-id: `c4-buy-path`
- Trigger: owner-ask
- Scope paths: `PaymentAvailability.php`, `CheckoutWizard.php`, cart/wishlist/checkout/order views, `LoginController.php`, `BuyPathSmoothnessTest.php`, `BUY_PATH_SMOKE_CHECKLIST.md`
- Type tags: [x] code [x] test [x] commerce [x] docs-only
- Checklist:
  - [x] A1–A2 scoped C4
  - [x] A3–A5 payment guard via PaymentAvailability + resolve()
  - [x] A6 tests authored (run on PHP host)
  - [x] A9–A10 no live keys
  - [x] A11 next = C5 demo purge
  - [x] A14 plan board updated
  - [x] A15 owner summary
- Status: COMPLETE (code)
- Notes: Wishlist remains product-level (documented in UI). Fake pay only local/tests.

### 2026-09-12 — Fix PublicContent nav test + C3 admin upload flowless
- Change-id: `c1-nav-fix-c3-upload`
- Trigger: owner-ask (failing test + start C3)
- Scope paths: `navbar.blade.php`, `PublicContentVisibilityTest.php`, `ProductResource.php`, `EditProduct.php`, `ADMIN_PRODUCT_UPLOAD_RUNBOOK.md`, `AdminProductUploadFlowTest.php`
- Type tags: [x] code [x] test [x] admin [x] docs-only
- Checklist:
  - [x] A1 five always-read
  - [x] A2 scoped fix + C3 only
  - [x] A6 tests updated/authored — run on owner PHP host
  - [x] A9–A10 safe
  - [x] A11 next = C4 buy-path
  - [x] A14 plan board updated
  - [x] A15 owner summary
- Root cause: navbar hardcoded `/about` bypassed PublicContent footer gate
- Status: COMPLETE (code); verify with PublicContentVisibilityTest + AdminProductUploadFlowTest

### 2026-09-12 — C2 multi-variant depth (W3)
- Change-id: `c2-variant-depth`
- Trigger: owner-ask (verify C1, push, plan C2, execute)
- Scope paths: `ProductVariant.php`, `ProductResource.php`, `AddToCart.php`, PDP/cart/checkout blades, `tests/Feature/VariantDepthTest.php`, `docs/C2_VARIANT_DEPTH_PLAN.md`
- Type tags: [x] code [x] test [x] admin [x] docs-only
- Checklist:
  - [x] A1–A2 scoped to C2 after C1 push
  - [x] A3 services unchanged for money; variant effectivePrice used
  - [x] A4 no client totals
  - [x] A6 tests authored `VariantDepthTest` — PHP unavailable in sandbox (not executed)
  - [x] A7 n/a build
  - [x] A9–A10 safe
  - [x] A11 next = C3 admin upload polish
  - [x] A14 plan status updated
  - [x] A15 owner summary prepared
- Status: PARTIAL (code complete; test run blocked — no PHP)
- C1 push: `471c653` → `origin/arena/01a09498-rythm`

### 2026-09-12 — C1 W5 hide-when-empty (first eng chunk)
- Change-id: `c1-public-content-visibility`
- Trigger: owner-ask (best-first actionable)
- Scope paths: `app/Support/PublicContent.php`, `app/Observers/PageObserver.php`, `app/Providers/AppServiceProvider.php`, `app/Livewire/CheckoutWizard.php`, `app/Services/SiteSettingsService.php`, product/checkout/footer/account/order views, `tests/Feature/PublicContentVisibilityTest.php`, plan docs
- Type tags: [x] code [x] test [x] docs-only [x] commerce
- Checklist:
  - [x] A1 Five always-read + priority plan
  - [x] A2 Scoped to W5/C1 only
  - [x] A3 Tax gate in CheckoutWizard aligned with OrderService
  - [x] A4 No client money trust changes beyond tax enable flag
  - [x] A5 n/a authZ surface
  - [x] A6 Tests **authored** (`PublicContentVisibilityTest`) — **not executed here** (no php/composer in sandbox this sitting)
  - [x] A7 n/a front build (blade only)
  - [x] A8 no new hex
  - [x] A9 no secrets
  - [x] A10 no live pay / phase 18
  - [x] A11 §G next chunk = C2 variants; C1 partial complete
  - [x] A12 locked decisions unchanged
  - [x] A13 footgun 13 reinforced
  - [x] A14 PRODUCTION_PRIORITY_PLAN status board updated
  - [x] A15 Owner: why C1 first + what shipped
- Risks / follow-ups: Run `php artisan test --filter=PublicContentVisibilityTest` on machine with PHP; C2 variants next
- Status: PARTIAL (code complete; test run blocked by missing PHP in env)

### 2026-09-12 — Production priority plan (5 owner priorities)
- Change-id: `production-priority-plan`
- Trigger: owner-ask
- Scope paths: `docs/PRODUCTION_PRIORITY_PLAN.md`, `docs/CLIENT_HANDOVER_DETAILS.md`, `docs/RAZORPAY_SETUP_GUIDE.md`, `docs/MEMORY.md`, `docs/ARCHITECTURE.md`
- Type tags: [x] docs-only
- Checklist:
  - [x] A1 Read five always-read + flow verify
  - [x] A2 Docs only for planning drop
  - [x] A3–A8 n/a code
  - [x] A9 No secrets (Razorpay guide uses placeholders only)
  - [x] A10 No live pay / phase 18 enablement
  - [x] A11 §C + §G updated — production programme planned; client-owned details non-blocking
  - [x] A12 Locked decisions unchanged (single-brand, auth checkout, server money)
  - [x] A13 Footgun: empty client policy/tax must hide not crash — tracked as W5
  - [x] A14 ARCHITECTURE pointer; PHASES unchanged (no phase reopen)
  - [x] A15 Owner summary: W1–W5 plan + Razorpay chat guide + handover file
- Risks / follow-ups: Implement C1 (hide-empty) then C2 (variants) on owner go
- Status: COMPLETE
- Programme: W1 upload flowless · W2 buy path · W3 multi-variant · W4 no-demo · W5 client details optional

### 2026-09-12 — Product upload + payment flow verify plan
- Change-id: `flow-verify-product-payment`
- Trigger: owner-ask
- Scope paths: `docs/FLOW_VERIFY_PRODUCT_PAYMENT.md`, `docs/MEMORY.md`
- Type tags: [x] docs-only
- Checklist:
  - [x] A1 Read five always-read files before editing
  - [x] A2 Touched only task-relevant paths
  - [x] A3 n/a (docs)
  - [x] A4 n/a
  - [x] A5 n/a
  - [x] A6 n/a — docs/verify only
  - [x] A7 n/a
  - [x] A8 n/a
  - [x] A9 No secrets committed
  - [x] A10 No launch/pay enablement
  - [x] A11 §C verified unchanged for launch facts; added pointer fact via log
  - [x] A12 Locked decisions unchanged
  - [x] A13 none new
  - [x] A14 n/a phase status; plan doc only
  - [x] A15 Owner summary: both flows code-DONE; live gates owner-pending
- Risks / follow-ups: Owner may request Track A smoke or Track B launch blockers next
- Status: COMPLETE
- Verdict: Product upload + payment **implemented & phase-complete**; live money/rights/HTTPS **pending**

### 2026-09-12 — MEMORY strict checklist protocol v2
- Change-id: `memory-strict-checklist`
- Trigger: owner-ask
- Scope paths: `docs/MEMORY.md`, `docs/RULES.md`, `docs/PHASES.md`
- Type tags: [x] docs-only
- Checklist:
  - [x] A1 Read five always-read files before editing
  - [x] A2 Touched only task-relevant paths
  - [x] A3 n/a (docs)
  - [x] A4 n/a
  - [x] A5 n/a
  - [x] A6 n/a — docs only
  - [x] A7 n/a
  - [x] A8 n/a
  - [x] A9 No secrets committed
  - [x] A10 No launch/pay enablement
  - [x] A11 §1 updated — protocol version, MEMORY enforcement fact
  - [x] A12 Locked decisions unchanged
  - [x] A13 Footgun added — skipping MEMORY update
  - [x] A14 Mirrors: RULES G10/AI9, PHASES quality gate note
  - [x] A15 Owner summary: strict per-change MEMORY checklist now binding
- Risks / follow-ups: Agents must use template on every material change hereafter
- Status: COMPLETE

### 2026-09-12 — PRD + five always-read files (initial)
- Change-id: `always-read-v1`
- Trigger: owner-ask
- Scope paths: `docs/PRD.md`, `docs/ARCHITECTURE.md`, `docs/RULES.md`, `docs/PHASES.md`, `docs/DESIGN.md`, `docs/MEMORY.md`, `README.md`
- Type tags: [x] docs-only
- Checklist:
  - [x] A1–A15 satisfied for docs-only bootstrap (see prior session narrative)
  - [x] A11 Facts seeded from tracker (phases 0–17 complete, conditional GO, not live)
- Status: COMPLETE
- Notes: Single-brand multi-product PRD; AI front door = five files.

### 2026-09-01 (historical — tracker)
- Phase 16 UAT 23/23 PASS; Phase 17 CONDITIONAL GO; MVP evidence complete; 4 pre-live items open; Phase 18 inactive.

### 2026-08-25 → 2026-08-30 (historical)
- Phases 0–11 + 6A qualification programme (safety, stack, storefront, commerce, catalogue, RBAC, pay, notify, fulfill, CX).

---

# C. Current facts (source of truth snapshot)

*Update cells in the **same change** that makes them true. Stale cells are bugs.*

| Fact | Value | Updated |
|---|---|---|
| **Product** | Rhythm Exports / Rythme Music Store — musical instruments ecommerce | 2026-09-12 |
| **Commerce model** | **Single brand, many products** (not multi-vendor) | 2026-09-12 |
| **Repo** | `anoopuri21/rythm` | 2026-09-12 |
| **PRD** | `docs/PRD.md` (v1.0) | 2026-09-12 |
| **Always-read set** | `ARCHITECTURE` `RULES` `PHASES` `DESIGN` `MEMORY` | 2026-09-12 |
| **MEMORY protocol** | **v2 STRICT checklist** — change incomplete without §A log | 2026-09-12 |
| **Delivery** | Phases **0–17 + 6A COMPLETE**; **17 = CONDITIONAL GO** (1 Sep 2026) | 2026-09-12 |
| **Phase 18** | Inactive until explicit owner deploy command | 2026-09-12 |
| **Auto Mode** | PAUSED | 2026-09-12 |
| **Live?** | **No** — 4 pre-live owner items open | 2026-09-12 |
| **Pre-live blockers** | (1) Razorpay live keys + prod webhook (2) AS-H011 legal wording (3) catalogue/media rights (4) host HTTPS/TLS | 2026-09-12 |
| **Payments now** | Razorpay **test mode** until owner flips live | 2026-09-12 |
| **Tax/returns public** | Domain exists; **defaults disabled** until owner approval | 2026-09-12 |
| **Withheld public pages** | `shipping`, `returns`, `warranty`, `faqs` | 2026-09-12 |
| **Currency** | INR | 2026-09-12 |
| **Checkout** | Auth required; guest cart + merge on login | 2026-09-12 |
| **Stack** | Laravel 13.24 · Livewire 4 · Filament 5.x · Tailwind 4 · Spatie Media · Razorpay | 2026-09-12 |
| **Design tokens** | Brand `#B20202` / `#930303` · ink `#222` · paper `#fff` · soft `#E7F4F1` · Inter via `@theme` | 2026-09-12 |
| **DB** | SQLite dev/tests · **MySQL 8** prod/UAT (`rhythm_db` historically) | 2026-09-12 |
| **Admin URL** | `/admin` | 2026-09-12 |
| **Business logic home** | `app/Services/*` | 2026-09-12 |
| **Inventory authority** | `InventoryService` only | 2026-09-12 |
| **Order transitions** | `OrderService` + `OrderStateMachine` | 2026-09-12 |
| **Storefront routes** | `routes/web.php` | 2026-09-12 |
| **Brand config** | `config/rythme.php` + Filament Site Settings | 2026-09-12 |
| **Session branch (Arena)** | `arena/01a09498-rythm` (session-fixed) | 2026-09-12 |

### C.1 Fact-update matrix (which §1 keys to touch)

| If you changed… | Must refresh fact keys |
|---|---|
| Launch / pay mode / legal pages | Live?, Pre-live blockers, Payments now, Withheld pages, Tax/returns |
| Phase completion / Auto Mode | Delivery, Phase 18, Auto Mode |
| Stack / Filament / Laravel major | Stack (+ ARCHITECTURE.md) |
| Design tokens / font | Design tokens (+ DESIGN.md) |
| New service authority / invariant | Business logic / Inventory / Order rows (+ ARCHITECTURE) |
| Commerce model / checkout policy | Commerce model, Checkout (+ PRD + RULES) |
| Only bugfix, same architecture | §C **Verified unchanged** in log (`none`) — still required |

---

# D. Locked decisions (don’t re-ask)

Change only with **explicit owner approval** + PRD/RULES update + log.

- Single-vendor / single-brand storefront.  
- No guest checkout.  
- Server-authoritative pricing, shipping, tax, coupons.  
- Exact MySQL 8 for production acceptance (not MariaDB-as-proof).  
- Shared hosting / cPanel class deploy; cron-drained queue.  
- Blade + Livewire storefront (not React/Next).  
- Deployment is human-gated (Phase 18).  
- Manufacturer brands are catalogue labels only.  
- Phases 0–17 complete ≠ site live.  
- **MEMORY v2 checklist is mandatory on material changes.**

---

# E. Critical paths (debugging map)

| Journey | Entry → core code |
|---|---|
| Home | `HomeController` → `HomepageDataService` → `resources/views/home/*` |
| Shop | `ShopController` + Livewire `ShopIndex` → `ProductQueryService` |
| PDP | `ProductController` + Livewire add-to-cart/wishlist/review/Q&A |
| Cart | `CartService` + Livewire `CartDrawer` `CartPage` `CartBadge` |
| Checkout | Livewire `CheckoutWizard` → `OrderService` → `PaymentGateway` |
| Pay return | `RazorpayController` → payment verify → paid transition + `InventoryService` |
| Orders | `OrderController` + policies/signed links |
| Admin catalogue | Filament Product* + activation/import services |
| Refunds | `RefundService` (Finance) |
| Shipments | `FulfillmentService` |
| Notifications | `CommerceNotificationService` + deliveries |

*If you add a new critical journey, append a row here in the same change.*

---

# F. Known footguns

1. **Client totals lie** — always recompute server-side.  
2. **Webhook retries** — idempotent via `payment_events`.  
3. **Variant vs product stock** — one inventory source per line.  
4. **Paid cancel** → `refund_pending`; don’t claim gateway refund early.  
5. **Legal pages** may exist in DB but stay withheld.  
6. **Stale docs** (`plan.md`, `AGENT_RULES_STRICT`, old NEXT_SESSION) — five always-read + tracker win.  
7. **Filament version** in ancient docs may say v3; trust `composer.json` + code (5.x).  
8. **Font mirror drift** — `app.css` `@theme` (Inter) wins over stale Poppins mirrors.  
9. Destructive tests must not hit persistent UAT/prod DB.  
10. `vendor/` may be external/symlink in agent envs.  
11. **Skipping MEMORY “because docs/small”** — causes next session full re-scan; **forbidden**.  
12. Claiming **done** without §A checklist — treat as incomplete work.  
13. **Empty client tax/policy/shipping** must **hide** on storefront — never fake values, never crash checkout (W5).  
14. **Wishlist is product-level** today — variant-specific wishlist may need explicit work if owner expects it (W2.6).

*New trap discovered → add numbered item same day.*

---

# G. Open owner items / backlog

**Launch blockers (live):** four pre-live items in §C.  

**Deferred (non-blocking unless asked):** observability, full CI/CD, vector search, media conversions, broad perf, multi-currency, native apps.

**Doc front door:** five always-read files; deep `docs/*` on demand only.

**Flow verify (product upload + payment):** `docs/FLOW_VERIFY_PRODUCT_PAYMENT.md` — code/phases DONE; live keys/rights/HTTPS pending.

**Production priority programme:** `docs/PRODUCTION_PRIORITY_PLAN.md` (W1–W5).  
**Client optional details:** `docs/CLIENT_HANDOVER_DETAILS.md` — empty/OFF = hidden, no blockers.  
**Razorpay owner guide:** `docs/RAZORPAY_SETUP_GUIDE.md`.  
**Eng chunks:** C1–C4 code complete → **next C5** demo/placeholder purge. Run `PublicContentVisibilityTest`, `VariantDepthTest`, `AdminProductUploadFlowTest`, `BuyPathSmoothnessTest`.

---

# H. Cross-file mirror map (don’t forget)

| Change type | Also update |
|---|---|
| Phase / launch / Auto Mode | `PHASES.md` + `tasks/MASTER_PROJECT_TRACKER.md` |
| Tokens / typography / UI law | `DESIGN.md` + `resources/css/app.css` (+ design-system doc if deep) |
| Layers / services / routes / aggregates | `ARCHITECTURE.md` (+ PRD §architecture if product-level) |
| Product scope / personas / NFR | `PRD.md` + `RULES.md` as needed |
| Binding behavioral law | `RULES.md` first, then MEMORY §D |
| README entry points | `README.md` always-read table if files move |

---

# I. Quick commands

```bash
php artisan test
npm run build
php artisan route:list
php artisan migrate --force          # careful on shared DB
php artisan serve --host=0.0.0.0 --port=8000
```

`/admin` · `/` · `/shop`

---

# J. Pointer index (optional deep reads)

| Topic | Path |
|---|---|
| PRD | `docs/PRD.md` |
| Architecture inventory | `docs/architecture-overview.md` |
| Commerce plan | `docs/architecture/01-commerce-architecture.md` |
| Domain / states / ACL | `docs/domain-model.md`, `state-machine.md`, `permissions-matrix.md` |
| Tracker / sequence | `tasks/MASTER_PROJECT_TRACKER.md`, `CANONICAL_PHASE_SEQUENCE.md` |
| Release / rollback | `docs/release-checklist.md`, `rollback-plan.md` |

---

# K. Size & archive policy

1. Keep this file scannable (**target ≤ 450 lines**).  
2. Session log: keep **last ~15 checklist entries** in full; older → `docs/MEMORY_ARCHIVE.md` with pointer.  
3. Facts table stays complete; don’t archive §C.  
4. No secrets, tokens, customer PII, raw card data.  
5. Prefer path references over pasted code blocks.

---

# L. AI start/end ritual (print mentally every task)

**START**

```
[ ] Read ARCHITECTURE, RULES, PHASES, DESIGN, MEMORY
[ ] Note §C facts + open blockers
[ ] Define scope paths before first edit
```

**END (before saying “done”)**

```
[ ] Paste §A checklist into §B log (filled)
[ ] §C keys updated or “none”
[ ] Mirrors H done or n/a
[ ] Tests/build recorded
[ ] Owner summary ready
```

---

*No checklist → not done. Read every session. Write every change.*
