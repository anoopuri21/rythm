# Owner-side MVP Launch Runbook

**Purpose:** short, safe owner checklist for the client-facing e-commerce demo and later launch path.
**Language:** simple English / Hinglish
**Updated:** 31 August 2026
**Branch:** `rhythm-uat`
**Current state:** Phases 0–11 accepted; Phase 12 minimum launch safety is in progress.
**Auto Mode:** PAUSED by owner.
**Deployment / Phase 18 / Agent 10:** INACTIVE.
**Companion plan:** `tasks/MVP_LAUNCH_PLAN.md`

## Rules that always apply

1. Complete one phase, send its evidence, and wait for Agent 0 acceptance before the next phase.
2. Never run `migrate:fresh`, `db:wipe`, destructive seed/import, `RefreshDatabase`, or destructive tests against persistent UAT or production.
3. Use a disposable isolated database for destructive tests. On persistent UAT, use only reviewed forward migrations and `migrate:status`.
4. Verify the exact MySQL 8 engine. MariaDB output is not a substitute.
5. Keep Composer dependencies outside the repository vendor path. Never commit `.env`, credentials, logs, dumps, customer PII, payment data or temporary catalogue/media files.
6. Live Razorpay payments/refunds, production changes, tax/return/legal enablement and destructive actions require explicit human approval.
7. Do not invent GST, HSN, shipping, return, warranty, privacy, contact, offer or customer-data wording. Keep unknown/unsupported behavior disabled.
8. Arena cannot run the owner’s PHP, MySQL, browser/accessibility or hosting checks. Owner evidence is required for those gates.

## Safe preflight before every phase

From the project root:

```text
git checkout rhythm-uat
git pull --ff-only origin rhythm-uat
git status --short
git rev-parse HEAD
```

Expected: branch is `rhythm-uat`, tree is clean, and the SHA is recorded.

For database identity, run in the owner environment and redact host, username, password, database name if needed:

```sql
SELECT VERSION() AS server_version, @@version_comment AS version_comment;
```

Use a disposable MySQL 8 database for destructive checks. For persistent UAT, take/confirm a backup first and run only reviewed commands such as:

```text
php artisan config:clear
php artisan migrate --force
php artisan migrate:status
```

### Windows CMD note

If using CMD/Cmder, use `set "NAME=value"` only for the current window and use `> file 2>&1` instead of `tee`. Clear temporary `DB_*` variables before normal application tests so they do not point tests at the wrong database.

## Evidence template

Send a short redacted pack after each phase:

```text
Phase: <12–17>
Commit SHA: <40-character SHA>
Environment: <PHP/Laravel/MySQL/Node/browser/OS versions>
Database: <isolated or persistent; exact MySQL result>
Checks: <commands and counts>
Manual result: <PASS/FAIL per item>
Known defects: <none or reference>
Safety: <no destructive persistent-UAT/production action>
Owner decision: ACCEPT / BLOCK / NEEDS FIX
```

# Phase 12 — Minimum safety and working commerce flows

**Status:** IN PROGRESS

### Owner checks

1. Pull the latest `rhythm-uat` commit and confirm the SHA.
2. Run the focused Phase 12/security checks and the full available PHP regression in isolated QA.
3. Confirm authentication, customer ownership, admin authorization and direct URL/action denial.
4. Confirm cart, checkout, order totals, payment initiation/finalization/webhook replay, inventory and coupon effects are server-authoritative and idempotent.
5. Confirm basic CSRF/throttling/security-header, secret, dependency, upload and debug-off checks.
6. Confirm invoice/order links and customer data do not expose another customer’s records.
7. Confirm only owner-approved legal/tax/privacy/content behavior is enabled.

**Pass gate:** no unresolved critical/high safety blocker; required PHP, MySQL, browser and runtime evidence is attached. Unknown legal/business rules remain disabled.

**Do not do:** live payment/refund, production migration, destructive UAT reset, invented policy content or broad enterprise hardening.

# Phase 13 — Practical performance smoke

**Status:** PENDING

### Owner checks

