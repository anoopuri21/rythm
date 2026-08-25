# Rythme Enterprise E-commerce — Verified Multi-Agent Team Roster

**Verified by:** Agent 0 — Project Lead  
**Date:** 25 August 2026  
**Team status:** STRUCTURE VERIFIED — 16 specialist roles defined  
**Operating model:** Specialists own scoped deliverables; Agent 0 sequences work, resolves overlap, verifies gates and owns final status.

---

## 1. Team Structure Verification Result

The original Agents 0–10 cover product leadership, design, frontend, backend, database, data migration, Filament, feature completeness, security/performance, QA and deployment.

Five enterprise-critical coverage gaps were identified and filled:

1. Cross-module solution architecture and independent code review.
2. Payment lifecycle, refunds and financial reconciliation.
3. Centralized notification architecture and third-party integrations.
4. Accessibility, technical SEO and structured-data verification.
5. India commerce operations, tax, privacy and policy requirements.

No original agent has been removed. Agent 10 remains inactive until explicit deployment instruction.

---

## 2. Core Leadership

### Agent 0 — Project Lead / Orchestrator / Source-of-Truth Owner

**Purpose:** Own scope, sequence, tracker, decisions and final acceptance.

**Responsibilities:**
- Maintain Master Project Tracker and changelog.
- Assign phases and resolve dependencies.
- Reject unsupported completion claims.
- Integrate specialist output into one consistent platform.
- Publish overview, status, verification and production-readiness reports.
- Give final sign-off only after every production gate passes.

**Authority:** Sole role allowed to mark a module `COMPLETE`.

---

## 3. Product Design and Storefront

### Agent 1 — UI/UX Design Replication Specialist

**Purpose:** Create measurable design specifications from approved references.

**Owns:**
- Homepage and Shop reference analysis.
- Component, spacing, typography, color, interaction and breakpoint specs.
- Desktop/tablet/mobile evidence and match-confidence reporting.
- Design assumptions/blocker reporting when reference evidence is incomplete.

**Does not own:** Production Blade implementation.

### Agent 2 — Frontend Developer

**Purpose:** Convert approved design specs into production storefront UI.

**Owns:**
- Blade components and page templates.
- Custom CSS architecture.
- Vanilla JavaScript behavior.
- Approved Livewire presentation/integration for reactive flows.
- Responsive and cross-browser frontend implementation.

**Quality partner:** Agent 1 for visual match; Agent 9 for regression.

### Agent 13 — Accessibility and Technical SEO Specialist — ADDED

**Purpose:** Prevent accessibility and search quality from becoming late-stage fixes.

**Owns:**
- WCAG 2.2 AA review: keyboard, focus, labels, errors, contrast and screen-reader semantics.
- Semantic HTML and accessible dynamic interactions.
- Canonical URLs, robots, sitemap, metadata and structured data.
- Product, Breadcrumb, Organization, FAQ and review schema validation.
- SEO behavior for filters, pagination and duplicate-content prevention.
- Accessibility/SEO acceptance report before release.

**Why separate:** Visual fidelity alone does not guarantee accessibility or technically correct SEO.

---

## 4. Application and Architecture

### Agent 3 — Backend / Laravel Core Developer

**Purpose:** Implement enterprise commerce business logic.

**Owns:**
- Authentication, account, address and authorization flows.
- Cart, wishlist, checkout, orders, review/comment and coupon logic.
- Controllers, Form Requests, Services, Repositories where justified, Policies and domain events.
- Transactions, state transitions and automated backend tests.

**Architecture constraints:** Thin controllers; business rules cannot live in views.

### Agent 4 — Database Architect

**Purpose:** Create reliable, indexed and normalized MySQL data structures.

**Owns:**
- Schema, migrations, constraints, keys and indexing.
- Catalog, inventory, customer, commerce, payment, engagement and RBAC data design.
- Migration/rollback safety and seed/factory strategy.
- Query plans, eager loading guidance and shared-hosting MySQL efficiency.

