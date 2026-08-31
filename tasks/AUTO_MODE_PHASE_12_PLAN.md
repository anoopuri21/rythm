# Manual Execution Plan — Phase 12 MVP Safety, Authorization, Payment/Order and Privacy Blockers

**Status:** IN PROGRESS — Auto Mode reactivated by owner command on 31 August 2026; Chunk 1 closed
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

- Harden safe application defaults and document environment-only production requirements.
- Add or repair CI checks for dependencies, secrets and security without embedding credentials.
- Keep live gateway, credential rotation and production actions human-gated.

### Chunk 3 — MVP privacy, legal and accessibility blockers

- Record the minimum PII/data-flow and customer-data ownership boundaries needed for the enabled demo flows.
- Obtain owner/professional decisions for any legal, tax, return, warranty or privacy text required by enabled behavior; keep unknown behavior disabled.
- Do not implement account deletion/export, detailed retention tooling or a consent banner until exact retention/legal behavior and actual tracking use are approved.
- Remediate only evidence-backed critical accessibility blockers and add regression coverage; defer non-blocking polish.

### Chunk 4 — independent Phase 12 qualification

- Run applicable static/automation suites, PHP/Composer checks in the disposable external QA copy and owner-side runtime/UAT gates.
- Require independent review and a redacted evidence pack.
- Mark Phase 12 `COMPLETE` only after no unresolved critical/high blocker, required privacy/legal traceability, critical accessibility evidence and payment/order/authorization gates pass. Full penetration testing and advanced hardening are not MVP prerequisites.

## Stop conditions

Auto Mode pauses for a credential or production action, destructive persistent-UAT operation, unresolved legal/business rule, missing external evidence, or a critical/high security finding that cannot be safely resolved with repository evidence. Phase 18/deployment remains inactive until a separate explicit deployment command after Phase 17.