1. Run the production frontend build.
2. Render homepage, shop/search, product, cart and checkout with realistic catalogue data.
3. Check bounded query counts and obvious N+1/slow-query behavior; use `EXPLAIN` only in disposable QA.
4. Test viewports `1440×900`, `768×1024`, `390×844` and `360×800`.
5. Record page errors, console errors, overflow, broken images, blocking UI and obvious slow steps.
6. Do not introduce Redis, CDN, external search or a large load-testing programme just to satisfy this phase.

**Pass gate:** the critical storefront/cart/checkout flow is usable with no material performance or rendering regression.

# Phase 14 — Minimum operations

**Status:** PENDING

### Owner checks

1. Verify production environment values, external secrets, `APP_DEBUG=false`, HTTPS/TLS, secure cookies and correct MySQL configuration.
2. Take a database and media backup.
3. Restore one copy into an isolated target and verify tables, media and application access.
4. Verify cPanel/shared-host cron, queue processing, storage/media access and redacted logs.
5. Write a short rollback procedure: restore backup if needed, revert the release, and re-run safe smoke checks.
6. Confirm no command in the procedure deletes persistent UAT/production data.

**Pass gate:** owner can restore, operate and roll back the candidate safely.

# Phase 15 — cPanel/shared-host release package

**Status:** PENDING

### Owner checks

1. Freeze the approved commit SHA and create one versioned release package.
2. Exclude `.env`, credentials, logs, database dumps, customer data, temporary media, `node_modules` and the workspace `vendor` copy.
3. Include the environment checklist, reviewed forward migrations, storage/media steps, cron/queue steps and rollback notes.
4. Confirm the target PHP/Laravel/MySQL versions and public path.
5. Run a staging/shared-host smoke test for homepage, search, cart, checkout, test payment, order and admin essentials.
6. Record package hash/version and any host-specific limitation.

**Pass gate:** owner can use the package and checklist without guessing or putting secrets in Git.

# Phase 16 — Focused client UAT

**Status:** PENDING

### Owner checks

Test with approved test data and test payment mode only:

1. Browse categories, search, open product/variant and verify stock/price.
2. Add/update/remove cart items and reach checkout.
3. Complete approved test payment; verify one order, correct status, invoice and inventory result.
4. Check account/order history and customer ownership boundaries.
5. In admin, verify essential catalogue and order actions with role restrictions.
6. Test retry, refresh, duplicate click, failed payment and safe cancellation paths.
7. At `1440×900`, `768×1024`, `390×844` and `360×800`, record overflow, clipping, keyboard/accessibility blockers, broken links, console errors and visible data issues.
8. Record every failure with URL, viewport, steps, expected result and actual result. Do not include secrets or real customer/payment data.

**Pass gate:** browse, search, cart, checkout, payment, order, invoice, account and essential admin journeys pass with no release-blocking defect.

# Phase 17 — Evidence review and go/no-go

**Status:** PENDING

Agent 0 reviews the evidence from Phases 12–16 and checks:

- payment/order/inventory correctness;
- authorization and customer-data isolation;
- approved legal/tax/privacy/content boundaries;
- PHP/MySQL/browser/runtime evidence;
- performance smoke results;
- HTTPS/configuration, backup/restore, logs, queue/cron and rollback;
- release package, UAT results and unresolved defect list.

Record exactly one decision: `GO`, `NO-GO` or `BLOCKED`, with owner actions and residual risks. A `GO` here is a readiness decision, not a deployment command.

# Phase 18 — Deployment boundary

**Status:** INACTIVE

Do not deploy from this runbook. Phase 18 and Agent 10 require a separate explicit owner deployment command after Phase 17 acceptance. Live payment/refund and production changes remain human-gated.

## Future backlog — not MVP blockers

Unless a real launch blocker appears, defer:

- full penetration testing and advanced threat modelling;
- CSP nonce/strict-dynamic migration;
- account export/deletion, retention tooling and consent workflows awaiting legal/product decisions;
- large-scale load testing, Redis/CDN/object storage, external search and advanced resilience;
- full observability dashboards, multi-region recovery, broad CI/CD and zero-downtime deployment;
- extended browser/device matrices, visual regression and non-critical accessibility polish;
- Analytics/marketing automation, SMS/WhatsApp, abandoned-cart/price-drop campaigns, gift cards and other growth features.

These are deferred, not silently marked complete. They must not weaken the mandatory safety, financial, backup, rollback, UAT or go/no-go gates above.
