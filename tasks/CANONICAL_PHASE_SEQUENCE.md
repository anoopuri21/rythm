# Rythme Canonical Delivery Phase Sequence

**Established:** 26 August 2026
**Authority:** Agent 0
**Status:** CANONICAL — supersedes conflicting phase numbering in older planning documents
**MVP launch track:** `tasks/MVP_LAUNCH_PLAN.md`
**Delivery posture:** short, manual and launch-blocker-focused; enterprise enhancements are deferred unless they become a real safety or release blocker

## Source-of-truth rule

1. `tasks/MASTER_PROJECT_TRACKER.md` is authoritative for delivery phase number, current status and acceptance.
2. This document is authoritative for the ordered phase sequence and crosswalk.
3. `tasks/ENTERPRISE_ECOMMERCE_ROADMAP.md` is a capability inventory. Its former phase numbers are now **E-series workstream IDs** and must not be used as delivery phase numbers.
4. Completed delivery phases retain their accepted numbers (currently 0 through 11, plus 6A). They will not be retroactively renumbered.
5. A later phase may close inherited tasks from several E-series workstreams, but every deferred task must remain traceable in the crosswalk.
6. Agent 10 remains inactive until explicit deployment activation. Canonical sequencing does not activate deployment.

## Canonical delivery sequence

| Delivery phase | Scope | Primary workstream coverage | Status |
|---|---|---|---|
| 0 | Existing repository status audit | E0 baseline | COMPLETE |
| 0A | Critical commerce and authorization safety remediation | E1 domain, E2 finance | COMPLETE |
| 0B | Stack alignment, Filament 5, exact MySQL and shared-host baseline | E0, E11 | COMPLETE |
| 1 | Homepage and Shop design specification | Storefront programme | COMPLETE |
| 2 | MySQL schema and domain architecture | E1 | COMPLETE |
| 3 | Homepage and Shop frontend qualification | Storefront programme | COMPLETE |
| 4 | Accounts, cart, wishlist, checkout and orders | E1, E2 | COMPLETE |
| 5 | Verified reviews, moderated product Q&A and coupons | E3 | COMPLETE |
| 6 | Controlled catalogue acquisition and import pipeline | Catalog acquisition prerequisite absent from legacy roadmap | COMPLETE |
| 7 | Admin governance, staff RBAC and auditability | E7 | COMPLETE |
| 8 | Payment, refund and financial reconciliation operations | E2 | COMPLETE |
| 9 | Central notifications and external-integration event architecture | E4 | COMPLETE |
| 10 | Shipping, fulfillment, returns and India tax workflow | E5 | COMPLETE |
| 11 | Customer experience, search and merchandising | E6 plus approved residual E3 enhancements | COMPLETE |
| 12 | MVP core safety: authorization, privacy/payment/order blockers, basic security and approved content boundaries | Launch-blocking subset of E8 plus cross-cutting gates | COMPLETE |
| 13 | Practical storefront, cart and checkout performance smoke checks | Launch-blocking subset of E9 | COMPLETE |
| 14 | Minimum operations: environment, SSL, backup/restore, logs, queue/cron and rollback | Launch-blocking subset of E10 | COMPLETE |
| 15 | cPanel/shared-host release package and migration checklist | Launch-blocking subset of E11 | PENDING |
| 16 | Focused client UAT: browse, search, cart, checkout, payment, order, invoice and admin essentials | Launch-blocking subset of E12 | PENDING |
| 17 | Final evidence review and explicit go/no-go decision | Required release gates across all workstreams | PENDING |
| 18 | Shared-hosting deployment, launch and stabilization | E13; Agent 10 by explicit activation only | INACTIVE |

## Legacy workstream crosswalk

| Enterprise workstream | Capability | Canonical delivery coverage |
|---|---|---|
| E0 | Baseline audit, environment and scope freeze | 0, 0B; completed |
| E1 | Domain hardening and database integrity | 0A, 2, 4; completed baseline, remaining findings feed their owning later phase |
| E2 | Payment gateway, refunds and reconciliation | 0A and 4 foundations; operational completion in 8 |
| E3 | Reviews, ratings and comments | Owner-approved reviews + product Q&A completed in 5; blog/threaded comments excluded; separately approved enhancements may enter 11 |
| E4 | Notifications | 9 |
| E5 | Shipping, fulfillment, returns and tax | 10 |
| E6 | Customer experience, search and merchandising | 11 |
| E7 | Admin governance, RBAC and auditability | 7 |
| E8 | Security, privacy and compliance | Launch blockers only in 12; advanced hardening and unapproved legal/privacy workflows are future backlog |
| E9 | Performance, scalability and resilience | Practical storefront/cart/checkout smoke in 13; advanced scale/resilience is future backlog |
| E10 | Observability and production operations | Minimum backup/restore, logs, queue/cron and rollback in 14; full observability is future backlog |
| E11 | CI/CD, infrastructure and deployment preparation | Shared-host release package/checklist in 15; broad CI/CD and automation are future backlog |
| E12 | QA, accessibility, compatibility and release candidate | Focused client UAT in 16; extended matrices and non-blocking polish are future backlog |
| E13 | Production launch and stabilization | 18 after phase 17 sign-off and explicit deployment activation |

## Practical dependency rationale for the next phases

- **Phase 12 first:** payment/order correctness, authorization, customer-data boundaries and approved legal/content behavior are release blockers.
- **Phase 13 depends on the real storefront flow:** smoke the homepage, catalogue/search, cart and checkout rather than starting a broad load-testing programme.
- **Phase 14 depends on the candidate build:** verify the minimum shared-host runtime, backup/restore, queue/cron, logs and rollback before packaging.
- **Phase 15 packages the verified candidate:** include the commit/version, environment checklist, migrations, storage/media steps and rollback notes for cPanel/shared hosting.
- **Phase 16 is owner-facing UAT:** test only the critical client journeys and admin essentials needed for a credible demo/release candidate.
- **Phase 17 is a review, not deployment:** Agent 0 records the evidence-based decision; Phase 18 stays inactive until the owner explicitly activates deployment.
- **Phases 12–17 are mandatory safety/release gates:** they may be short, but they cannot be bypassed or collapsed into deployment.
- Existing E-series capabilities not needed for these gates remain traceable as future backlog; no enterprise completeness claim is made for the MVP track.

## Phase 6 activation decisions — RESOLVED 26 August 2026

1. Scraper implementation language: **PHP**.
2. The pipeline may be developed against bounded public reference data while commercial rights to competitor text/images remain unresolved.
3. Source-access constraints: public pages only, no authentication bypass, no CAPTCHA bypass and respectful rate limiting.

Commercial content and image rights remain a production-data gate even if the technical pipeline is built and tested with bounded fixtures. The owner has since required manual, short and practical execution; no continuous/autonomous execution is implied, and this does not waive gates, professional approvals or activate Agent 10.
