# Rythme Enterprise E-commerce — Capability Workstream Inventory

**Prepared:** 25 August 2026  
**Numbering reconciled:** 26 August 2026
**Target:** Enterprise-grade, production-ready single-vendor musical instruments store  
**Current locked stack:** Laravel 13.24.0, PHP 8.3+, Blade/Livewire 4/custom CSS/Vanilla JS, Tailwind 4 retained where audited, Filament 5.7.6, Razorpay abstraction, exact MySQL 8.x

> This document began as a static capability review of `main` at commit `5bb8053`. Its former phase numbers are now **E-series enterprise workstream IDs**, not delivery phase numbers. The authoritative delivery order and status are `tasks/CANONICAL_PHASE_SEQUENCE.md` and `tasks/MASTER_PROJECT_TRACKER.md`. Existing code and tests indicate a strong functional base, but **the platform must not be called production-ready until all Production Gate items are verified in a staging environment**.

---

## Status Legend

| Status | Meaning |
|---|---|
| ✅ Completed | Implemented in the current repository with existing automated coverage |
| 🟡 Partial / Revalidation needed | Some implementation exists, but enterprise or production requirements remain |
| ⬜ Pending | Not found or not complete in the current repository |
| 🔒 Gate | Must pass before production release |

## Working Rule for Every Delivery Phase

Each canonical delivery phase is handled separately and closed only after:

1. Scope and acceptance criteria are reviewed.
2. Implementation is completed without breaking existing behavior.
3. Security and authorization checks are added.
4. Automated tests are added/updated.
5. `php artisan test`, frontend build, and relevant audits pass.
6. Manual responsive and accessibility smoke testing is completed.
7. Documentation and environment variables are updated.
8. User reviews the phase before the next phase begins.

---

# A. Existing Completed Foundation

These features were present in the reviewed repository. Delivery Phases 0/0B revalidated the baseline; later owning phases must still provide their required production evidence.

## A1. Platform and Storefront — ✅ Completed

- Laravel 13 / PHP 8.3+ application foundation.
- Blade, Livewire, Alpine.js and Tailwind storefront.
- Responsive homepage and reusable layout/components.
- Admin-managed homepage sections, hero slides, blocks and FAQs.
- Dynamic CMS pages and polymorphic SEO entries.
- Shop listing, product search, filters, sorting and pagination.
- Product detail page, variants, stock state, gallery, related products and JSON-LD.
- Categories, nested category tree and brands.
- Filament resources for products, categories, brands, pages and homepage content.
- Dynamic sitemap, robots.txt and custom 404/500 pages.

## A2. Customer and Commerce Core — ✅ Completed

- Customer registration, login and logout.
- Forgot/reset password and email verification flows.
- Customer profile, password update and address book.
- Guest/session cart and authenticated cart merge.
- Cart drawer, cart page, quantity controls and server-side price snapshots.
- Authenticated wishlist and move-to-cart flow.
- Checkout address/payment wizard.
- Orders, immutable order-item/address snapshots and status history.
- GST, shipping fee and free-shipping threshold settings.
- Customer order history, order detail, tracking and printable invoice.
- Customer cancellation for eligible order states with stock restoration.
- Coupons with fixed/percentage discount and usage controls.

## A3. Payment, Reviews and Admin Operations — ✅/🟡

- ✅ Payment gateway abstraction and fake gateway for tests.
- ✅ Razorpay order/callback/webhook integration and signature verification code.
- 🟡 Real Razorpay test-mode end-to-end staging verification is still required.
- ✅ Product rating/review submission for verified purchasers.
- ✅ Duplicate review guard, approval workflow and rating summary.
- ✅ Filament review moderation resource.
- ✅ Filament order management and guarded status transitions.
- ✅ Customer, contact-message and newsletter admin resources.
- ✅ Dashboard widgets and cached site settings.
- ✅ Queued order confirmation and selected order-status emails.

## A4. Existing Quality and Security Baseline — ✅/🟡

