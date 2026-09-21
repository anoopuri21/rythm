# RULES — Always Read (binding)

> **One of five always-read files.** Others: `ARCHITECTURE.md` · `PHASES.md` · `DESIGN.md` · `MEMORY.md`  
> Violating these wastes tokens and breaks production safety.

---

## 0. Session bootstrap (AI — do this first)

1. Read **only** these five: `ARCHITECTURE` · `RULES` · `PHASES` · `DESIGN` · `MEMORY`.  
2. Read `PRD.md` only for product-scope questions.  
3. Open deep docs / whole-repo scan **only** when a task truly needs them.  
4. After **every material change** → complete **`MEMORY.md` §A checklist** in the same sitting (see G10 / AI9).  
5. Do not re-derive stack, brand model, or phase status from scratch every turn.  
6. **Do not tell the owner “done”** until MEMORY §A Definition of DONE is satisfied.

---

## 1. Product rules

| # | Rule |
|---|---|
| P1 | **Single-brand store** — Rhythm Exports / Rythme is the only seller. Many products; manufacturer `Brand` ≠ store seller. |
| P2 | **Not a marketplace** — no vendor onboarding, multi-store, or commission split unless PRD explicitly changes. |
| P3 | **INR only.** |
| P4 | **Checkout requires login.** Guest may browse + cart; cart merges on login. |
| P5 | **Truthful UX** — never claim shipping/returns/warranty/refund-complete/stock-alert delivery the system cannot prove. |
| P6 | **Withheld pages** stay unpublished until owner legal approval (`shipping`, `returns`, `warranty`, `faqs`). |
| P7 | Copy must be original (Bajaao = inspiration only). |

---

## 2. Tech stack rules

| # | Rule |
|---|---|
| T1 | Laravel **13.24** · PHP **8.3+** · Blade + Livewire + Tailwind 4 · Filament admin. |
| T2 | **No** Next.js / React / Vue / jQuery as primary UI. |
| T3 | Business writes go through **`app/Services/*`**, not controllers/Blade/Livewire guts. |
| T4 | Payments only via **`PaymentGateway`** interface (Razorpay prod/test, Fake in tests). |
| T5 | Stock only via **`InventoryService`** + movement ledger. |
| T6 | Order transitions only via **`OrderService` + `OrderStateMachine`**. |
| T7 | Server recomputes money/stock; **ignore client price/total/discount fields**. |
| T8 | Prod DB evidence = **exact MySQL 8.x**. SQLite success ≠ production proof. |
| T9 | Shared-hosting safe: no assumed forever-on queue worker; use scheduler drain pattern. |
| T10 | Secrets in `.env` only — never commit keys, dumps, or real PII. |

---

## 3. Code & architecture rules

| # | Rule |
|---|---|
| C1 | Thin HTTP layer → services → models. No multi-aggregate orchestration in models. |
| C2 | **Snapshots** on order items + addresses; don’t live-join mutable catalogue/address book for history. |
| C3 | Keep **order status ⊥ payment status ⊥ shipment status**. |
| C4 | Idempotency on checkout, payment events, inventory, refunds. |
| C5 | Transactions + row locks on checkout, capture, refund, fulfillment. |
| C6 | Form Requests + throttles on mutating routes. |
| C7 | Policies + Filament strict authZ — **deny by default**. Re-authorize inside sensitive service actions. |
| C8 | Finance role ≠ order fulfillment; Support ≠ catalogue/order/finance mutate. |
| C9 | Soft-delete products for history; prefer deactivation for unpublish. |
| C10 | Imported products: no activation without provenance review, approved local media, verified stock. |
| C11 | Prefer existing Livewire components for cart/wishlist/shop/checkout reactivity. |
| C12 | No ad-hoc hex colors in new views — **design tokens only** (`DESIGN.md`). |
| C13 | Touch targets ≥ 44×44px; one `h1` per page; respect `prefers-reduced-motion`. |

---

## 4. Admin (Filament) rules

| # | Rule |
|---|---|
| A1 | Panel path `/admin`; customers never access it. |
| A2 | Title/name fields → text inputs; long body → rich editor where project standard applies; images → Spatie MediaLibrary. |
| A3 | Orders are checkout-created — don’t invent admin “create order” that bypasses commerce invariants. |
| A4 | Refunds = Finance (or Super Admin). |
| A5 | Staff deletion disabled; role/MFA changes audited. |
| A6 | Notification delivery resources are inspection-oriented per role matrix. |