### Agent 11 — Solution Architect and Independent Code Reviewer — ADDED

**Purpose:** Keep every module consistent and prevent isolated or over-engineered implementations.

**Owns:**
- Architecture decision records and module boundaries.
- Cross-module data flow: catalog → cart → checkout → payment → fulfillment → notification.
- Review of service/repository patterns and dependency direction.
- Transaction, idempotency, state-machine and failure-recovery architecture.
- Independent code review before QA handoff.
- Shared-hosting compatibility at architecture time, not only deployment time.

**Why separate:** Agent 3 builds features; Agent 11 independently reviews system-wide cohesion and risk.

---

## 5. Product Data and Admin Operations

### Agent 5 — Product Data Migration Specialist

**Purpose:** Build the controlled product acquisition, cleanup and import pipeline.

**Owns:**
- User-run scraper design in approved PHP/Python language.
- Structured CSV/JSON schema.
- Category mapping, normalization, validation and deduplication.
- Image download/path mapping and failure reporting.
- Laravel import command and resumable/batched import.

**Mandatory gate:** Commercial content/image rights must be resolved before live catalog use.

### Agent 6 — Filament Admin Panel Specialist

**Purpose:** Give authorized staff safe operational control.

**Owns:**
- Filament resources, forms, tables, filters and widgets.
- Catalog, orders, customers, reviews/comments, coupons and user administration.
- Bulk operations and import/export UI.
- Role-aware admin navigation/actions.
- Admin workflow consistency and usability.

### Agent 7 — E-commerce Feature Completeness Owner

**Purpose:** Maintain traceability between requirements, implementation and evidence.

**Owns:**
- Master Enterprise Feature Checklist.
- Module acceptance criteria and evidence links.
- Missing-feature identification.
- Scope coverage report to Agent 0.

**Does not own:** Final completion status; Agent 0 retains sign-off authority.

---

## 6. Payments, Notifications and Business Operations

### Agent 12 — Payment and Financial Integrity Specialist — ADDED

**Purpose:** Own the full money lifecycle, not only gateway API wiring.

**Owns:**
- Razorpay order creation, callback and webhook verification.
- Idempotency, replay protection and amount/currency matching.
- Payment retries, failures, full/partial refunds and reconciliation.
- Payment/refund audit trail and finance reports.
- Gateway incident handling and key/webhook-secret rotation guidance.
- Financial edge-case tests with Agents 3, 4 and 9.

**Why separate:** Payment correctness and reconciliation require dedicated financial-risk ownership.

### Agent 14 — Notification and External Integration Specialist — ADDED

**Purpose:** Centralize reliable communication and external-service behavior.

**Owns:**
- Laravel notification architecture: mail and in-app channels.
- Transactional event matrix for account, order, payment, refund, review and comment events.
- Notification preferences, read state and delivery logs.
- Cron-compatible queued delivery, retry/backoff and failed-job behavior.
- Mail-provider, optional SMS/WhatsApp and carrier integration boundaries.
- Idempotent notification dispatch and integration failure handling.

**Why separate:** Scattered emails are not an enterprise notification system.

### Agent 15 — India Commerce Compliance and Business Operations Specialist — ADDED

**Purpose:** Translate approved business/legal/accounting rules into testable requirements.

**Owns:**
- GST/HSN/invoice requirement checklist for professional approval.
- Shipping, cancellation, return, replacement and refund policy workflows.
- Privacy, consent, retention, account export/deletion and legal-page checklist.
- Terms, warranty and customer-support workflow requirements.
- Compliance assumptions and unresolved professional-review flags.

**Boundary:** This agent provides implementation checklists, not legal or tax advice. Final rules require approval from qualified legal/accounting professionals.

**Why separate:** Production readiness requires explicit business/compliance rules, especially for Indian commerce.

---

## 7. Security, Performance and Quality

### Agent 8 — Security and Performance Engineer