- ✅ Existing repository contains 208 automated test methods.
- ✅ CSRF protection, validation, throttling, honeypot and mass-assignment protections are represented in code/tests.
- ✅ Security headers middleware and signed checkout-success links exist.
- ✅ Server-side totals, stock checks and payment signature tests exist.
- ✅ Baseline and later phase test/build/dependency audits have been recorded in the master tracker and phase evidence.
- 🟡 Production infrastructure, observability, backups, CI/CD and operational verification remain pending.

---

# B. Enterprise Capability Workstreams (E-series)

## Workstream E0 — Baseline Audit, Environment and Scope Freeze — ✅ Covered by Delivery Phases 0/0B

**Goal:** Establish a trustworthy baseline before changing business functionality.

### Tasks

- [ ] Install dependencies in a clean environment and verify supported PHP/Node versions.
- [ ] Run all migrations and seeders on SQLite and target production DB (MySQL 8+).
- [ ] Run the complete PHP test suite and record exact tests/assertions.
- [ ] Run frontend production build.
- [ ] Run `composer audit` and `npm audit`.
- [ ] Validate route list, scheduled commands, queues and storage links.
- [ ] Audit stale documentation (`README`, `plan.md`, architecture docs, task JSON).
- [ ] Create a feature inventory mapped to routes, models, services, jobs and tests.
- [ ] Identify dead code, outdated assumptions and mismatches between documentation and implementation.
- [ ] Freeze approved currency, tax, shipping, cancellation, return and refund business rules.
- [ ] Confirm production stack: MySQL/Redis, object storage, mail provider, hosting and Razorpay account mode.
- [ ] Create `.env.production.example` without secrets.

### Definition of Done

- Clean setup, migration, build and tests pass.
- Dependency audit has no unresolved high/critical vulnerability.
- Baseline report and approved business decisions are documented.

---

## Workstream E1 — Domain Hardening and Database Integrity — 🟡 Foundations covered; later owning phases retain residual work

**Goal:** Make catalog, inventory, cart and order data safe under concurrency and production load.

### Tasks

- [ ] Review all money fields and calculations; use integer paise or a consistently tested decimal strategy.
- [ ] Centralize currency/money formatting and rounding rules.
- [ ] Add explicit database constraints, indexes, unique keys and foreign-key behavior where missing.
- [ ] Formalize order, payment, fulfillment, cancellation, return and refund state machines.
- [ ] Add idempotency keys for checkout/payment operations.
- [ ] Make payment callbacks/webhooks replay-safe and transaction-safe.
- [ ] Add webhook event log with unique gateway event/payment identifiers.
- [ ] Audit stock reservation vs. stock decrement behavior.
- [ ] Implement atomic stock reservation/release to prevent overselling during concurrent checkouts.
- [ ] Define low-stock, out-of-stock, backorder and discontinued-product behavior.
- [ ] Add inventory movement ledger for stock in/out/adjustment/return/cancellation.
- [ ] Add database-backed audit records for sensitive admin commerce changes.
- [ ] Add concurrency, duplicate callback and rollback tests.

### Definition of Done

- Duplicate requests cannot create duplicate charges/orders.
- Concurrent checkout cannot oversell inventory.
- Commerce state transitions are explicit, audited and thoroughly tested.

---

## Workstream E2 — Payment Gateway, Refunds and Financial Reconciliation — 🟡 Foundations complete; Delivery Phase 8 pending

**Goal:** Complete production-safe Razorpay operations beyond initial payment capture.

### Tasks

- [ ] Validate Razorpay test-mode checkout end to end on staging.
- [ ] Validate callback and webhook behavior using real Razorpay test events.
- [ ] Verify order amount, currency, gateway order ID and payment ID server-side.
- [ ] Add safe webhook payload retention with secret/PII redaction.
- [ ] Implement payment retry for failed/abandoned payments.
- [ ] Implement full and partial refunds through Razorpay.
- [ ] Track refund IDs, amount, reason, status and failure details.
- [ ] Add return/refund approval workflow in Filament.
- [ ] Add payment/refund status timeline for admins and customers.
- [ ] Create reconciliation command/report for internal orders versus Razorpay payments/refunds.
- [ ] Add alerts for signature failures, amount mismatches and unreconciled payments.
- [ ] Document live-key rotation and webhook-secret rotation.