---

## 5. Security rules

| # | Rule |
|---|---|
| S1 | CSRF on storefront POSTs except verified Razorpay callback/webhook endpoints. |
| S2 | Verify Razorpay **signature/HMAC + amount** before any paid transition. |
| S3 | Webhook/callback replays must be safe (payment_events dedupe, first-transition guards). |
| S4 | Throttle auth, checkout, contact, newsletter, payment endpoints. |
| S5 | Signed URLs for checkout success (and designed invoice/order link cases). |
| S6 | Security headers / CSP middleware stay on. |
| S7 | Staff MFA where required; audit sensitive admin changes. |
| S8 | Mass-assignment only via `$fillable` / explicit casts; no `request()->all()` into commerce models. |

---

## 6. Git / workspace / delivery rules

| # | Rule |
|---|---|
| G1 | This Arena session branch is fixed: work on the session branch only; don’t freestyle other branch names. |
| G2 | Don’t commit `vendor/`, `node_modules/`, `.env`, secrets, large dumps, or temporary uploaded evidence binaries. |
| G3 | Prefer small, reviewable commits with clear why. |
| G4 | Run relevant tests after commerce/security changes; `npm run build` if front assets change. |
| G5 | Deployment / live Razorpay / legal page enablement = **explicit owner command only**. |
| G6 | Auto Mode only on exact `ACTIVATE AUTO MODE`; pause on blocker or `PAUSE AUTO MODE`. |
| G7 | Owner is often non-technical — give short numbered copy-safe steps when manual action is unavoidable. |
| G8 | Destructive tests never target persistent UAT/production data. |
| G9 | Do not mark roadmap phases COMPLETE without evidence + tracker update (Agent-0 style gate). |
| G10 | **MEMORY gate (STRICT):** every material change set must include a filled `MEMORY.md` §A checklist log entry + §C fact refresh/verify in the **same sitting**. Net diff without MEMORY update = **incomplete work**. |

---

## 7. Content & media rules

| # | Rule |
|---|---|
| M1 | Product imagery: managed MediaLibrary first; keep license/source discipline. |
| M2 | No hotlinked random copyrighted assets. |
| M3 | SEO: unique title/description, semantic headings, JSON-LD where implemented (Product/FAQ). |
| M4 | Seeds and fixtures ≠ production catalogue rights clearance. |

---

## 8. AI working rules (token savers)

| # | Rule |
|---|---|
| AI1 | **Start from the five always-read files** — not a full-repo crawl. |
| AI2 | Search/read narrowly (one service, one migration, one view) for the task. |
| AI3 | Don’t rewrite working locked flows (cart/checkout/payment invariants) “for style.” |
| AI4 | Don’t expand scope into marketplace, new framework, or Phase 18 deploy unless asked. |
| AI5 | When confused about status → `PHASES.md` + `MEMORY.md`, not ancient `plan.md` / archived agent rules. |
| AI6 | Archived/non-authoritative: `docs/AGENT_RULES_STRICT.md` (legacy), stale NEXT_SESSION guides if they conflict with tracker. |
| AI7 | After finishing work: fill **MEMORY.md §A checklist** into §B log + update/verify §C facts (same sitting). |
| AI8 | Prefer editing existing services/patterns over inventing parallel systems. |
| AI9 | **No silent skips:** “small change”, “docs only”, or “will log later” do **not** waive G10. Docs-only still uses the checklist (`n/a` where appropriate). |
| AI10 | Before final reply claiming completion, mentally run MEMORY §L END ritual; if any box open → finish MEMORY first. |

---

## 9. Hard stop — ask owner before

- Enabling live payments or webhooks in production  
- Publishing legal/policy pages or tax/invoice identity  
- Schema-breaking migrations without rollback plan  
- Changing single-brand → multi-vendor model  
- Deleting commerce/financial data  
- Disabling authZ, signature checks, or inventory ledger  
- Large dependency upgrades (Laravel/Filament major) outside an approved phase  

---

## 10. MEMORY protocol pointer

Full template, triggers, mirrors, and Definition of DONE: **`docs/MEMORY.md` §A–§L (protocol v2)**.

---

*If a rule here conflicts with an old doc, **this file + PHASES + MEMORY win**, then PRD, then deep architecture docs.*
