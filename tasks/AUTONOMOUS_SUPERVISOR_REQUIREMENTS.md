# Rythme Autonomous Supervisor — Requirements Contract

**Owner:** Project owner
**Governance reviewer:** Agent 0 — Project Lead
**Status:** COMPLETE — AUTONOMOUS SUPERVISOR ACTIVE THROUGH PHASE 17; PHASE 18 EXCLUDED
**Recorded:** 27 August 2026
**Target authorization:** Canonical Phases 6A and 8–17; Phase 18/deployment excluded

## 1. Purpose

Build a durable Autonomous Supervisor that operates as the project owner's day-to-day proxy inside Arena sessions. It must understand the canonical plan, choose the next safe task, supervise Agent 0 and specialists, recover from routine environment/tool failures, preserve evidence and resume from a versioned checkpoint after a transport or session interruption.

“Human-like thinking” is an operating-quality goal, not a measurable 99% guarantee. The implementable requirement is evidence-first judgment with explicit assumptions, confidence, risk, reversibility and decision records.

## 2. Platform boundary

- Runtime model: Arena plus resumable repository checkpoints.
- Arena cannot continue after the active session closes and cannot guarantee an always-on background worker.
- A stopped, timed-out or compacted session must therefore resume from committed state rather than memory alone.
- No external always-on service is in scope.
- Shared-host compatibility, no Docker/Podman and no persistent-worker assumptions remain binding.
- The Supervisor must never claim that it continued while no Arena session was active.

## 3. Governance and authority

### 3.1 Position

- The Supervisor sits above Agent 0 as owner proxy, priority controller and governance auditor.
- Agent 0 remains sole technical completion/sign-off authority and source-of-truth integrator.
- Specialists cannot self-approve completion.
- Team changes require an evidence-backed Supervisor proposal and Agent 0 approval. Every accepted change records purpose, scope, capability, overlap and rollback.

### 3.2 Autonomous authority

The Supervisor may autonomously:

- Read/audit the repository and project evidence.
- Prioritize approved work and generate chunk plans.
- Assign, reassign and engage active specialists through Agent 0.
- Direct code, documentation, tests and isolated migrations.
- Approve safe technical choices consistent with locked requirements.
- Run non-destructive UAT operations where access exists.
- Require review, reject unsupported completion and reopen failed work.
- Commit and push completed, tested chunks to `rhythm-uat`.
- Recover missing disposable runtimes/dependencies outside the repository.
- Continue to the next eligible authorized chunk without routine owner confirmation.

### 3.3 Human-gated or forbidden actions

The Supervisor must stop for:

- Phase 18, Agent 10 activation, production deployment or launch.
- Real payment/refund/money movement or live gateway operation.
- Credential creation, disclosure, rotation or privileged secret access.
- Irreversible deletion, destructive persistent-UAT/production database work or unreviewed data overwrite.
- Publication of legal, tax, shipping, return, warranty or customer-right promises without approved professional/business evidence.
- A security incident requiring credential or production action.
- A genuinely ambiguous business decision with material customer/financial consequences.

Missing approval remains `BLOCKED`; it may never be inferred, fabricated or converted to completion.

## 4. Scope and stop conditions

- Authorized delivery horizon: finish Phase 6A, then canonical Phases 8–17 in order.
- Phase 18 remains inactive until the owner explicitly starts deployment.
- Existing phase dependencies and mandatory gates may not be skipped.
- The Supervisor stops only when all authorized gates pass, evidence is current, critical blockers are zero, the working tree is clean and the active branch is pushed; or when a mandatory human gate is reached.
- Phase 17 may result in `NOT READY` or `BLOCKED`; it must not force a production-ready verdict.

## 5. Decision model

Use conservative, evidence-first judgment in this order:

1. Apply explicit owner decisions and current canonical sources.
2. Preserve safety, security, data integrity and truthful customer behavior.
3. Prefer reversible, idempotent and shared-host-compatible choices.
4. Check dependencies and acceptance criteria before scheduling work.
5. Compare options on correctness, evidence, owner value, risk, time and reversibility.
6. Record assumptions and confidence for material decisions.
7. Escalate only when evidence cannot safely resolve a mandatory gate.

Conflicting documents are resolved by authority and recency: current owner direction, Master Tracker, canonical phase sequence, approved phase contracts, then legacy documents. A conflict must be recorded and stale automation must not execute until corrected.

## 6. Persistent state requirements

The build must provide compact, versioned machine-readable state containing at least:

- Schema version and project identity.
- Active branch, local HEAD and last verified remote HEAD.
- Supervisor lifecycle state: inactive, planning, executing, recovering, blocked or complete.
- Current phase/chunk/task and dependency list.
- Assigned accountable/primary/reviewer agents.
- Attempt number, action class and retry budget.
- Last verified action, evidence references and next safe action.
- Working-tree expectation and pending commit/push state.
- Open blockers, human gates, risks and decisions.
- Test/build/migration/audit gate outcomes with timestamps and commit identity.
- Resume token/checkpoint identity without secrets.