### Definition of Done

- Payment, failure, retry, duplicate callback, full refund and partial refund scenarios pass.
- Finance/admin can reconcile every order with gateway records.

---

## Workstream E3 — Enterprise Review, Rating and Comment System — 🟡 Owner-approved scope completed in Delivery Phase 5

**Goal:** Extend the current verified-purchase review feature into a complete moderated engagement system.

### Existing

- ✅ Verified-purchase product reviews.
- ✅ Star rating, approval and duplicate guard.
- ✅ Review moderation resource.

### Capability disposition

- [x] Add review title.
- [x] Define and enforce one review per verified customer/product under the approved policy.
- [x] Add staff answers through moderated product Q&A.
- [x] Add moderation states/reasons, sanitization, authorization, throttling and audit-relevant metadata for the approved interactions.
- [x] Include truthful aggregate-rating/review schema only when approved reviews exist.
- [ ] Review media, edit windows, abuse reports, helpful voting, threaded review replies, aggregate caching, bulk moderation and export remain unapproved backlog candidates; they are not implied by workstream completion.
- [ ] Reviewer answer/reply notifications are owned by Delivery Phase 9 if approved in its event matrix.
- [x] CMS/blog comments are explicitly excluded from the approved scope and must not be inferred from this historical inventory.

### Definition of Done for the approved scope

- Reviews and product Q&A are authorized, moderated, sanitized, rate-limited and tested.
- Product rating aggregates remain correct across approved moderation transitions.
- Completion evidence is recorded in `tasks/PHASE_5_INTERACTIONS_QA.md`.

---

## Workstream E4 — Notification System — ⬜ Delivery Phase 9 pending

**Goal:** Build a centralized, reliable, user-configurable notification system.

### Existing

- ✅ Queued order confirmation email.
- ✅ Selected order-status emails.

### Pending Tasks

- [ ] Replace fragmented mail triggers with Laravel Notification classes/events/listeners.
- [ ] Add database notification channel and customer notification center.
- [ ] Add unread count, read/unread, mark-all-read and pagination.
- [ ] Add event matrix for:
  - account verification and password/security events;
  - order placed, payment successful/failed;
  - order confirmed, processing, shipped, delivered and cancelled;
  - refund initiated/completed/failed;
  - review approved/rejected and merchant reply;
  - comment/reply/mention where enabled;
  - wishlist price-drop and back-in-stock alerts;
  - low-stock/new-order/payment-failure admin alerts.
- [ ] Add notification preferences by category/channel.
- [ ] Add transactional versus marketing consent rules.
- [ ] Add email templates with branding and plain-text fallback.
- [ ] Add retry/backoff, failed-job handling and idempotent notification dispatch.
- [ ] Add optional SMS/WhatsApp provider only after provider and consent approval.
- [ ] Add admin notification log and delivery status visibility.
- [ ] Add queue and notification tests.

### Definition of Done

- Critical commerce events generate one reliable, traceable notification.
- Customers control non-essential notifications without disabling mandatory transactional messages.

---

## Workstream E5 — Shipping, Fulfillment, Returns and Tax — ⬜ Delivery Phase 10 pending

**Goal:** Replace simple shipping/GST settings with an operational fulfillment workflow.

### Tasks

- [ ] Confirm India GST model, GSTIN, HSN/SAC and intra/inter-state tax requirements with a tax professional.
- [ ] Add product-level tax classification/HSN where required.
- [ ] Add CGST/SGST/IGST calculation rules where applicable.
- [ ] Add tax-inclusive/exclusive invoice configuration.
- [ ] Add shipping zones, serviceability by PIN code and shipping methods.
- [ ] Add weight/dimension fields and shipping-rate calculation.
- [ ] Integrate approved shipping aggregator/carrier or define manual fulfillment workflow.
- [ ] Add shipment entity, AWB, carrier, tracking URL, labels and shipment events.
- [ ] Support partial shipment where required.
- [ ] Add return request/RMA workflow with configurable eligibility window.
- [ ] Add replacement/exchange flow where approved.
- [ ] Add cancellation and return reason management.
- [ ] Add downloadable tax invoice/credit note with immutable numbering rules.
- [ ] Add fulfillment and tax tests.

