# Rythme Enterprise E-commerce — Autonomous Auto-Mode Protocol

**Owner:** Agent 0 — Project Lead  
**Status:** PAUSED — Phase 5 complete; canonical Phase 6 not activated
**Activation command:** `ACTIVATE AUTO MODE`  
**Pause command:** `PAUSE AUTO MODE`

---

## 1. State Rules

- Default state is collaborative ask-first mode.
- Auto Mode activates only when the user explicitly issues `ACTIVATE AUTO MODE`.
- Merely discussing or documenting Auto Mode does not activate it.
- `PAUSE AUTO MODE` immediately returns control to ask-first mode.
- Auto Mode pauses automatically at a genuine blocker or at the completion of a full roadmap phase.
- Agent 0 remains accountable for plan, sequencing, integration and quality in both modes.

---

## 2. Auto Mode Operating Behavior

### Full codebase ownership

On activation, Agent 0 will:

1. Read the current Master Tracker, audit report, roster and changelog.
2. Verify repository branch, commit and working-tree state.
3. Verify the external Composer vendor rule before running PHP tooling.
4. Scan relevant source, migrations, routes, views, configs and tests.
5. Determine implementation status from code and evidence rather than asking the user to restate it.

### Self-generated execution plan

- Agent 0 will generate one concrete chunk-level plan at Auto Mode start.
- Tasks must name actual deliverables rather than vague phases.
- Dependencies, owners, test gates and completion criteria must be recorded.
- After initial plan publication, ordinary task-by-task confirmation is not required.

### Autonomous decisions

Agent 0 may decide and log:

- Naming and code organization.
- Framework-conventional structure.
- Common implementation techniques.
- Minor UI details where reference measurements do not exist.
- Test organization and refactoring needed to meet approved architecture.
- Safe dependency use consistent with locked stack and dependency-audit decision.

Every material autonomous decision must be added to `tasks/TEAM_CHANGELOG.md`.

---

## 3. Genuine Blockers That Pause Auto Mode

Auto Mode may interrupt execution only for:

1. Missing design evidence where no trustworthy reference, screenshot or source is available.
2. Missing external product data or scraper output required for import work.
3. Destructive actions that would delete or overwrite working user data or code.
4. New legal/ethical risk requiring owner acknowledgment.
5. Business rules that materially change commerce behavior, financial obligations or customer rights.
6. Missing credentials/external service access required for a real integration test.
7. A platform/environment limitation that prevents a mandatory quality gate and has no safe local substitute.

A blocker report must state:

- What is blocked.
- Why autonomous inference would be unsafe.
- Exact user input/resource required.
- What independent work can continue, if any.

---

## 4. Continuous Execution

- Completing one chunk automatically starts the next eligible chunk.
- Agent 0 will not ask whether to continue ordinary approved work.
- Work continues within the current turn while tool/time/context limits allow.
- At a natural checkpoint, the response asks the user to type `continue`; this is a transport checkpoint, not an approval request.
- A phase-level summary is mandatory before Auto Mode advances to the next full roadmap phase.

---

## 5. Mandatory Checkpoint Format

```text
🤖 AUTO MODE — CHUNK [X] of [Total]
✅ Completed: [deliverables]
📂 Files Created/Modified: [files]
📊 Tracker Update: [status and overall progress]
🔜 Next Chunk: [next approved task]

⏸️ If genuinely blocked: 🚧 BLOCKER — [required input]
Otherwise: Type "continue" to proceed to the next chunk.
```

---

## 6. Non-Negotiable Quality Gates

Auto Mode changes communication frequency, not engineering quality.

Every applicable chunk must pass:

- Architecture and independent code review.
- Authorization and security checks.
- Database migration/constraint review.
- Relevant automated tests.
- Full regression suite at phase gate.
- Frontend production build for UI/assets changes.
- Composer/npm security audit for dependency changes.
- Design reference comparison for visual work.
- Accessibility/SEO checks for public UI.
- Tracker, audit evidence and changelog update.

No specialist may self-approve final completion. Agent 0 retains completion authority.

---

## 7. Workspace Dependency Rule During Auto Mode

