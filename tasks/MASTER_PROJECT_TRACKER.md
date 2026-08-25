# Rythme Enterprise E-commerce — Master Project Tracker

**Owner:** Agent 0 — Project Lead  
**Established:** 25 August 2026  
**Repository strategy:** Audit and qualify the existing repository  
**Current phase:** Phase 3 — Pixel-accurate Homepage + Shop frontend BLOCKED on populated live-preview captures
**Overall status:** PHASE 3 CHUNKS 1–4 PASSED / CHUNK 5 REMEDIATION AND EMPTY-STATE REVIEW GREEN / POPULATED 1440/390 EVIDENCE PENDING / NOT PRODUCTION-READY
**Audit report:** `tasks/PHASE_0_STATUS_AUDIT.md`  
**Auto Mode:** PAUSED — genuine Phase 3 isolated populated-preview capture blocker

---

## 1. Locked Project Decisions

| Area | Locked decision |
|---|---|
| Product | Bajaao-style single-vendor musical instruments e-commerce platform |
| Brand | Rythme / Rhythm Exports |
| Backend | PHP Laravel 13.24.0 |
| Admin | Latest Filament version compatible with Laravel 13.24.0 |
| Production database | Exact MySQL 8.x via direct environment-configured connection; phpMyAdmin/HeidiSQL are administration clients only; no Docker/Podman dependency |
| Frontend | Blade templates + custom CSS + Vanilla JS; Livewire approved for reactive commerce flows |
| Hosting target | Shared hosting/cPanel constraints; deployment work remains inactive until explicitly requested |
| Homepage reference | xstore.8theme.com/elementor3/electronic-mega-market/ |
| Shop reference | xstore.8theme.com/elementor3/electronic-mega-market/shop/ |
| Product reference source | bajaao.com catalog, subject to legal/content ownership review before commercial launch |
| Completion authority | Only Agent 0 may mark a module complete after specialist output and QA evidence |
| Composer vendor storage | Physical `vendor` directory is forbidden inside `/home/user/rythm`; dependencies must live outside the workspace, with only a symlink allowed |
| Session vendor path | `/tmp/rythm-vendor`; Composer commands must run from `/home/user/rythm` so project autoload paths remain correct |
| Auto Mode activation | Only exact user command `ACTIVATE AUTO MODE`; registered protocol is not itself activation |
| Auto Mode pause | `PAUSE AUTO MODE`, genuine blocker, or full roadmap phase completion |
| Active integration branch | `rhythm-uat` until the owner requests another branch or Agent 0 records a justified branch change |
| Code-task completion | After applicable local tests pass, commit and push the latest code to the active branch and provide a commit report; push/auth failures must be reported, never concealed |
| Owner assistance standard | Assume the owner is non-technical: minimize manual work and provide every unavoidable manual action as short, numbered, copy-safe steps |
| Working database policy | Use a persistent project/UAT MySQL 8 database for application data; do not label it demo/practice. Destructive automated tests still require isolation and may never erase persistent or production data |

---

## 2. Status Definitions

| Status | Meaning |
|---|---|
| UNVERIFIED | Code may exist, but the new team has not audited it |
| PENDING | Approved scope, implementation not started |
| IN PROGRESS | Assigned and actively being worked on |
| BLOCKED | Cannot continue until a dependency/decision is resolved |
| QA | Implementation complete; verification underway |
| COMPLETE | Project Lead reviewed evidence and accepted all quality gates |

No existing feature is inherited as COMPLETE. Phase 0 must classify every module.

---

## 3. Phase Tracker