### Definition of Done

- Admin can fulfill, track, cancel, return and refund an order with a complete audit trail.
- Invoice/tax behavior matches approved legal/accounting rules.

---

## Workstream E6 — Customer Experience, Search and Merchandising — ⬜ Delivery Phase 11 pending

**Goal:** Improve conversion, usability and catalog discoverability at production scale.

### Tasks

- [ ] Add typo-tolerant search solution appropriate to scale (database/Scout/Meilisearch decision).
- [ ] Add SKU, category, brand and attribute-aware search.
- [ ] Add product attribute/facet system suitable for musical instruments.
- [ ] Add recently viewed products.
- [ ] Add related, complementary and frequently-bought-together merchandising rules.
- [ ] Add wishlist price-drop and back-in-stock subscriptions.
- [ ] Add compare-products feature for compatible categories.
- [ ] Add abandoned-cart recovery with consent and safe frequency caps.
- [ ] Improve coupon/promotion conflict, stacking and eligibility rules.
- [ ] Add gift cards/store credit only if approved.
- [ ] Add out-of-stock alternatives and notify-me flow.
- [ ] Validate all empty, loading, error and offline states.
- [ ] Perform mobile checkout conversion review.

### Definition of Done

- Search and filters remain fast with a realistic catalog.
- Merchandising rules are admin-manageable and do not compromise price integrity.

---

## Workstream E7 — Admin Governance, RBAC and Auditability — ⬜ Delivery Phase 7 pending

**Goal:** Ensure staff access follows least privilege and every sensitive action is traceable.

### Tasks

- [ ] Define roles: super admin, catalog manager, order manager, support, marketing and finance.
- [ ] Implement permissions/policies for each Filament resource/action.
- [ ] Require admin 2FA and secure recovery process.
- [ ] Add admin session timeout and login alerts.
- [ ] Add audit log for authentication, product price/stock, order status, payment/refund, coupon and settings changes.
- [ ] Capture actor, before/after values, reason, timestamp and request metadata safely.
- [ ] Require reason/confirmation for destructive or financial actions.
- [ ] Add soft-delete/restore policies for applicable records.
- [ ] Add protected exports with authorization and PII controls.
- [ ] Add bulk-operation safeguards and tests.
- [ ] Separate customer/admin authentication boundaries where appropriate.

### Definition of Done

- A staff member can access only the minimum capabilities assigned to their role.
- Financial/catalog changes are attributable and auditable.

---

## Workstream E8 — Security, Privacy and Compliance Hardening — ⬜ Delivery Phase 12 pending

**Goal:** Complete a production-focused security and privacy pass.

### Tasks

- [ ] Perform current OWASP Top 10 and Laravel-specific threat review.
- [ ] Review authorization/IDOR for every customer and admin endpoint/Livewire action.
- [ ] Add strict validation and rate limits to all state-changing actions.
- [ ] Review CSP for Razorpay and production assets; remove unsafe directives where possible.
- [ ] Add secure cookie, trusted proxy, HTTPS, HSTS and production debug checks.
- [ ] Add secret-management and rotation procedure; ensure no credentials exist in Git history/files.
- [ ] Add upload MIME, size, malware-scanning and private/public storage policies.
- [ ] Add bot/spam protection for registration, login, contact, review and comments.
- [ ] Add PII classification, retention and deletion/anonymization policy.
- [ ] Implement account data export and account deletion workflow.
- [ ] Add privacy/cookie consent appropriate to actual tracking technologies.
- [ ] Review legal pages: terms, privacy, returns, shipping, warranty and cancellation.
- [ ] Add dependency and secret scanning to CI.
- [ ] Conduct an independent penetration test before live launch.

### Definition of Done

- No unresolved critical/high security issue.
- Privacy and retention workflows are documented and testable.
- Pen-test findings are resolved or formally accepted by the owner.

---

## Workstream E9 — Performance, Scalability and Resilience — ⬜ Delivery Phase 13 pending

