# PHASES — Always Read (delivery status)

> **One of five always-read files.** Others: `ARCHITECTURE.md` · `RULES.md` · `DESIGN.md` · `MEMORY.md`  
> **Authority:** `tasks/MASTER_PROJECT_TRACKER.md` + `tasks/CANONICAL_PHASE_SEQUENCE.md`  
> This file is the **condensed mirror**. On conflict, tracker wins — then update this mirror.

**Last mirrored:** 2026-09-12

---

## 1. Big picture

| Item | State |
|---|---|
| Delivery phases 0–17 + 6A | **COMPLETE** |
| MVP launch evidence | **COMPLETE** (1 Sep 2026) |
| Phase 17 decision | **CONDITIONAL GO** |
| Phase 18 deployment | **INACTIVE** until explicit owner deploy command |
| Auto Mode | **PAUSED** |
| Live production | **NOT LIVE** — 4 pre-live owner items open |
| Product posture | Short MVP launch track; non-essential enterprise work deferred |

---

## 2. Canonical delivery sequence

| Phase | Scope | Status |
|---|---|---|
| **0** | Repo status audit | COMPLETE |
| **0A** | Critical admin/payment/discount/inventory/refund safety | COMPLETE |
| **0B** | Stack alignment, Filament 5, MySQL 8, cron strategy | COMPLETE |
| **1** | Homepage + Shop design specs | COMPLETE |
| **2** | MySQL schema + domain architecture | COMPLETE |
| **3** | Homepage + Shop frontend qualification | COMPLETE |
| **4** | Accounts, cart, wishlist, checkout, orders | COMPLETE |
| **5** | Reviews, coupons (product Q&A later **removed**) | COMPLETE |
| **6** | Catalogue acquisition/import pipeline | COMPLETE |
| **6A** | Multi-category catalogue + HP/Shop expansion | COMPLETE |
| **7** | Admin RBAC, staff, auditability | COMPLETE |
| **8** | Payment, refunds, financial reconciliation | COMPLETE |
| **9** | Notifications + integration events | COMPLETE |
| **10** | Shipping, fulfillment, returns, India tax framework | COMPLETE *(values disabled by default)* |
| **11** | CX, search, merchandising | COMPLETE |
| **12** | MVP core safety (authz, payment/inventory, content bounds) | COMPLETE |
| **13** | Performance smoke (build, bounded queries, critical pages) | COMPLETE |
| **14** | Min ops: env, backup/restore, queue/cron, logs, rollback | COMPLETE |
| **15** | Shared-host release package + migration runbook | COMPLETE |
| **16** | Owner UAT critical journeys (23/23 PASS) | COMPLETE |
| **17** | Go/no-go evidence review (no deploy) | COMPLETE — **CONDITIONAL GO** |
| **18** | Shared-host deploy, launch, stabilize | **INACTIVE** / owner-guided demo only when commanded |

**E-series** IDs in enterprise roadmap = capability workstreams, **not** delivery phase numbers.

---

## 3. What “COMPLETE” means here

- Implementation + required tests/build/evidence accepted for that phase gate.  
- **Does not** by itself mean production-live or legal/content clearance.  
- Later phases may still keep feature flags **disabled** (returns/tax/legal pages).

---

## 4. Phase 17 — Conditional GO (gates still open)

Live launch still needs owner resolution of:

| # | Pre-live item |
|---|---|
| 1 | **Razorpay live keys** + production webhook endpoint verified |
| 2 | **AS-H011 legal wording** before enabling legally sensitive public content |
| 3 | **Catalogue content/media rights** clearance for commercial use |
| 4 | **Host HTTPS/TLS** verification on real deployment host |

Until then: Razorpay **test mode**, withheld legal pages, returns/tax enablement per owner policy.

---

## 5. Phase 18 rules

- Starts **only** on explicit owner deployment command.  
- Agent 10 style deploy automation stays unused unless owner directs.  
- Demo deploy may use test payments; must not silently flip live money.  
- Follow `docs/release-checklist.md`, `docs/rollback-plan.md`, deploy docs under `docs/DEPLOY_*.md`.

---

## 6. Module snapshot (storefront/commerce)

| Area | Notes |
|---|---|
| Homepage / nav / shop / filters / sort | Built & phase-qualified |
| PDP / cart / wishlist / checkout / orders | Built; server-authoritative money |
| Reviews / Q&A / coupons | Built + moderation/reservation rules |
| Payments / refunds / reconcile | Built; live keys = owner gate |
| Notifications | Built; ops depends on mail/cron |
| Fulfillment / returns / tax | Domain complete; **safe defaults disabled** until approved |
| Admin RBAC / audit / MFA | Built |
| Search / merchandising / stock alerts | Built with bounded behavior; ops scheduling for alerts |

Use tracker § module tables if a residual “PARTIAL” line items matter for a task.

---

## 7. Quality gates (every meaningful change)

1. Targeted or full `php artisan test` as appropriate.  
2. `npm run build` if front assets change.  
3. No new critical authZ/payment/inventory bypass.  
4. MySQL-safe migrations (prod target MySQL 8).  
5. Don’t enable withheld legal/tax/return **values** without owner approval.  
6. **MEMORY v2 STRICT:** fill `docs/MEMORY.md` §A checklist into §B log + verify §C facts **before claiming done** (`RULES.md` G10/AI9).  
7. If phase/launch/Auto Mode changed → update **this file** + `tasks/MASTER_PROJECT_TRACKER.md` in the same sitting.

Historical bar examples (don’t treat as live counts without re-run): Phase 12 era ~396 tests; always re-run locally for current HEAD.

---

## 8. What to work on NOW (default priority)

Unless owner says otherwise:

1. **Launch blockers only** — the 4 pre-live items, deploy prep, bugfixes on critical path.  
2. **Owner-requested features** with explicit scope.  
3. **Docs/AI always-read maintenance** (these five files).  
4. Defer broad enterprise polish, vector search, heavy perf programmes, CI/CD expansion.

**Do not** restart Phases 0–17 from scratch. **Do not** self-start Phase 18.

---

## 9. Auto Mode

| Command / event | Effect |
|---|---|
| `ACTIVATE AUTO MODE` | Allowed autonomous execution per protocol |
| `PAUSE AUTO MODE` / genuine blocker / full phase complete | Pause |
| Phase 18 | Still needs **separate** deploy activation |

Current: **PAUSED**.

---

## 10. Pointers (deep only when needed)

| Need | File |
|---|---|
| Live acceptance detail | `tasks/MASTER_PROJECT_TRACKER.md` |
| Sequence / E-crosswalk | `tasks/CANONICAL_PHASE_SEQUENCE.md` |
| MVP track | `tasks/MVP_LAUNCH_PLAN.md` |
| UAT script | `tasks/PHASE_16_OWNER_UAT_SCRIPT.md` |
| Phase plans/QA | `tasks/PHASE_*`, `tasks/AUTO_MODE_*` |
| Release/rollback | `docs/release-checklist.md`, `docs/rollback-plan.md` |

---

*When you complete or reopen a phase gate, update the tracker first, then refresh §1–2 of this file and MEMORY.*