- A physical `vendor/` directory is forbidden inside `/home/user/rythm`.
- Composer dependencies must be stored outside the workspace.
- Current session path: `/tmp/rythm-vendor`.
- Workspace may contain only the symlink `vendor -> /tmp/rythm-vendor`.
- Before any Composer/PHP task, verify that `vendor` is a symlink to the external directory.
- If external dependencies are missing, recreate the external path and run Composer from `/home/user/rythm`.
- `node_modules` and generated build directories remain disposable/excluded artifacts.

---

## 8. Safety and Scope Guardrails

- Auto Mode cannot override the locked technology stack.
- Auto Mode cannot activate deployment Agent 10 without the explicit deployment command.
- Auto Mode cannot declare production readiness before all gates pass.
- Auto Mode cannot push, publish, rotate secrets, perform live payments or alter production data without the required access and approved workflow.
- Existing secrets must never be written to source files, logs or reports.
- Uploaded files are temporary reference evidence only: never copy or commit them into the repository; retain only necessary hashes/dimensions/findings and delete staged originals immediately after inspection.
- Destructive migrations/data transformations require a backup/rollback plan and user escalation when real data is at risk.
- The active integration branch is `rhythm-uat` until the owner requests a replacement or Agent 0 records a justified branch split.
- A code-changing task is not operationally closed until applicable local gates pass, the latest code is committed, a push to the active branch is attempted, and a commit report is provided. Remote/authentication failures are blockers to push, not permission to claim it succeeded.

---

## 9. Current Auto Mode State

- **Registered:** Yes
- **Active:** No — paused automatically at the completed Phase 5 full-phase checkpoint.
- **Completed phases:** Phase 0B Stack Alignment, Phase 1 Homepage + Shop Design Specifications, Phase 2 MySQL Schema + Domain Architecture, Phase 3 Homepage + Shop Frontend, Phase 4 Accounts/Commerce, and Phase 5 Reviews/Product Q&A/Coupons (`COMPLETE`).
- **Current phase:** Phase 5 — Verified reviews, moderated product Q&A and coupons (`COMPLETE`); the canonical next phase is Phase 6 controlled catalogue acquisition/import, pending its pre-activation decisions.
- **Sequence authority:** `tasks/CANONICAL_PHASE_SEQUENCE.md` controls delivery order; `tasks/MASTER_PROJECT_TRACKER.md` controls status; enterprise-roadmap E-series IDs are capability workstreams only.
- **Execution/evidence:** `tasks/AUTO_MODE_PHASE_5_PLAN.md` and `tasks/PHASE_5_INTERACTIONS_QA.md`; all isolated gates and the owner-reported exact MySQL 8.4.3 UAT forward migrations passed.
- **Owner-locked interaction scope:** verified-purchase reviews plus moderated product Q&A; no blog comments.
- **Phase 2 MySQL evidence:** owner reported all three forward migrations completed successfully on MySQL Community Server 8.4.3 `rhythm_db`, with all migrations shown as `Ran`.
- **Automated Phase 3 evidence:** **233 tests / 811 assertions**, production build, Blade compilation, changed-PHP syntax/Pint and zero Composer/npm advisories passed after remediation.
- **Rendered Phase 3 evidence:** exact-width Homepage/Shop empty/static evidence at 1440/768/390/320 plus isolated populated Homepage/Shop evidence at 1440/390 was reviewed and accepted.
- **Visual result:** populated Homepage sections, six-across desktop Homepage products, four-across desktop Shop results, two-across 390px cards, category shortcuts, facets, sorting, pagination and responsive shared surfaces were evidenced without observed horizontal canvas expansion.
- **Evidence handling:** uploaded originals were deleted immediately after metadata and findings were recorded; no upload was committed or retained.
- **Persistent data safety:** isolated SQLite evidence did not connect to, reset or seed persistent `rhythm_db`; Phase 4 destructive suites remain isolated-only.
- **Phase 4 evidence:** full regression passed at 244 tests / 858 assertions; 17 isolated Chromium commerce page/viewport combinations at 1440/768/390/320 passed with zero axe violations, horizontal overflow or console/page errors; production build and dependency audits passed.
- **Phase 4 closed findings:** signed guest tracking/invoices, variant stock, email reverification, transactional address defaults, checkout idempotency, Razorpay checkout launch/invalid-callback safety, persisted totals and unsupported claim removal are implemented and regression-covered.
- **Deployment:** Agent 10 remains inactive; no production sign-off is implied.