**Goal:** Meet defined service levels under realistic traffic and failure scenarios.

### Tasks

- [ ] Set measurable SLOs for uptime, latency, error rate and checkout success.
- [ ] Profile homepage, shop, product, cart, checkout and admin queries.
- [ ] Eliminate N+1 queries and add missing indexes based on query plans.
- [ ] Configure Redis for production cache, sessions and queues if approved.
- [ ] Add cache invalidation tests and stampede-safe strategy for hot data.
- [ ] Optimize images, responsive variants, WebP/AVIF and lazy loading.
- [ ] Define CDN/object-storage strategy for media.
- [ ] Optimize frontend bundles and Core Web Vitals.
- [ ] Add queue separation/priorities for payments, transactional mail and marketing jobs.
- [ ] Add timeouts, retries and circuit-breaker-like handling around external services.
- [ ] Run load tests for catalog browsing, cart and checkout.
- [ ] Run concurrent inventory/payment tests.
- [ ] Define graceful degradation for mail, cache, queue, Razorpay and carrier outages.

### Definition of Done

- Agreed load test passes without overselling, duplicate orders or unacceptable latency.
- Critical external-service failures are recoverable and observable.

---

## Workstream E10 — Observability and Production Operations — ⬜ Delivery Phase 14 pending

**Goal:** Make production failures visible, diagnosable and recoverable.

### Tasks

- [ ] Configure structured application logs with request/correlation IDs.
- [ ] Redact passwords, tokens, payment signatures and sensitive PII from logs.
- [ ] Integrate error monitoring and release tracking.
- [ ] Add application, database, queue, cache and external API metrics.
- [ ] Add alerts for error spikes, queue backlog, failed jobs, payment mismatch and low stock.
- [ ] Add uptime/health endpoints with safe dependency checks.
- [ ] Create dashboards for orders, payments, refunds and notification delivery.
- [ ] Configure automated encrypted database and media backups.
- [ ] Define backup retention and off-site storage.
- [ ] Perform and document restoration drill.
- [ ] Create runbooks for payment incidents, queue failure, DB outage, rollback and secret compromise.
- [ ] Define on-call contacts and incident severity levels.

### Definition of Done

- A simulated production incident is detected, investigated and recovered using documented runbooks.
- Backup restoration is proven, not merely configured.

---

## Workstream E11 — CI/CD, Infrastructure and Deployment Preparation — ⬜ Delivery Phase 15 pending

**Goal:** Establish repeatable, secure and low-risk releases.

### Tasks

- [ ] Define development, staging and production environments.
- [ ] Create CI gates for PHP style/static analysis, tests, frontend build, dependency audit and secret scan.
- [ ] Add browser E2E tests for critical user journeys.
- [ ] Add deployment pipeline with artifact/version tracking.
- [ ] Add safe migration strategy and pre-deployment backup.
- [ ] Configure cPanel/shared-host-compatible PHP, scheduler and cron-driven queue execution without assuming SSH or persistent workers.
- [ ] Configure environment secrets outside the repository.
- [ ] Add zero/low-downtime deployment and rollback procedure.
- [ ] Add post-deploy smoke tests.
- [ ] Add staging Razorpay webhook and mail testing.
- [ ] Restrict and protect production admin access.
- [ ] Document DNS, TLS, storage, cron, queues and scaling procedure.

### Definition of Done

- A release can be deployed to staging and production, smoke-tested and rolled back through documented automation.

---

## Workstream E12 — QA, Accessibility, Compatibility and Release Candidate — ⬜ Delivery Phase 16 pending

**Goal:** Validate the complete system against acceptance criteria before launch.

### Tasks

