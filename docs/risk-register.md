# Rythme Current Risk Register

**Audit date:** 29 August 2026  
**Scale:** Critical blocks safe operation/release; High must close before production readiness; Medium requires owned mitigation; Low is monitored.

| ID | Severity | Area | Evidence / risk | Existing control | Required action / owner |
|---|---|---|---|---|---|
| R-001 | Critical | Environment | `rythm.test` was observed connected to unrelated `maverick_academy`; reads/writes and UAT could target the wrong database. | App does not auto-create missing domain tables; navbar errors degrade. | Owner: set environment to `rhythm_db`, clear config/cache, restart PHP if needed, verify effective DB and migration status before UAT. |
| R-002 | High | Tax/legal | GST/HSN, intra/inter-state rules, invoice numbering, return/warranty/shipping terms are not professionally approved. | Unknown rules remain disabled/unpublished. | Tax/legal owner + Agent 0 in Phase 10/12; no implementation guesses. |
| R-003 | High | Fulfillment | Durable shipment domain exists but operational Filament/customer workflow is incomplete. | Locked allocations/state service and audit evidence. | Complete Phase 10 admin/customer workflow, authorization and tests. |
| R-004 | High | Returns | RMA/eligibility/replacement workflow is absent. | Cancellation/refund is explicit and financial state is separate. | Build configurable disabled-by-default RMA in Phase 10; professional rules gate. |
| R-005 | High | Security assurance | Independent penetration test and final route/IDOR review are not complete. | Auth, policies, throttles, signed URLs, CSP and tests exist. | Phase 12 independent review; close all critical/high findings. |
| R-006 | High | Backups | Encrypted off-site backup and restore drill are not proven. | No destructive production automation is authorized. | Phase 14 configure retention and perform documented restore drill. |
| R-007 | High | Operations | Error monitoring, metrics, production alerts and incident/on-call drills are incomplete. | Audit logs and read-only reconciliation commands exist. | Phase 14 observability and incident runbooks. |
| R-008 | High | Release | Reproducible cPanel release/rollback artifact is not yet proven. | Shared-host-safe scheduler design and clean branch checkpoints. | Phase 15 package, staging deploy/rollback drill; Phase 18 remains inactive. |
| R-009 | Medium | Media performance | No responsive image conversions; original uploads may be oversized. | Local managed media, byte limits during acquisition, lazy loading. | Phase 13 define dimensions/formats, conversion lifecycle and LCP budgets. |
| R-010 | Medium | Frontend performance | Swiper, GSAP and Lenis are in the main entry bundle; route-level lazy loading is absent. | Production Vite build and reduced-motion handling. | Measure bundle/CWV before splitting; avoid speculative dependencies. |
| R-011 | Medium | Homepage queries | Popular category representative images may issue up to ten extra queries during cache rebuild. | One-hour cache, bounded category limit. | Profile MySQL query count; batch representative media query if material. |
| R-012 | Medium | Global layout | Navbar category schema/cache lookup occurs on every uncached process/render. | Forever cache plus missing-table fallback. | Measure and retain only if negligible; consider boot-safe cached snapshot. |
| R-013 | Medium | Search scale | Search/facets remain MySQL/shared-host based and realistic-volume latency needs final evidence. | Normalized bounded query service and existing large-catalogue tests. | Phase 11 weighted/typo strategy and Phase 13 query/load evidence. |
| R-014 | Medium | Financial unknown outcomes | Provider may accept a payment/refund while browser request times out. | Durable processing states, idempotency, webhook/event ledger and no blind financial retry. | Continue reconciliation-only handling and alerting; test outage cases. |
| R-015 | Medium | Queue delivery | Cron or hosting limits can delay mail/notifications. | Bounded stop-when-empty worker, failed outcomes, retries and reconciliation. | Phase 14 backlog alert; Phase 15 cron verification. |
| R-016 | Medium | Secrets | Hosting/operator mistakes could expose keys through `.env`, logs or support evidence. | Secrets environment-only, redaction conventions, no committed credentials. | Phase 12 secret scan/rotation procedure; never paste full headers/tokens. |
| R-017 | Medium | Imported content | Imported copy/media may contain retailer promises or rights ambiguity. | Publication review, local media, commercial approval metadata and activation guard. | Continue per-product review and audit trail. |
| R-018 | Medium | Stock truth | Source availability could be mistaken for Rythme inventory. | Imports inactive; activation requires positive verified local/variant stock. | Maintain real inventory entry/reconciliation; never infer source stock. |
| R-019 | Medium | Accessibility/browser | Final WCAG 2.2 AA, screen-reader and supported-browser matrix is incomplete. | Semantic views, keyboard controls and prior axe checks. | Phase 12/16 full accessibility and compatibility gate. |
| R-020 | Medium | Database portability | Production is MySQL while most automated tests use SQLite; engine-specific DDL/index behavior can differ. | Owner MySQL migrations and prior migration hotfix evidence. | Run every forward migration on disposable/persistent staging MySQL before release. |
| R-021 | Low | Cache staleness | Incorrect observer coverage can leave homepage/catalogue stale. | Product/category/homepage observers now flush relevant caches. | Regression-test every new merchandising input. |
| R-022 | Low | Legacy admin role | Broad legacy `admin` alias remains equivalent to super-admin during migration. | Explicit comment and controlled role map. | Remove after all owner accounts are migrated and recovery is proven. |

## Release blockers

At minimum R-001 through R-008 must close before Phase 17 can sign off. No current evidence authorizes Phase 18, live deployment, live-key use or real financial test writes.