| Phase | Owner(s) | Scope | Status | Completion gate |
|---|---|---|---|---|
| 0 | Agent 0, 8, 9, 11 | Existing codebase status audit | COMPLETE | Evidence accepted; `PHASE_0_STATUS_AUDIT.md` |
| 0A | Agent 0, 3, 4, 8, 9, 11, 12 | Critical admin/payment/discount/inventory/refund safety remediation | COMPLETE | Accepted 25 Aug 2026: 221 tests / 739 assertions; build and dependency audits green |
| 0B | Agent 0, 2, 6, 8, 9, 11 | Stack alignment: dependency review, Filament 5, exact MySQL 8 migration, cron strategy | COMPLETE | Accepted 25 Aug 2026: all independent gates plus owner-reported MySQL 8.4.3 forward migration |
| 1 | Agent 1, 2, 13 | Homepage + Shop design specifications | COMPLETE | Accepted 25 Aug 2026: structure, four viewport captures, Rythme Red, accessibility/SEO contract |
| 2 | Agent 4, 3, 11, 12, 15 | MySQL schema, migrations and domain architecture | COMPLETE | Accepted 25 Aug 2026: 225 tests / 753 assertions plus owner-reported MySQL 8.4.3 forward migration/status |
| 3 | Agent 2, 1, 9, 13 | Pixel-accurate Homepage + Shop frontend | BLOCKED | Chunks 1–4 and remediation pass; seven exact-width empty/static captures qualify; isolated populated Homepage/Shop 1440/390 captures required |
| 4 | Agent 3, 4, 9, 11, 12, 14 | Accounts, cart, wishlist, checkout and orders | PENDING | Functional, authorization and integration tests pass |
| 5 | Agent 3, 6, 9, 14 | Reviews, ratings, comments/Q&A and coupons | PENDING | Moderation, security and workflow tests pass |
| 6 | Agent 5, 4, 8, 15 | Bajaao catalog scraper/import pipeline | PENDING | Validated import, deduplication and media report |
| 7 | Agent 6, 3, 4, 11 | Filament resources, dashboards and RBAC | PENDING | Role matrix and admin UAT pass |
| 8 | Agent 8, 3, 4, 11–15 | Security, accessibility, compliance and performance hardening | PENDING | No unresolved critical/high issue; load targets pass |
| 9 | Agent 9 + all | Full QA, regression and bug fixing | PENDING | UAT pass; critical bug count = 0 |
| 10 | Agent 0, 7, 9 | Production-readiness review | PENDING | Every production gate verified |
| 11 | Agent 10 | Shared-hosting deployment | INACTIVE | Activated only by explicit user command |

---

## 4. Module Verification Tracker

### 4.1 Shopping Experience

| Module | Current status | Reference match | Evidence required |
|---|---|---|---|
| Homepage | UNVERIFIED | UNVERIFIED | Section inventory, screenshots, responsive comparison |
| Header/navigation/mega menu | UNVERIFIED | UNVERIFIED | Desktop/mobile interaction comparison |
| Product listing | UNVERIFIED | UNVERIFIED | Route, query, UI and pagination tests |
| Category/brand/price/rating filters | UNVERIFIED | UNVERIFIED | Query correctness and UI tests |
| Sorting | UNVERIFIED | UNVERIFIED | Price/popularity/newest tests |
| Search/autocomplete | UNVERIFIED | UNVERIFIED | Relevance, performance and UX tests |
| Product detail | UNVERIFIED | UNVERIFIED | Gallery, variants, specs and related products |
| Recently viewed | UNVERIFIED | UNVERIFIED | Persistence/privacy behavior |
| Stock/availability | UNVERIFIED | UNVERIFIED | DB and concurrency behavior |
| Footer | UNVERIFIED | UNVERIFIED | Reference and responsive comparison |

### 4.2 Cart and Checkout

| Module | Current status | Evidence required |
|---|---|---|
| Cart add/update/remove | UNVERIFIED | Functional and price-integrity tests |
| Guest/auth cart merge | UNVERIFIED | Session/login integration tests |
| Multi-step checkout | UNVERIFIED | UI, validation and failure-path tests |
| Address management | UNVERIFIED | Ownership and CRUD tests |
| Coupon engine | UNVERIFIED | Rule, expiry, limit and concurrency tests |
| Order summary/confirmation | UNVERIFIED | Immutable totals and notification tests |
| Payment gateway | UNVERIFIED | Callback/webhook/idempotency/reconciliation evidence |

### 4.3 User Engagement

| Module | Current status | Evidence required |
|---|---|---|
| Wishlist | UNVERIFIED | Authorization and cart integration tests |
| Reviews and ratings | UNVERIFIED | Verified-purchase, moderation and aggregation tests |
| Product Q&A/comments | UNVERIFIED | Threading, moderation, abuse and XSS tests |
| Order history/tracking | UNVERIFIED | Ownership and status timeline tests |
| Email notifications | UNVERIFIED | Event matrix, queue/cron and delivery tests |
| In-app notifications | UNVERIFIED | Persistence/read-state/preference tests |

### 4.4 User Account

| Module | Current status | Evidence required |
|---|---|---|
| Registration/login/logout | UNVERIFIED | Authentication and throttling tests |
| Email verification | UNVERIFIED | Signed link and access policy tests |
| Password reset | UNVERIFIED | Token, expiry and security tests |
| Profile management | UNVERIFIED | Validation and authorization tests |
| Address book | UNVERIFIED | Ownership and default-address tests |
| Account dashboard | UNVERIFIED | Responsive UI and data isolation tests |

### 4.5 Admin Capabilities

