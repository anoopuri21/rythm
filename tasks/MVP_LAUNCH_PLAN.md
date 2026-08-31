# MVP Launch Track — Short Practical Delivery Plan

**Status:** ACTIVE — manual delivery only
**Branch:** `rhythm-uat`
**Auto Mode:** PAUSED
**Deployment:** Not authorized in this plan; Phase 18 remains separately gated.

## Goal

Deliver the smallest honest working e-commerce demo/release candidate quickly, without starting non-essential enterprise work. Do not call it production-ready until the mandatory owner/runtime gates pass.

## Active launch phases

### MVP-1 — Core safety and working flows

Use current Phase 12 work only for launch blockers:

- Authentication, customer ownership, cart, checkout, order, payment callback/webhook and inventory integrity.
- Basic security headers, CSRF/throttling, no secrets in Git/logs, and production debug disabled.
- Keep returns, tax, warranty, marketing and other legally sensitive behavior disabled unless explicitly approved.
- Close only evidence-backed critical/high blockers; do not redesign unrelated modules.

**Pass when:** focused/full PHP checks, dependency/security checks and targeted route/action checks pass; no unresolved critical/high blocker.

### MVP-2 — Practical performance smoke

- Production frontend build passes.
- Homepage, shop/search, product, cart and checkout render without blocking errors.
- Check obvious N+1/slow-query issues using existing bounded queries and realistic catalogue data.
- Keep Redis, CDN, external search, large load testing and advanced resilience work deferred unless a real blocker appears.

**Pass when:** owner sees acceptable page speed and no obvious console/overflow/error regression at the agreed viewports.

### MVP-3 — Minimum operations and release package

- Verify `APP_ENV=production`, `APP_DEBUG=false`, HTTPS/TLS, external secrets and exact MySQL configuration.
- Take a database/media backup and prove one isolated restore.
- Verify shared-host cron/queue command, storage/media access, logs and a short rollback path.
- Produce one cPanel/shared-host release package with version/commit evidence.

**Pass when:** the owner can restore, release and roll back the candidate without deleting persistent data.

### MVP-4 — Owner UAT and go/no-go

- Run critical customer journeys: browse/search, product/variant, cart, checkout, test payment, order status, account and admin catalogue/order handling.
- Run the four agreed viewports: 1440×900, 768×1024, 390×844 and 360×800.
- Record console errors, overflow, accessibility blockers, broken links and payment/order/inventory outcomes.
- Agent 0 reviews the redacted evidence and records `GO`, `NO-GO` or `BLOCKED`.

**Pass when:** no release-blocking defect remains and all owner/runtime evidence is attached.

## Explicitly deferred until after delivery

Keep these in the future backlog unless a launch-blocking defect requires them:

- Full OWASP penetration test, CSP nonce/strict-dynamic migration and advanced threat modelling.
- Account export/deletion, detailed retention/anonymization policy and consent tooling until legal/product decisions are supplied.
- Redis/CDN/object storage, external search, large-scale load/concurrency testing and advanced circuit breakers.
- Full observability dashboards, multi-region/disaster recovery, automated CI/CD and zero-downtime deployment.
- Extended browser/device matrix, visual regression infrastructure and non-critical accessibility polish.
- Analytics/marketing automation, SMS/WhatsApp, price-drop/abandoned-cart campaigns, gift cards and other growth features.

## Non-negotiable launch blockers

These cannot be deferred or described as production-ready without evidence:

- Payment/order/inventory integrity or an unresolved critical/high security issue.
- Missing owner-approved legal/tax/return/warranty wording where the page or feature requires it.
- Missing HTTPS, production secrets/configuration, backup/restore proof or rollback path.
- Failed owner UAT, broken critical journey, exposed customer data or a destructive migration risk.

## Working rule

One short phase at a time. Fix only the blocker in front of us, verify it, commit/push it, then move to the next MVP gate. No Auto Mode activation, deployment, Phase 18 or Agent 10 work is implied by this plan.