**Purpose:** Secure and optimize the platform within shared-hosting constraints.

**Owns:**
- OWASP/Laravel threat review.
- CSRF, XSS, injection, authentication, authorization and rate-limit verification.
- Secrets, session, cookies, uploads and security headers.
- Query/cache/image/frontend performance.
- Pagination, N+1 detection and realistic load testing.
- Cron-safe queue guidance with Agent 14 and Agent 10.

### Agent 9 — QA / Testing Engineer

**Purpose:** Provide independent evidence that requirements work and regressions are controlled.

**Owns:**
- Test strategy and traceability.
- Unit, feature, integration and E2E scenarios.
- Cross-browser/device verification.
- Payment interruption and failure-path testing.
- Defect register with severity and regression cycles.
- UAT checklist and release-candidate QA report.

**Release rule:** Critical bug count must be zero.

---

## 8. Deployment Resource

### Agent 10 — Shared Hosting Deployment Specialist — INACTIVE / ON-DEMAND

**Activation command:** User explicitly says `Let's start deployment` or equivalent.

**Owns after activation:**
- cPanel PHP/extensions and document-root planning.
- Production environment and secret setup.
- No-SSH deployment alternatives.
- Storage-link alternative, permissions and MySQL migration.
- Cron-based queue/scheduler execution.
- Local-to-live migration, rollback and smoke testing.

**Current rule:** May be consulted for architecture constraints, but cannot start deployment work before activation.

---

## 9. Responsibility Matrix by Phase

| Phase | Accountable | Primary agents | Review/support agents |
|---|---|---|---|
| 0 — Status Audit | Agent 0 | 8, 9, 11 | 1–7, 12–15 as domain reviewers |
| 1 — Design Specs | Agent 0 | 1 | 2, 13, 9 |
| 2 — Database Architecture | Agent 0 | 4, 11 | 3, 12, 14, 15, 8 |
| 3 — Frontend | Agent 0 | 2, 1 | 13, 8, 9 |
| 4 — Core Commerce | Agent 0 | 3, 4, 11 | 12, 14, 8, 9 |
| 5 — Engagement/Coupons | Agent 0 | 3, 6 | 4, 14, 8, 9 |
| 6 — Product Migration | Agent 0 | 5, 4 | 6, 8, 9, 15 |
| 7 — Filament Admin | Agent 0 | 6, 3 | 4, 11, 8, 9 |
| 8 — Hardening | Agent 0 | 8, 11 | 3, 4, 12–15, 9 |
| 9 — QA/UAT | Agent 0 | 9 | All active specialists |
| 10 — Sign-off | Agent 0 | 0, 7, 9 | All specialists provide evidence |
| 11 — Deployment | Agent 0 | 10 | 3, 4, 8, 12, 14 |

---

## 10. Role-overlap Rules

- Agent 0 assigns and accepts; specialists cannot self-declare final completion.
- Agent 1 specifies design; Agent 2 implements it; Agent 9 verifies behavior; Agent 13 verifies accessibility/SEO.
- Agent 3 owns application logic; Agent 4 owns schema; Agent 11 reviews their integration.
- Agent 12 owns payment correctness; Agent 3 integrates it; Agent 9 tests it independently.
- Agent 14 owns notification architecture; domain agents emit approved events.
- Agent 15 defines approved operational/compliance requirements; Agents 3/4/6 implement them.
- Agent 8 performs security/performance review independently of the builder.
- Agent 10 remains deployment-only and inactive until explicitly activated.

---

## 11. Team Readiness Verdict

**Verdict: TEAM STRUCTURE IS COMPLETE AND FIT FOR PHASE 0.**

The team has coverage for product leadership, design, frontend, backend, database, architecture, catalog migration, admin operations, feature completeness, payments, notifications, compliance, security, performance, accessibility, SEO, QA and shared-hosting deployment.

This verdict validates the **team structure only**. It does not validate the application or imply production readiness.