State changes must be atomic and schema-validated. Operational summaries are committed; disposable logs, raw outputs, media and secrets are not.

## 7. Planner and execution requirements

- Read the Master Tracker, canonical sequence, current phase contract, roster, changelog and Git state before choosing work.
- Never use legacy `tasks/tasks.json` as current authority when it conflicts with canonical governance.
- Produce bounded chunks with deliverables, owners, dependencies, risks, gates and rollback.
- Allow independent safe tasks to proceed while another task waits on owner UAT.
- Do not repeat completed work unless evidence is stale, regression is found or scope changed.
- Before every write, verify branch, clean/expected diff and idempotency strategy.
- Every completed code/governance chunk requires applicable tests, diff checks, commit, push attempt and branch/hash/push report.
- Persistent UAT must never be targeted by destructive automated tests.

## 8. Failure, timeout and recovery policy

Retries are risk-based:

- Read-only/reversible operation: up to three bounded attempts.
- Disposable runtime/dependency restoration: up to three attempts with integrity checks.
- Idempotent writes: one retry only after verifying the first attempt's actual outcome.
- Commit/push: inspect Git state and remote identity before retrying; never duplicate a commit blindly.
- Non-idempotent writes, imports or migrations: never auto-retry until post-state proves retry safety.
- Destructive, financial, credential and production actions: zero automatic retries.

For Arena busy/timeout/transport failure:

1. Assume outcome unknown, not failed.
2. Inspect repository, process and remote state.
3. Reconcile what actually completed.
4. Save a checkpoint before continuing.
5. Use one safe alternate tool/method when the original path repeatedly fails.
6. Mark `BLOCKED` only after allowed retries/fallbacks are exhausted or a mandatory gate is reached.

The Supervisor must detect and prevent duplicate execution caused by a delayed tool response.

## 9. Security and secrets

- Secrets are environment-only and never stored in Git, state, logs, prompts, fixtures or reports.
- Missing secrets become a human gate; placeholders may be documented but never presented as real credentials.
- Uploaded files remain temporary and must never be committed.
- Existing dependency, authorization, OWASP, audit and provenance controls remain binding.
- Any generated command must avoid exposing secrets in shell history or process output where feasible.

## 10. Evidence and communication

Each checkpoint must report:

- Project Lead context.
- Supervisor lifecycle status.
- Activated specialist(s).
- Completed output and exact evidence.
- Tracker/state update.
- Branch, commit and push status when applicable.
- Next safe action or exact blocker.

Routine transport checkpoints may ask the owner to type `continue`; this is not approval. Owner contact is reserved for mandatory gates or critical unresolved incidents.

## 11. Compatibility audit findings to remediate during build

The existing automation cannot be reused unchanged:

- `automation/task-agent.mjs` and `automation/config.json` enforce stale branch `feature/dev` instead of `rhythm-uat`.
- `tasks/tasks.json` also points to stale branch/workflow and is not the canonical tracker.
- `docs/AGENT_RULES_STRICT.md` contains superseded Filament, palette, branch and source-of-truth rules.
- `tasks/AUTO_MODE_PROTOCOL.md` previously contained stale current-phase text; its current phase and execution horizon must stay reconciled with the Master Tracker and canonical sequence.

Build Chunk 0 must quarantine or update these conflicts before any automated write/commit behavior is enabled.

## 12. Build acceptance criteria

The Supervisor is not `ACTIVE` until all of the following pass:

1. Requirements and authority matrix reviewed against current canonical governance.
2. Versioned state schema and validator pass valid/invalid fixtures.
3. Planner chooses the correct next eligible task from real project state.
4. Branch and persistent-data guards reject unsafe scenarios.
5. Recovery simulations pass for timeout, unknown outcome, missing `/tmp` dependencies, failed test, interrupted commit and failed push.
6. Retry tests prove non-idempotent writes are not duplicated.
7. Team proposal/Agent 0 approval workflow is evidenced.
8. Mandatory human gates cannot be bypassed.
9. Bootstrap and resume runbooks are copy-safe.
10. Agent 0 independently accepts activation evidence and records the activation commit.

Until then, status remains `BUILDING` or `QA`; no Supervisor-driven project progress may be claimed.

## 13. Planned build chunks

0. Reconcile stale automation/governance conflicts and freeze authoritative inputs.
1. Define state schema, validator, atomic checkpoint writer and fixtures.
2. Implement read-only project auditor and dependency-aware next-task planner.
3. Implement risk-classified action/retry/recovery controller.
4. Implement Agent 0 assignment, review and team-change proposal protocol.
5. Implement evidence/gate evaluator and tracker reconciliation.
6. Add bootstrap/resume/status commands and copy-safe runbook.
7. Run failure simulations, security review and independent QA.
8. Activate only after Agent 0 acceptance; then resume Phase 6A from the verified checkpoint.
