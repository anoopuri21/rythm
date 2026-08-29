# Rythme Canonical Delivery Phase Sequence

**Established:** 26 August 2026
**Authority:** Agent 0
**Status:** CANONICAL — supersedes conflicting phase numbering in older planning documents

## Source-of-truth rule

1. `tasks/MASTER_PROJECT_TRACKER.md` is authoritative for delivery phase number, current status and acceptance.
2. This document is authoritative for the ordered phase sequence and crosswalk.
3. `tasks/ENTERPRISE_ECOMMERCE_ROADMAP.md` is a capability inventory. Its former phase numbers are now **E-series workstream IDs** and must not be used as delivery phase numbers.
4. Completed delivery phases 0 through 5 retain their accepted numbers. They will not be retroactively renumbered.
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
| 7 | Admin governance, staff RBAC and auditability | E7 | IN PROGRESS |
| 8 | Payment, refund and financial reconciliation operations | E2 | PENDING |
| 9 | Central notifications and external-integration event architecture | E4 | PENDING |
| 10 | Shipping, fulfillment, returns and India tax workflow | E5 | PENDING |
| 11 | Customer experience, search and merchandising | E6 plus approved residual E3 enhancements | PENDING |
| 12 | Security, privacy, compliance and accessibility hardening | E8 plus cross-cutting accessibility/legal gates | PENDING |
| 13 | Performance, scalability and resilience | E9 | PENDING |
| 14 | Observability, backups and production operations | E10 | PENDING |
| 15 | CI/CD and shared-hosting release packaging | E11 | PENDING |
| 16 | Full QA, compatibility, UAT and release candidate | E12 | PENDING |
| 17 | Production-readiness review and Agent 0 sign-off decision | All workstreams and release gates | PENDING |
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
| E8 | Security, privacy and compliance | 12 |
| E9 | Performance, scalability and resilience | 13 |
| E10 | Observability and production operations | 14 |
| E11 | CI/CD, infrastructure and deployment preparation | 0B shared-host constraints; remaining packaging/automation in 15 |
| E12 | QA, accessibility, compatibility and release candidate | Accessibility hardening in 12; per-phase QA plus final phase 16 |
| E13 | Production launch and stabilization | 18 after phase 17 sign-off and explicit deployment activation |

## Dependency rationale for the next phases

- **Phase 6 precedes search/performance qualification:** realistic catalogue volume, normalized source data and media reports are needed before meaningful search, merchandising and load evidence.
- **Phase 7 precedes expanded financial/operational admin workflows:** least-privilege staff roles and auditability must exist before adding finance, support and fulfillment powers.
- **Phase 8 precedes refund notifications and return orchestration:** gateway refund and reconciliation truth must exist before downstream systems report financial outcomes.
- **Phase 9 precedes later workflow messaging:** fulfillment, review and payment events should use one idempotent notification architecture rather than adding more fragmented mail triggers.
- **Phase 10 requires professional business approval:** GST, HSN, shipping, return, replacement and warranty rules cannot be invented by implementation agents.
- **Phases 12–17 are mandatory release gates:** they cannot be collapsed into deployment.

## Phase 6 activation decisions — RESOLVED 26 August 2026

1. Scraper implementation language: **PHP**.
2. The pipeline may be developed against bounded public reference data while commercial rights to competitor text/images remain unresolved.
3. Source-access constraints: public pages only, no authentication bypass, no CAPTCHA bypass and respectful rate limiting.

Commercial content and image rights remain a production-data gate even if the technical pipeline is built and tested with bounded fixtures. The owner additionally authorized continuous sequential Agent 0 execution through Phase 11 under `tasks/AUTO_MODE_PHASE_6_TO_11_PROGRAMME.md`; this does not waive gates or activate Agent 10.