| Module | Current status | Evidence required |
|---|---|---|
| Product/category/brand CRUD | UNVERIFIED | Resource behavior and authorization |
| Order management | UNVERIFIED | Transition rules and audit history |
| Customer management | UNVERIFIED | PII controls and permissions |
| Review/comment moderation | UNVERIFIED | Workflow, bulk action and audit tests |
| Coupon management | UNVERIFIED | Validation and permissions |
| Sales dashboard/reports | UNVERIFIED | Query accuracy and performance |
| Roles/permissions | UNVERIFIED | Least-privilege role matrix tests |
| Product import/export | UNVERIFIED | Validation, retry and failure report |

---

## 5. Phase 0 Audit Checklist

### Repository and Runtime

- [ ] Record branch, commit and working-tree state.
- [ ] Verify PHP and Laravel exact versions.
- [ ] Determine the installed Filament version and Laravel compatibility.
- [ ] Inspect Composer and Node dependencies.
- [ ] Confirm whether frontend code follows the newly locked conventions.
- [ ] Verify MySQL configuration and remove SQLite-only assumptions.
- [ ] Run fresh MySQL migrations and seeders.
- [ ] Run complete PHP automated tests.
- [ ] Run frontend production build.
- [ ] Run dependency security audits.

### Architecture

- [ ] Inventory routes, controllers, requests, policies, middleware and services.
- [ ] Inventory models, migrations, indexes, constraints and relationships.
- [ ] Inventory Blade components, CSS, JavaScript and Livewire components.
- [ ] Inventory Filament resources, widgets and access controls.
- [ ] Inspect payment, notification, review/comment and import systems.
- [ ] Detect N+1 queries, unsafe raw SQL, debug code and stale files.
- [ ] Map each Enterprise Feature Checklist item to implementation and tests.

### Design

- [ ] Capture current Homepage desktop/tablet/mobile evidence.
- [ ] Capture current Shop desktop/tablet/mobile evidence.
- [ ] Obtain and inspect current reference-page evidence.
- [ ] Mark each section Match: Yes / Partial / No / Cannot Verify.
- [ ] Do not infer pixel-perfect fidelity without measurable evidence.

### Shared-hosting Constraints

- [ ] Identify features requiring persistent workers, WebSockets or unavailable services.
- [ ] Plan cron-driven queue processing where background execution is required.
- [ ] Audit filesystem/storage-link assumptions.
- [ ] Audit scheduler and deployment requirements without starting deployment work.

### Audit Deliverables

- [ ] Phase 0 Status Audit Report.
- [ ] Verified module inventory.
- [ ] Design mismatch list.
- [ ] Architecture/security risk register.
- [ ] Test/build/audit evidence.
- [ ] Updated sequence and effort priorities.
- [ ] Project Lead approval before Phase 1.

---

## 6. Current Blockers and Clarifications

| Item | State |
|---|---|
| Existing codebase qualification | Phase 0 audit and Phase 0A safety remediation complete; later phase gates remain |
| Phase 3 post-remediation empty-state evidence | PARTIAL PASS: Shop 1440/768/390/320 and Homepage 768/390/320 exact DPR-2 widths qualify; supplied Homepage desktop JPEG is only 1600px wide |
| Phase 3 populated catalogue presentation | BLOCKED: isolated ignored SQLite fixture and live preview are ready with 33 products, 32 categories and 24 brands; owner captures at 1440/390 remain required; persistent `rhythm_db` was untouched |
| Reference-page measurements/screenshots | PASSED: four owner-supplied DPR-2 captures measured; hashes recorded without committing third-party images |
| MySQL connection for migration verification | PASSED: owner reported all Phase 2 migrations `Ran` on persistent MySQL 8.4.3 `rhythm_db` |
| Final brand logo/colors/assets | Phase 1 direction accepted: current logo + Rythme Red `#B20202`; final production assets still require later QA |
| Comment scope | Must decide product Q&A only vs. review/blog comments before Phase 5 |
| Product scraper language | PHP or Python decision required before Phase 6 |
| Commercial rights to source product content/images | Must be resolved before production catalog launch |
| Hosting/deployment | Intentionally inactive until explicit command |

---

## 7. Production Sign-off Gates

Agent 0 will not issue production sign-off until all are verified:

- [ ] Master Enterprise Feature Checklist complete.
- [ ] Homepage and Shop design match measured and accepted.
- [ ] MySQL schema and migrations production-safe.
- [ ] Payment, order, inventory and coupon integrity verified.
- [ ] Authorization and role boundaries verified.
- [ ] Security audit has zero unresolved critical/high findings.
- [ ] Performance fits agreed shared-hosting limits.
- [ ] Queue/scheduler strategy works without persistent workers.
- [ ] Full automated regression and UAT pass.
- [ ] Critical bug count is zero.
- [ ] Catalog import report accepted.
- [ ] Backup, recovery and operational documentation ready before deployment.

---

## 8. Phase 0A Acceptance Evidence

Accepted by Agent 0 on 25 August 2026 after specialist implementation and independent review:

- Deny-by-default Filament boundary: customers cannot enter admin; seeded admin access remains explicit.
- Checkout persists only server-derived catalog prices, discount, shipping, GST and totals; stale/tampered coupons are rejected.
- Coupon use is locked and reserved before payment initiation, capped under concurrency, and released once on unpaid cancellation.
- Payment initiation/finalization is replay-safe; gateway identifiers are unique; customer confirmation is ownership-scoped.
- First paid transition alone changes product/variant inventory, status history and confirmation mail state.
- Paid cancellation creates a durable pending refund request and never falsely reports gateway refund completion.
- Fresh migration and seed passed; production frontend build passed.
- Full PHP regression: **221 tests / 739 assertions passed**.
- PHP syntax scan: **198 files passed**.
- Composer audit: **0 advisories**. npm production/full audits: **0 vulnerabilities**.
- Full-repository Pint scan reports 53 pre-existing style issues outside this remediation; every changed Phase 0A PHP file passes targeted Pint. This is tracked as baseline cleanup, not a safety regression.
- External Composer dependency rule verified: `vendor` is a symlink resolving to `/tmp/rythm-vendor`.

Phase 0A closes its identified critical findings but does **not** constitute enterprise or production sign-off. Exact MySQL 8 validation, Filament 5 alignment, full architecture/design phases, reconciliation/refund execution operations and remaining roadmap gates are still pending.

## 9. Phase 0B Acceptance Evidence

Completed and independently reviewed on 25 August 2026:

- Preserved the locked backend at exact Laravel **13.24.0**.
- Upgraded Filament **3.3.54 → 5.7.6**, Livewire **3.8.3 → 4.4.2**, and Filament media plugin **3.3.54 → 5.7.6**.
- Replaced the Filament-3-only Tiptap plugin with Filament 5's native TipTap-backed `RichEditor`, preserving HTML storage.
- Migrated resource schemas, actions, navigation enum types, custom Settings page and order infolist to Filament 5 APIs.
- Preserved and re-tested the explicit admin/customer panel boundary.
- Added Composer's `filament:upgrade` post-autoload hook and published Filament 5 assets for shared-hosting artifacts.
- Added a bounded every-minute queue worker schedule and cPanel operations contract.
- Fresh SQLite migration/seed and production build passed.
- Full regression: **222 tests / 744 assertions passed**.
- Changed-PHP Pint: **42 files passed**; PHP syntax: **198 files passed**.
- Composer audit: no advisories. npm production/full audits: zero vulnerabilities.

**Exact MySQL acceptance evidence:** the owner reported MySQL Community Server **8.4.3**, configured the persistent project/UAT database as `rhythm_db`, successfully aligned local Composer dependencies, and reported `php artisan migrate --force` completed successfully. This closes the forward-migration compatibility gate. The agent cannot directly inspect the owner's localhost, so this portion is explicitly owner-reported evidence. MariaDB results were not substituted.

Sample/development seeders and destructive automated suites were intentionally not run against `rhythm_db`. The independent full regression remains **222 tests / 744 assertions** on the isolated test environment. Persistent `rhythm_db` remains protected from `migrate:fresh`, `db:wipe`, `RefreshDatabase`, and unapproved seed/import operations.

**Agent 0 decision:** Phase 0B is `COMPLETE` and accepted on 25 August 2026. This is not production sign-off.

## 10. Phase 2 Acceptance and Recommended Next Action

**Phase 2 exact MySQL evidence:** the owner reported successful execution of migrations `000004`, `000005`, and `000006` on persistent MySQL Community Server 8.4.3 `rhythm_db`, followed by `php artisan migrate:status` showing all migrations as `Ran`. Sample seeders and destructive test commands were not run. Together with the independent **225 tests / 753 assertions**, migration rollback/forward, syntax/style, and security-audit evidence, Agent 0 accepts Phase 2 as `COMPLETE` on 25 August 2026. This is not production sign-off.

Auto Mode is paused at the full-phase checkpoint. The next eligible action is explicit `ACTIVATE AUTO MODE` to begin Phase 3. Deployment remains inactive.
