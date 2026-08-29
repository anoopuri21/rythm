# Rythme Prioritized Backlog

**Audit date:** 29 August 2026  
**Ordering rule:** safety/data correctness first, then canonical Phases 10–17. Phase 18 remains inactive.

## P0 — immediate blockers

1. **Correct the active database** — Owner sets `DB_DATABASE=rhythm_db`, clears optimized config/cache, restarts PHP/Herd if required, verifies effective database and `migrate:status`. Never migrate `maverick_academy` for this app.
2. **Qualify homepage discovery candidate** — Run focused homepage/category/admin tests, full regression, and desktop/mobile rendering for Popular Categories, latest active arrivals, explicitly marked Trending and truthful Best Deals.
3. **Preserve clean financial state** — For paid cancellations, Finance processes the existing pending refund once; processing/unknown outcomes are reconciled, not retried.

## P1 — canonical Phase 10

1. Build least-privilege Filament shipment create/allocate/transition operations on `FulfillmentService`.
2. Add customer-safe partial-shipment timeline and approved transactional events without exposing internal references unnecessarily.
3. Build configurable, disabled-by-default return/RMA reasons, eligibility and state machine.
4. Connect approved returns to the existing refund workflow without conflating logistical and provider outcomes.
5. Add optional product HSN/tax classification and immutable order-line tax snapshots.
6. Add invoice/credit-note identity framework only after professional numbering/tax approval.
7. Run MySQL migration, authorization, state-machine, rendered and full regression gates.

## P1 — canonical Phase 11

1. Measure and improve MySQL shared-host search across realistic catalogue volume.
2. Add weighted SKU/name/category/brand/attribute matching and bounded typo tolerance where feasible.
3. Complete category/brand/attribute facets and truthful empty/error states.
4. Add recently viewed and admin-managed related/complementary merchandising.
5. Add consent-safe back-in-stock subscriptions; exclude unapproved price-drop/abandoned-cart promises.
6. Perform mobile conversion, SEO and responsive qualification.

## P1 — release gates, Phases 12–17

### Phase 12: security/privacy/compliance/accessibility

- Route/action authorization and IDOR matrix; OWASP/Laravel review.
- CSP/cookies/proxies/HTTPS/HSTS/debug hardening.
- Secret/dependency scan and rotation procedure.
- PII retention/export/deletion; approved legal/privacy/cookie content.
- WCAG 2.2 AA and independent penetration test closure.

### Phase 13: performance/resilience

- Define measurable SLOs and profile critical storefront/admin queries.
- Eliminate material N+1s; verify MySQL indexes/explain plans.
- Responsive image conversions and frontend bundle/CWV budgets.
- Load/concurrency/outage tests for browse/cart/checkout/payment/queue.

### Phase 14: observability/backups/operations

- Correlation IDs, structured redacted logs, error monitoring and metrics.
- Payment/refund/queue/notification/stock alerts and dashboards.
- Encrypted off-site backups, retention and successful restore drill.
- Incident severity/on-call and payment/DB/queue/secret runbooks.

### Phase 15: CI/CD/shared-host release

- CI tests/style/static analysis/build/audit/secret scan.
- Reproducible release artifact and version evidence.
- Safe migration, backup, cPanel scheduler/queue, storage-link and rollback drill.
- Staging smoke tests; production secrets remain external.

### Phase 16: full QA/release candidate

- Critical E2E journeys, duplicate/slow/refresh cases and admin regression.
- Supported browser/mobile matrix, accessibility and SEO validation.
- Payment, email, fulfillment, invoice/tax and returns owner UAT.
- Freeze candidate with zero blocker/critical/high-risk unresolved defects.

### Phase 17: production readiness decision

- Independently verify every gate and evidence artifact.
- Require clean pushed state, no critical blocker and explicit Agent 0 sign-off.
- Produce go/no-go decision; do not deploy.

## P2 — targeted technical debt

1. Batch Popular Category representative-image loading if profiling confirms cache-rebuild N+1 cost.
2. Lazy-load route-specific Swiper/GSAP/Lenis modules if bundle measurements justify it.
3. Remove broad legacy `admin` alias after account migration/recovery proof.
4. Add media format/dimension governance and orphan cleanup.
5. Reconcile older planning-document statuses with the master tracker without changing canonical authority.

## P3 — explicitly deferred/unapproved

- SMS/WhatsApp providers and marketing automation.
- Gift cards/store credit, abandoned-cart campaigns and price-drop promises.
- Persistent external search daemon, Redis, object storage or carrier integration unless separately approved.
- Invented GST/HSN/shipping/return/warranty/legal content.
- Phase 18 deployment, Agent 10 and real financial writes without explicit human authorization.

## Completion mapping

Each backlog item requires: bounded plan, implementation, focused tests, full regression, relevant MySQL/build/browser/audit evidence, documentation, Agent 0 acceptance, commit/push/hash and clean workspace. External credentials and professional decisions remain human gates rather than implementation guesses.
