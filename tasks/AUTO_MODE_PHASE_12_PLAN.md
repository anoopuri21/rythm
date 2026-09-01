# Manual Execution Plan — Phase 12 MVP Safety, Authorization, Payment/Order and Privacy Blockers

**Status:** IN PROGRESS — Auto Mode reactivated by owner command on 31 August 2026; Chunks 1–3 closed; Chunk 4 qualification and the AS-H011 legal-text decision remain
**Canonical phase:** 12
**Branch:** `rhythm-uat`
**Execution mode:** Auto Mode autonomous execution; deployment, Phase 18 and Agent 10 remain separately gated
**Accountable:** Agent 0
**Primary specialists:** Agents 8, 3, 4, 9, 11, 13 and 15
**Deployment:** inactive; Phase 18 and Agent 10 remain separately gated

## Authority and scope

This plan follows `tasks/CANONICAL_PHASE_SEQUENCE.md`, `tasks/MASTER_PROJECT_TRACKER.md`, `tasks/OWNER_SIDE_PHASEWISE_EXECUTION_RUNBOOK.md` and the MVP launch track. It covers only launch-blocking safety, authorization, payment/order integrity, privacy boundaries and evidence-backed accessibility defects. Manual execution may make safe, reversible technical corrections, but it may not invent legal text, change financial/customer-rights rules, expose or rotate credentials, perform destructive persistent-UAT work, or activate deployment. Advanced hardening and enterprise completeness remain future backlog.

## Chunk sequence

### Chunk 0 — baseline threat model and gate inventory

**Status:** COMPLETE — baseline recorded 30 August 2026

- Inventory web routes, controllers, policies, middleware, Filament resources, Livewire actions, validation and ownership checks.
- Review CSRF, throttling/rate limits, file uploads, storage visibility, headers, CSP/Razorpay origins, HTTPS/HSTS, secure cookies, trusted proxies and production debug settings.
- Run read-only repository/history secret checks without copying secrets into reports.
- Reconcile dependency/security scan entry points and identify what requires owner runtime or external tooling.
- Build the initial OWASP/Laravel threat model, authorization matrix, privacy data map and accessibility baseline.

**Deliverables:** `docs/phase12-security-threat-model.md`, `docs/phase12-authorization-matrix.md`, `docs/phase12-privacy-data-map.md`, `docs/phase12-accessibility-baseline.md`.

**Gates:** clean diff checks; no critical audit finding; no secret values retained; no persistent destructive operation.

### Chunk 1 — safe authorization and input-boundary remediation

**Status:** COMPLETE — closed 31 August 2026 under reactivated Auto Mode; review/Q&A write limits, explicit order-mutation auth boundaries, checkout address ownership and CSP origin/framing tightening were already in place, and the remaining customer-facing route/Livewire/controller action-boundary sweep found every boundary enforced with no new defect. The sweep is locked by `tests/automation/security-phase12-boundaries.test.mjs` (see `docs/phase12-authorization-matrix.md` closure record); PHP runtime confirmation stays with Chunk 4 owner-side qualification

- Correct only evidence-backed IDOR, authorization, validation, CSRF, throttling and upload-boundary defects.
- Add focused regression coverage for every corrected boundary.
- Preserve inactive-by-default returns/tax/legal behavior and existing ownership controls.

### Chunk 2 — security configuration and dependency/secret scan contract

**Status:** COMPLETE — closed 31 August 2026 under Auto Mode; headers/CSP/session/app defaults re-reviewed, tracked-tree secret and artifact scans passed, dependency pins verified, and the contract is locked by `tests/automation/security-phase12-config.test.mjs`

- Safe application defaults re-verified: env-driven `APP_DEBUG` defaulting to `false`, secure-by-default session cookie attributes, production-only HSTS and a bounded CSP limited to the approved Razorpay/fonts/media origins.
- Read-only `git grep` secret scans found no private keys, Razorpay/AWS/Stripe-style keys or hardcoded credential assignments in the tracked tree; `.env.example`/`.env.production.example` ship empty secret values and production-safe flags; no `vendor/`, `node_modules/` or `.env` is tracked. Supervisor test fixtures that deliberately embed a bare key marker to prove rejection remain the recorded allowlist.
- Locked stack pins re-verified: Laravel `13.24.0` exact, PHP `^8.3`, npm `lockfileVersion` 3.
- Live gateway use, credential rotation, production configuration and any real secret remain human-gated.

#### Environment-only production requirements

Production values must come exclusively from the environment on the host (cPanel variables or a server-side `.env` that is never committed):

- `APP_ENV=production`, `APP_DEBUG=false`, a host-generated `APP_KEY`, exact MySQL 8 credentials, `SESSION_SECURE_COOKIE=true` behind HTTPS, and the Razorpay key/secret/webhook triple in test mode until launch approval.
- `composer audit` and `npm audit` remain owner-side pre-release gates because PHP/Composer are unavailable in Arena; Arena records the committed-tree and lockfile contract instead.
- Any rotation, live-mode payment key or production data change requires an explicit owner step and is never executed by Auto Mode.

### Chunk 3 — MVP privacy, legal and accessibility blockers

**Status:** COMPLETE — closed 31 August 2026 under Auto Mode; privacy map confirmed against enabled flows, static critical-accessibility-defect sweep clean with regression coverage added, and the legal/tax/return/warranty/privacy wording decision is recorded as human gate **AS-H011** with unknown behavior kept disabled

- The privacy data map (`docs/phase12-privacy-data-map.md`) was re-read against the enabled demo flows (accounts, addresses, cart/session, checkout, orders, payments, reviews, Q&A, wishlist, stock alerts, notifications, contact, newsletter, disabled returns) and still matches the schema/code inventory; its five owner/professional decisions remain open and untouched.
- Static accessibility sweep across every Blade view found **zero evidence-backed critical defects**: all rendered images carry `alt`, icon-only buttons keep accessible names, and the layout keeps its skip link and `main` landmark. This does not replace the Chunk 4 rendered four-viewport, keyboard and axe evidence.
- No account deletion/export/erasure route exists, returns stay disabled (`returns_enabled` default `0`), and tax stays disabled (`tax_rules_enabled` default `0`) until approved wording exists.
- Regression contract: `tests/automation/privacy-phase12-chunk3.test.mjs`.

### Chunk 4 — independent Phase 12 qualification

- Run applicable static/automation suites, PHP/Composer checks in the disposable external QA copy and owner-side runtime/UAT gates.
- Require independent review and a redacted evidence pack.
- Mark Phase 12 `COMPLETE` only after no unresolved critical/high blocker, required privacy/legal traceability, critical accessibility evidence and payment/order/authorization gates pass. Full penetration testing and advanced hardening are not MVP prerequisites.

## Stop conditions

Auto Mode pauses for a credential or production action, destructive persistent-UAT operation, unresolved legal/business rule, missing external evidence, or a critical/high security finding that cannot be safely resolved with repository evidence. Phase 18/deployment remains inactive until a separate explicit deployment command after Phase 17.
