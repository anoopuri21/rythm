# Auto Mode Programme — Canonical Delivery Phases 6–11

**Owner:** Agent 0 — Project Lead
**Authorized:** 26 August 2026
**Mode:** Continuous sequential Auto Mode through Phase 11
**Deployment:** Agent 10 and Phase 18 remain inactive

## Owner authorization

The owner authorized Agent 0 to take full execution ownership from canonical Phase 6 through Phase 11 without requesting routine confirmation at each full-phase checkpoint. Agent 0 must still enforce architecture, security, data-safety, tests, accessibility, dependency, evidence, commit and push gates. A genuine blocker, unsafe/destructive action or unavailable mandatory external gate must be reported truthfully.

If Razorpay test credentials, professional tax/legal approval or owner-side manual UAT are unavailable, Agent 0 may complete and evidence independent technical work and continue only with independent later work. The affected phase must remain `QA` or `BLOCKED`; it must not be falsely marked `COMPLETE`.

## Locked owner decisions

### Phase 6 — Controlled catalogue acquisition/import

- Scraper implementation language: **PHP**.
- Development against bounded public reference data is permitted while commercial text/image rights remain unresolved.
- Source boundary: public pages only; no authentication bypass; no CAPTCHA bypass; respectful rate limiting.
- Commercial competitor text/image rights remain a production-data gate.

### Phases 7–8 — Governance and finance

- Roles: Super Admin, Catalogue Manager, Order Manager, Support, Marketing and Finance.
- Permissions are deny-by-default and least-privilege.
- Staff TOTP 2FA is required.
- Sensitive actions require reason/confirmation and an audit trail.
- Refund actions are finance-authorized; full and partial refund/reconciliation workflows are in scope.

### Phase 9 — Notifications

- Approved channels: **transactional email only**.
- SMS, WhatsApp and the customer in-app/database notification centre are excluded from the approved Phase 9 delivery scope.
- Email delivery must still be centralized, idempotent, logged, preference/consent-aware where applicable and cron-safe.

### Phase 10 — Fulfillment, returns and tax

- Build a configurable framework without inventing legal, tax, warranty, shipping or return promises.
- Manual fulfillment, shipment/AWB/tracking, RMA, tax/HSN and state-aware structures and tests are permitted.
- Unknown rates, windows and legal text remain disabled or unpublished until qualified approval.
- No carrier integration is assumed.

### Phase 11 — Customer experience/search/merchandising

- Use a MySQL/shared-host-safe baseline without a persistent search daemon.
- Scope: weighted/typo-tolerant search where feasible, SKU/category/brand/attribute facets, recently viewed, admin-managed related/complementary rules, back-in-stock subscriptions, and truthful empty/error states.
- Gift cards, abandoned-cart marketing, price-drop promises and persistent external search services are excluded unless separately approved later.

## Workspace-capacity policy

- Never create a physical repository `vendor/` directory; use the external `/tmp/rythm-vendor` symlink arrangement only when PHP tooling is required.
- Scrape and import runs must be bounded, resumable and disk-budgeted.
- Raw responses, downloaded media, browser artefacts, generated catalogues, test databases, dependency directories and build output must remain in `/tmp` or ignored disposable paths and be deleted after evidence extraction.
- Commit only source code, small deterministic fixtures and compact evidence reports—never a competitor catalogue/media dump.
- Use generated temporary data for realistic-volume tests instead of persisting large datasets in the repository.
- Check repository/workspace size at every chunk and phase gate; stop before capacity risk.
- Persistent UAT data remains protected from destructive commands and unapproved imports.

## Sequential execution plan

| Phase | Primary outcome | Mandatory local evidence | External/bounded gate handling |
|---|---|---|---|
| 6 | Bounded PHP acquisition, normalization, validation, deduplication, media manifest and resumable Laravel import | Unit/feature tests, isolated migration/import, malformed/duplicate/resume/rate-limit tests, disk cleanup report | Commercial content/media rights remain production gate |
| 7 | Least-privilege staff RBAC, TOTP 2FA and sensitive-action auditability | Policy/resource/action tests, 2FA/recovery tests, audit immutability/redaction review, admin UAT automation | Owner recovery/manual admin UAT may remain external |
| 8 | Payment retry, full/partial refund, webhook log and reconciliation operations | Fake/provider-contract tests, replay/concurrency/mismatch/refund/reconciliation tests | Real Razorpay test-mode evidence requires credentials and may leave phase in QA |
| 9 | Central transactional-email event architecture | Event matrix tests, idempotency, redaction, retry/failure and cron-safe delivery tests | Real mail-provider delivery may remain external |
| 10 | Configurable manual fulfillment, shipment, RMA and India tax framework | State-machine, authorization, invoice/tax configuration and workflow tests | Professional tax/legal approval remains external; no policy publication |
| 11 | Shared-host-safe search, facets and bounded merchandising/CX | Realistic temporary-catalog search/performance, authorization, accessibility, SEO and responsive tests | Owner manual conversion UAT may remain external |

## Status and commit rule

- Work proceeds strictly in canonical order, with a recorded gate at each phase.
- Every phase produces or updates its plan/evidence report, master tracker, protocol and changelog.
- Every completed code/governance chunk is checked, committed and pushed to `rhythm-uat`.
- Agent 0 alone may mark a phase `COMPLETE`.
- Phase 12 and later work is outside this authorization.
