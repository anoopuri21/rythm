# Rythme Prioritized Backlog

**Updated:** 31 August 2026
**Delivery objective:** working client-facing e-commerce demo and safe launch path with the least necessary scope
**Canonical sequence:** `tasks/CANONICAL_PHASE_SEQUENCE.md`
**Short plan:** `tasks/MVP_LAUNCH_PLAN.md`
**Auto Mode / deployment:** paused / inactive

## P0 — do now: Phase 12 launch blockers

1. Close only critical safety issues affecting authentication, authorization, customer ownership, cart, checkout, payment callbacks/webhooks, order totals, inventory or customer-data exposure.
2. Verify basic production-safe controls: CSRF, throttling, security headers, secret handling, dependency findings, debug state and approved content boundaries.
3. Run the focused and full available checks, then obtain owner-side PHP/Laravel, exact MySQL 8, browser/accessibility and runtime evidence where Arena cannot execute them.
4. Keep payment, refund, tax, return, warranty, privacy and legal decisions human-approved. Do not invent values or enable disabled workflows.
5. Record only evidence-backed fixes, commit/push them to `rhythm-uat`, and keep the workspace clean.

## P1 — minimum launch path

### Phase 12 — core safety, authorization and payment/order correctness

- Route/action authorization and ownership boundaries.
- Payment initiation/finalization/webhook idempotency, order totals and inventory integrity.
- Basic security headers, CSRF/throttling, secret/debug checks and customer-data privacy blockers.
- Approved legal/tax/content boundaries only.

**Gate:** no unresolved critical/high blocker; focused checks and required owner/runtime checks pass.

### Phase 13 — practical performance smoke

- Production frontend build.
- Homepage, catalogue/search, product, cart and checkout rendering.
- Bounded query/N+1 and obvious console, overflow or page-error checks.
- Agreed viewport smoke checks, without starting large-scale load testing.

**Gate:** no material storefront/cart/checkout performance or rendering regression.

### Phase 14 — minimum operations

- Production environment values, external secrets, HTTPS/TLS and debug-off verification.
- Database/media backup and one isolated restore proof.
- Shared-host logs, queue/cron, storage/media access and a short rollback path.

**Gate:** owner can restore and roll back safely without destructive commands against persistent data.

### Phase 15 — cPanel/shared-host release package

- Versioned release artifact and commit evidence.
- Environment checklist with secrets kept outside the repository.
- Safe forward migrations, storage/media and scheduler/queue steps.
- Migration, smoke-test and rollback checklist.

**Gate:** package is reproducible and usable by the owner on the target shared host.

### Phase 16 — focused client UAT

- Browse, search, product/variant, cart, checkout and test payment.
- Order status, invoice, account and essential admin catalogue/order actions.
- Four viewports: 1440×900, 768×1024, 390×844 and 360×800.
- Record broken journeys, console errors, horizontal overflow, accessibility blockers and payment/order/inventory outcomes.

**Gate:** no release-blocking defect; owner evidence is attached and the candidate is frozen.

### Phase 17 — evidence review and go/no-go

- Review security, payment/order, database, performance smoke, operations, release-package and UAT evidence.
- Confirm legal/content decisions and backup/restore/rollback evidence.
- Record `GO`, `NO-GO` or `BLOCKED` with unresolved risks and required owner action.
- Do not deploy from this phase.

**Gate:** explicit Agent 0 decision and owner approval; this does not activate Phase 18.

### Phase 18 — deployment

Remain `INACTIVE`. Deployment requires a separate explicit owner activation after Phase 17 acceptance. Agent 10 remains inactive. No live payment/refund action or production change is implied.

## P2 — future backlog; must not block the MVP track

- Full penetration testing, advanced threat modelling and CSP nonce/strict-dynamic migration.
- Detailed account export/deletion, retention/anonymization and consent tooling after legal/product decisions are supplied.
- Large-scale load/concurrency testing, Redis/CDN/object storage, external search and advanced resilience/circuit breakers.
- Full observability dashboards, multi-region disaster recovery, broad CI/CD automation and zero-downtime deployment.
- Extended browser/device matrix, visual-regression infrastructure and non-blocking accessibility polish.
- Analytics/marketing automation, SMS/WhatsApp, abandoned-cart/price-drop campaigns, gift cards and other growth features.
- Additional enterprise integrations or workflow completeness not required by the critical client journeys.

## Never defer or bypass

- Payment/order/inventory correctness and financial reconciliation truth.
- Authorization, customer-data isolation and critical security remediation.
- Owner-approved legal/tax/return/warranty/privacy decisions required by enabled behavior.
- HTTPS, production configuration, backup/restore and rollback evidence.
- Focused owner UAT, evidence review and explicit go/no-go decision.

## Completion rule

Every MVP phase needs a bounded plan, focused verification, relevant owner-side evidence, documentation, Agent 0 acceptance and a clean pushed commit where code changed. Absence of Arena runtime access is a pending owner gate, not evidence of success.