- [ ] Complete unit, feature, integration, Livewire and policy test coverage for critical paths.
- [ ] Add E2E flows for register/login, search, cart, coupon, checkout, payment, tracking, review/comment and refund.
- [ ] Test duplicate submissions, slow network and browser refresh during payment.
- [ ] Test Chrome, Firefox, Safari and Edge at supported versions.
- [ ] Test common Android/iOS viewport sizes.
- [ ] Perform WCAG 2.2 AA audit: keyboard, focus, labels, errors, contrast and screen reader basics.
- [ ] Validate SEO metadata, structured data, canonical URLs, sitemap and robots in staging.
- [ ] Validate transactional email rendering and deliverability (SPF/DKIM/DMARC).
- [ ] Validate invoice, tax, shipping and return workflows with business owners.
- [ ] Execute regression checklist for admin and storefront.
- [ ] Complete user acceptance testing and record sign-off.
- [ ] Freeze release candidate and resolve all blocker/critical defects.

### Definition of Done

- No blocker/critical defects and no unresolved high-risk defect.
- Business owner signs off the release candidate.

---

## Workstream E13 — Production Launch and Post-launch Stabilization — ⬜ Delivery Phase 18 inactive

**Goal:** Launch safely and stabilize the platform with measured monitoring.

### Tasks

- [ ] Complete production readiness checklist and go/no-go review.
- [ ] Confirm live Razorpay keys/webhook secrets and make a controlled low-value live transaction.
- [ ] Confirm email domain authentication and delivery.
- [ ] Confirm backups, monitoring, alerts, cron-driven queue execution and scheduler.
- [ ] Seed/import verified production catalog and content.
- [ ] Verify legal pages, contact details, GST/shipping/return configuration.
- [ ] Deploy the approved release and run production smoke tests.
- [ ] Monitor payment, order, queue and application errors closely during launch window.
- [ ] Validate live payment reconciliation and refund test.
- [ ] Maintain stabilization backlog for non-blocking issues.
- [ ] Conduct post-launch review after the agreed observation period.

### Definition of Done

- Production transactions, notifications, fulfillment and monitoring operate normally through the stabilization window.
- Handover and operational ownership are formally accepted.

---

# C. Production Release Gates

The platform is **production-ready only when every gate below is green**.

| Gate | Required result | Status |
|---|---|---|
| Functional | All approved storefront/admin flows pass | ⬜ |
| Payments | Live-like payment, replay protection, refunds and reconciliation verified | ⬜ |
| Inventory | Concurrent checkout cannot oversell | ⬜ |
| Security | No unresolved critical/high issue; authorization audit and pen test complete | ⬜ |
| Privacy/Legal | Approved policies, consent, retention and account deletion/export | ⬜ |
| Quality | Automated + E2E + manual regression pass | ⬜ |
| Accessibility | WCAG 2.2 AA review completed; critical issues fixed | ⬜ |
| Performance | Agreed load and Core Web Vitals targets pass | ⬜ |
| Reliability | Queue retries, idempotency and outage behavior verified | ⬜ |
| Operations | Monitoring, alerts, runbooks and on-call ownership ready | ⬜ |
| Backups | Encrypted backups configured and restore drill passed | ⬜ |
| Deployment | Staging/prod deploy and rollback procedure proven | ⬜ |
| Business UAT | Owner acceptance and launch approval recorded | ⬜ |

---

# D. Delivery-order authority

This inventory does not define execution order. The complete workstream-to-delivery crosswalk and dependency rationale are maintained in `tasks/CANONICAL_PHASE_SEQUENCE.md`; live completion status is maintained in `tasks/MASTER_PROJECT_TRACKER.md`.

Delivery Phases 0–5 are complete and accepted. Pending work proceeds sequentially through canonical Delivery Phases 6–18. Capability workstreams may contribute to more than one delivery phase, but their E-series IDs never replace delivery phase numbers and no production release gate may be bypassed.

---

# E. Decision ownership

The original review surfaced infrastructure, media, mail, shipping, tax, returns, notifications, search, RBAC, analytics and performance decisions. Resolved decisions are recorded in the master tracker and phase evidence; unresolved decisions belong to the pre-activation contract of their canonical owning delivery phase rather than to the historical E-series number.

The immediate pre-activation decisions for Delivery Phase 6 are recorded in `tasks/CANONICAL_PHASE_SEQUENCE.md`. Agent 10 and Delivery Phase 18 remain inactive unless explicitly activated after Delivery Phase 17 acceptance.
