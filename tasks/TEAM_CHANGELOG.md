# Rythme Multi-Agent Team — Rule and Plan Changelog

This document is maintained by Agent 0 (Project Lead). It records approved changes to project rules, architecture direction and phase sequencing.

## 25 August 2026 — Team Operating Model Established

- **Unqualified existing implementation → Audit-first qualification** — Reason: the existing repository will be used, but no feature can inherit `COMPLETE` status without evidence under the new enterprise checklist.
- **Frontend default: Blade + custom CSS + Vanilla JS → Livewire allowed for reactive commerce flows** — Reason: user explicitly approved Livewire while retaining Blade as the primary rendering layer.
- **Database target locked to MySQL** — Reason: project stack and shared-hosting target require MySQL validation; SQLite-only success is not production evidence.
- **Project identity locked to Rythme / Rhythm Exports** — Reason: user selection.
- **Deployment planning → On-demand only** — Reason: Agent 10 remains inactive until the user explicitly requests hosting/deployment.
- **Module completion claims → Project Lead sign-off only** — Reason: unified quality and production-readiness governance.
- **Design approximation → Evidence-based replication only** — Reason: pixel-perfect claims require confirmed live-page measurements, screenshots or source evidence.
- **Persistent queue assumptions → Shared-hosting-safe cron strategy required** — Reason: cPanel hosting may not support supervised long-running workers.

## 25 August 2026 — Team Structure Verification and Resource Expansion

- **Original 11 roles → 16 specialist roles** — Reason: enterprise coverage audit identified five gaps that should not remain implicit or be overloaded onto generalist agents.
- **Added Agent 11: Solution Architect and Independent Code Reviewer** — Covers cross-module architecture, ADRs, idempotency/state-machine design and independent review.
- **Added Agent 12: Payment and Financial Integrity Specialist** — Covers payment lifecycle, refunds, replay protection and reconciliation.
- **Added Agent 13: Accessibility and Technical SEO Specialist** — Covers WCAG 2.2 AA, semantic UX, canonical/indexation and structured data.
- **Added Agent 14: Notification and External Integration Specialist** — Covers centralized mail/in-app notifications, cron-compatible delivery and integration reliability.
- **Added Agent 15: India Commerce Compliance and Business Operations Specialist** — Covers approved GST/invoice, returns, privacy and policy requirements; does not replace qualified legal/accounting advice.
- **Agent 10 remains inactive/on-demand** — Deployment cannot begin without explicit user activation.

## 25 August 2026 — Phase 0 Audit Sequencing Proposal

- **Direct Phase 0 → Phase 1 design work → Proposed Phase 0A safety remediation first** — Reason: audit found unrestricted admin access, coupon amount trust, payment replay/idempotency risk and false refund state. Awaiting user approval.
- **Filament 3.3.54 accepted as installed → Upgrade assessment required for Filament 5.x** — Reason: locked rule requires latest compatible stable version; official current documentation is 5.x.
- **SQLite local evidence → MySQL 8 remains mandatory** — Reason: MariaDB migration compatibility passed, but it does not constitute exact MySQL 8 acceptance.
- **Vanilla/custom frontend assumption → Existing Tailwind/Alpine inheritance decision required** — Reason: current storefront is deeply dependent on Tailwind utilities and Alpine; forced removal would be a major rewrite.

## 25 August 2026 — Phase 0 Decisions Approved

- **Phase 1 next → Phase 0A Critical Safety Remediation first** — Approved: security and financial-integrity findings must be fixed before design work.
- **Keep all current frontend dependencies unchanged → Audit each dependency case-by-case** — Approved: Tailwind/Alpine may remain temporarily; unnecessary dependencies will be reduced only with measured impact and no blind rewrite.
- **Immediate Filament upgrade/stay indefinitely → Compatibility spike, then controlled Filament 5 upgrade** — Approved: plugin/resource compatibility must be proven first.
- **MariaDB compatibility acceptable → Exact MySQL 8.x mandatory** — Approved: MariaDB evidence is supplementary only.

## 25 August 2026 — External Composer Vendor Storage Locked

- **Workspace-local physical `vendor/` → Forbidden permanently for this workspace workflow** — Reason: explicit user directive to keep dependency files outside the persisted workspace.
- **External session path:** `/tmp/rythm-vendor`.
- **Workspace entry:** only symbolic link `vendor -> /tmp/rythm-vendor`; it is not a physical dependency directory.
- **Composer execution rule:** run Composer from `/home/user/rythm` so generated application paths remain correct.
- **Session reset rule:** if `/tmp/rythm-vendor` is cleared, recreate the external directory and symlink, then run `composer install` from project root. Never let Composer create a physical workspace vendor directory.

## 25 August 2026 — Autonomous Auto-Mode Protocol Registered

- **Ask-first only → Optional autonomous execution mode added** — Activates exclusively on the user command `ACTIVATE AUTO MODE`.
- **Per-task confirmations → One initial chunk plan, then continuous execution** — Ordinary implementation decisions are made autonomously and logged.
- **Autonomy boundary defined** — Only genuine design/data/destructive/legal/material-business/external-access blockers may interrupt execution.
- **Quality gates unchanged** — Auto Mode reduces confirmation overhead; it cannot skip tests, security, architecture, design, accessibility or phase sign-off.
- **Automatic pause points added** — `PAUSE AUTO MODE`, genuine blocker or completion of a full roadmap phase.
- **Vendor storage rule inherited** — Auto Mode must verify external `/tmp/rythm-vendor` storage before PHP/Composer work and may never create a physical workspace vendor folder.
- **Deployment remains separately gated** — Auto Mode cannot activate Agent 10 without explicit deployment instruction.

## 25 August 2026 — Phase 0A Critical Safety Remediation Accepted

- **Implicit Filament access → Explicit customer/admin boundary** — All users default to customer; only explicit administrators may access the panel. Granular staff RBAC remains Phase 7.
- **Browser-authoritative checkout money → Server-authoritative transaction pricing** — Product/variant prices, coupon discount, shipping, GST and final total are recomputed from locked server records.
- **Coupon consumption only after capture → Locked reservation before payment initiation** — Prevents concurrent max-use oversubscription; unpaid cancellation releases the reservation exactly once. Expiry/reconciliation automation remains a Phase 0B/2 operational concern.
- **Replay-sensitive payment effects → Exactly-once paid transition** — Unique gateway references, order/payment row locking, ownership checks and first-transition guards protect inventory, history, coupon and mail effects.
- **Product-only inventory mutation → Variant-aware source mutation** — Variant order items mutate variant stock; simple products mutate product stock; replay is a no-op and eligible cancellation restores the matching source.
- **Cancellation implies refunded → Cancellation creates pending refund record** — Financial state remains `refund_pending` until a gateway-backed processor verifies completion. The captured payment record remains paid meanwhile.
- **Phase 0A verdict: COMPLETE, not production-ready** — Fresh migration/seed, production build, 221 tests/739 assertions and zero Composer/npm advisories passed. Full-repository Pint has 53 inherited style issues; changed Phase 0A PHP files pass targeted Pint.
- **Auto Mode active → Paused at full phase boundary** — Next proposed phase is Phase 0B stack alignment. Agent 10 remains inactive.

## 25 August 2026 — Phase 0B Stack Alignment Executed / MySQL Gate Blocked

- **Filament 3.3.54 → Filament 5.7.6** — Controlled upgrade completed because 5.7.6 is the current compatible stable line for Laravel 13; admin and storefront regression remains green.
- **Livewire 3.8.3 → Livewire 4.4.2** — Required by Filament 5 and accepted after full Livewire commerce regression.
- **Filament-3-only AWCodes Tiptap plugin → Native Filament 5 RichEditor** — The plugin has no Filament 5 release. Native RichEditor uses TipTap and preserves existing HTML content without an unsupported dependency.
- **Broad framework update → Exact Laravel 13.24.0 restored and pinned** — Composer temporarily selected 13.26.1 during dependency resolution; Agent 0 restored exact 13.24.0 to obey the user-locked backend version. Composer's semantic-version warning is intentional.
- **Persistent queue worker assumption → Bounded cron worker** — The scheduler runs `queue:work --stop-when-empty --max-time=50 --tries=3 --timeout=45` every minute with overlap protection, suitable for cPanel cron.
- **MariaDB compatibility → Not accepted as exact MySQL evidence** — Available MariaDB 11.8 cannot close the exact MySQL 8 gate; Docker/Podman and an exact MySQL daemon are unavailable.
- **Phase 0B status: BLOCKED, not complete** — All independent gates pass: 222 tests/744 assertions, production build, 198-file syntax scan, 42 changed PHP files formatted, zero Composer/npm advisories.
- **Auto Mode active → Automatically paused at genuine blocker** — Requires secure access to a disposable exact MySQL 8.x database before resumption.

## 25 August 2026 — Direct MySQL 8 Qualification Route Locked

- **Container-provided MySQL → Direct environment-configured MySQL** — User confirmed shared hosting will not support Docker/Podman; neither technology may become a project database dependency.
- **Local qualification source:** Laragon's underlying database server, administered through HeidiSQL, after server identity confirms Oracle MySQL 8.x.
- **Hosting verification source:** cPanel-provided MySQL, administered through phpMyAdmin, using the same Laravel `DB_*` direct connection model.
- **Administration client identity → Server identity** — HeidiSQL and phpMyAdmin do not prove the engine/version. Acceptance requires `SELECT VERSION(), @@version_comment` to identify exact MySQL 8.x; MariaDB remains non-acceptance evidence.
- **Credential handling locked** — Credentials stay in ignored local `.env` or process environment and must not be committed or pasted into project documents/chat.
- **Destructive gate boundary locked** — `migrate:fresh` and database-backed tests may run only against a clearly empty disposable acceptance database, never cPanel production data.

## 25 August 2026 — UAT Branch and Commit/Push Reporting Rule Locked

- **Main-branch working tree → `rhythm-uat` integration branch** — All current work will be committed and pushed to `rhythm-uat` until the owner requests another branch or Agent 0 records a justified branch split.
- **Task completion without VCS report → Mandatory commit report** — Any completed task containing code changes or requiring local-system tests must finish with applicable test evidence, a commit, an attempted push, and a report containing branch, commit hash, summary and push result.
- **Push failure handling** — Missing remote access or authentication must be reported as a blocker; Agent 0 may not imply that unpushed work is remote-safe.
- **MySQL identity evidence accepted partially** — Owner reports MySQL Community Server 8.4.3 and an empty `rythme_acceptance` database. This proves engine identity, not application migration compatibility.
- **Request to skip database gate → Not accepted as phase completion evidence** — Auto Mode cannot waive mandatory quality gates. `rythme_acceptance` remains disposable and must not receive real data before migration/schema and later catalog/deployment gates.

## 25 August 2026 — Owner Assistance and Persistent Database Rules

- **Developer-oriented instructions → Guided owner workflow** — The owner is non-technical. Agent 0 must minimize manual actions and present unavoidable work as short numbered steps with exact labels/commands and safety warnings.
- **Disposable/demo application database → Persistent project/UAT database** — Normal application migrations and approved data imports target a persistent MySQL 8 project database. It must not be described as a demo/practice database.
- **Persistent database → Never a destructive test target** — The request does not authorize `migrate:fresh`, `RefreshDatabase`, or destructive automated suites against persistent/production data. Database qualification must use non-destructive initial migration evidence or an isolated test schema.
- **Exposed PAT → Not stored or replayed through visible agent commands** — Repository URL was recovered from README and `origin` configured. Because this environment has no secret-input channel, Git authentication will use a repository-scoped writable deploy key instead of placing the exposed PAT into command logs.

## 25 August 2026 — Phase 0B MySQL Gate Accepted

- **MySQL identity-only evidence → Forward migration evidence accepted** — Owner confirmed MySQL Community Server 8.4.3, aligned local locked Composer dependencies, and reported successful `php artisan migrate --force` execution against persistent `rhythm_db`.
- **Persistent UAT safety retained** — Sample/development seeders and destructive suites were not run. `migrate:fresh`, `db:wipe`, `RefreshDatabase`, and unapproved seed/import operations remain prohibited against `rhythm_db`.
- **Phase 0B `BLOCKED` → `COMPLETE`** — Agent 0 accepted the owner-reported MySQL migration evidence together with the previously passed 222 tests / 744 assertions, build, syntax/style and dependency-audit gates.
- **Completion is not production sign-off** — Phase 1–10 gates remain; deployment Agent 10 remains inactive.
- **Auto Mode remains paused at the full-phase boundary** — The registered protocol requires the exact `ACTIVATE AUTO MODE` command before Phase 1 autonomous planning begins.

## 25 August 2026 — Phase 1 Auto Mode Activated / Design Evidence Blocked

- **Phase 1 pending → Autonomous specification work executed** — Agents 1, 2 and 13 inventoried the current Homepage/Shop implementation, inspected both live XStore reference pages, mapped structural gaps, and produced responsive, accessibility and SEO contracts.
- **Semantic reference access → Grade B structure evidence only** — The live pages prove hierarchy and controls but not computed typography, spacing, color, geometry or responsive pixel behavior.
- **Current local values → Provisional, not reference-approved** — Existing 1520px container, monochrome Inter tokens, breakpoints and component dimensions are documented as local measurements only.
- **Reference copying → Prohibited** — XStore remains a layout/interaction reference; its products, imagery, copy, trademarks and theme code are not approved Rythme assets.
- **Phase 1 status: BLOCKED** — Requires current full-page Homepage and Shop screenshots at 1440px and 390px before pixel-fidelity acceptance; final colors will be confirmed after comparison.
- **Brand direction: Current Logo First** — Owner confirmed retention of the existing Rythme / Rhythm Exports logo treatment; current monochrome tokens remain provisional until screenshot comparison.
- **Auto Mode active → Automatically paused at genuine design-evidence blocker** — Independent work is complete; Phase 2/3 implementation dependencies are documented. Agent 10 remains inactive.

## 25 August 2026 — Phase 1 Screenshot Evidence Accepted

- **Missing viewport evidence → Four DPR-2 captures accepted** — Owner supplied Homepage and Shop screenshots at physical widths 2880px and 780px, matching requested 1440px and 390px CSS viewports at DPR 2.
- **Third-party screenshots → Hash-only repository evidence** — PNG dimensions and SHA-256 identities are recorded in `PHASE_1_SCREENSHOT_MEASUREMENTS.md`; reference imagery itself is not committed or republished.
- **Reference palette measured** — Primary accent `#00796B`, dark/footer approximately `#222222`, white surfaces and soft `#F5F5F5`–`#F7F7F8` sections.
- **Screenshot gate: PASSED** — Desktop/mobile composition, density and responsive behavior can now guide Phase 3. Mobile Homepage capture truncation after Popular Brands does not omit any active Rythme Homepage contract section.
- **Remaining Phase 1 decision:** current monochrome tokens versus measured teal accent while retaining the current Rythme logo.

## 25 August 2026 — Phase 1 Design Specification Accepted

- **Monochrome vs. teal checkpoint → Reference Teal selected** — Owner selected measured reference accent `#00796B` while retaining the current Rythme / Rhythm Exports logo.
- **Accessible token direction locked** — Primary `#00796B`, strong `#005F55`, soft `#E7F4F1`, text/footer `#222222`, surface `#FFFFFF`, alternate surface `#F7F7F8`, border `#E5E7EB`; white/primary contrast is approximately 5.32:1.
- **Phase 1 `BLOCKED` → `COMPLETE`** — Agent 0 accepted structural, screenshot, responsive, accessibility, SEO and implementation-handoff evidence.
- **Specification acceptance ≠ current pixel match** — Phase 3 must implement and prove side-by-side desktop/mobile visual regression. No claim is made that the current storefront already matches.
- **Auto Mode active → Paused at full-phase boundary** — Next phase is Phase 2 MySQL schema/migrations/domain architecture. Agent 10 remains inactive.

## 25 August 2026 — Phase 1 Color Direction Corrected to Rythme Red

- **Reference Teal → Owner-approved Rythme Red** — Owner explicitly replaced the earlier teal decision with primary `#B20202` and strong/hover `#930303`, while retaining soft surface `#E7F4F1` and all approved neutral tokens.
- **Brand fidelity overrides reference color** — XStore teal remains recorded only as measured reference evidence; Rythme implementation must use the approved red accent.
- **Accessibility values corrected from calculation** — White on `#B20202` is **7.24:1**, white on `#930303` is **9.31:1**, and `#222222` on white is **15.91:1**. The first two values differ from the copied teal ratios and are the authoritative computed results.
- **Scope limited to color system** — Logo, typography, layout, responsive contract, accessibility/SEO requirements and Phase 1 acceptance remain unchanged.
- **Implementation gates passed** — Full regression: **222 tests / 733 assertions**; production Vite build passed; npm audit reported zero vulnerabilities; changed PHP tests passed Pint.

## 25 August 2026 — Phase 2 Domain Foundations Implemented / MySQL Gate Blocked

- **Flat product options → Normalized category-aware facets** — Added attribute definitions, values, category applicability and product/variant assignment pivots; color remains one optional attribute type rather than a universal hardcoded field.
- **Mutable stock only → Atomic stock plus immutable movement ledger** — Paid capture and paid-cancellation restoration now write signed, balance-after, idempotency-keyed inventory movements inside the existing order transaction.
- **Gateway payment rows only → Redacted payment-event foundation** — Added unique gateway event identity, SHA-256 payload hash, redacted metadata and processing state without retaining unrestricted webhook payloads.
- **Order retries without schema key → Nullable unique order idempotency foundation** — Existing rows remain untouched; Phase 4 will integrate request keys into checkout behavior.
- **Policy-dependent schemas deferred** — Shipping, GST/HSN, partial refunds, Q&A, RBAC and import staging remain in later phases because their business/professional inputs are not approved.
- **Independent gates passed** — Isolated migration/seed and three-migration rollback/forward passed; targeted **43 tests / 144 assertions** and full **225 tests / 753 assertions** passed; changed PHP Pint/syntax and Composer audit passed.
- **Phase 2 status: BLOCKED** — Requires owner-run non-destructive `php artisan migrate --force` and `migrate:status` on persistent MySQL 8.4.3 `rhythm_db`.
- **Auto Mode active → Paused at genuine external MySQL blocker** — Agent 10 remains inactive.

## 25 August 2026 — Phase 2 MySQL Identifier Recovery

- **SQLite-compatible generated foreign-key names → Explicit MySQL-safe names** — Owner's first MySQL forward migration exposed a generated pivot foreign-key identifier longer than MySQL's 64-character limit.
- **Failed MySQL DDL recovery added** — Migration `000004` now drops only its brand-new, unlogged Phase 2 catalog tables before recreating them, because MySQL can retain partial DDL after a failed migration. Existing application tables and data are never dropped.
- **All catalog foreign keys explicitly named** — Custom identifiers are bounded below 64 characters; no application/data model scope changed.

## 25 August 2026 — Phase 2 Accepted

- **MySQL forward-migration blocker → Closed** — Owner reported migrations `000004`, `000005`, and `000006` completed successfully on persistent MySQL Community Server 8.4.3 `rhythm_db` after the identifier correction; `migrate:status` showed all migrations as `Ran`.
- **Phase 2 `BLOCKED` → `COMPLETE`** — Agent 0 accepted the owner-reported exact MySQL evidence together with the independently passed 225 tests / 753 assertions, isolated rollback/forward cycle, syntax/style checks, and Composer audit.
- **Auto Mode paused at phase checkpoint** — Phase 3 remains `PENDING` until the owner issues exact `ACTIVATE AUTO MODE`. Agent 10 remains inactive and no production sign-off is implied.

## 25 August 2026 — Phase 3 Auto Mode Activated

- **Phase 3 `PENDING` → `IN PROGRESS`** — Owner issued exact `ACTIVATE AUTO MODE`; Agents 2, 1, 9 and 13 activated under Agent 0.
- **Five-chunk implementation plan approved** — Shared truthfulness/accessibility, Homepage fidelity, Shop marketplace, automated QA and rendered visual regression are sequenced in `tasks/AUTO_MODE_PHASE_3_PLAN.md`.
- **Source review ≠ visual acceptance** — Browser-rendered 1440px/390px evidence remains mandatory before Agent 0 can accept Phase 3.

## 25 August 2026 — Phase 3 Chunk 1 Shared Storefront Baseline

- **Unapproved promotional claims → Truthful storefront copy** — Removed shipping, return, EMI, warranty, fixed-discount, synthetic sold-count and unbacked countdown claims from active Homepage/Shop/header surfaces; deal availability now uses real stock only.
- **Generic SEO fallback → Query-aware policy** — Homepage and base Shop are self-canonical; paginated Shop pages preserve their page canonical; filtered/search Shop states are `noindex, follow` and canonicalize to the base Shop.
- **Passive hero autoplay → User-controllable carousel** — Added pause/resume control, keyboard-focus pause and reduced-motion behavior.
- **Visual-only drawer → Contained accessible dialog** — Added dynamic expanded state, focus entry/restoration, focus/scroll trapping and complete tab/panel relationships.
- **Chunk 1 gates passed** — 46 tests / 190 assertions, production Vite build, changed-PHP syntax/Pint and diff checks passed.

## 25 August 2026 — Phase 3 Chunk 2 Homepage Fidelity

- **Five-column desktop products → Six-column measured density** — Homepage product sections now use six cards at the 1440px reference width, then step down through five/four/three/two and a 320px one-column fallback.
- **Two-column mobile benefits → Compact vertical benefit rows** — Matches the accepted 390px composition and preserves touch/readability.
- **Four forced mobile hero panels → Prioritized three-panel composition** — The lowest-priority fourth campaign is hidden on mobile; desktop retains the full split marketplace hero.
- **Uniform deal cards → Large lead deal plus three supporting cards** — Uses real product/stock data only; no synthetic urgency returned.
- **Broken empty sections → Data-aware rendering** — New Arrivals, Deals, Recently Launched, Categories and Brands now avoid empty shells; the hero keeps exactly one descriptive fallback `h1` if admin slides are empty.
- **Request-time debug writes → Removed** — Deleted obsolete homepage service instrumentation that wrote schema/database details to a local log on every request.
- **Chunk 2 gates passed** — Homepage/CMS suite: 37 tests / 157 assertions; production build, changed-PHP syntax/Pint and diff checks passed.

## 25 August 2026 — Phase 3 Chunk 3 Shop Marketplace

- **Narrow three-column Shop → Shared wide marketplace composition** — Shop now uses the 1520px storefront container, four results beside the desktop sidebar at reference width, two cards from 360px and a 320px one-column fallback.
- **No Shop shortcuts → Responsive category shortcut rail/grid** — Added DB-driven popular-category shortcuts with current selection state and mobile horizontal scrolling.
- **Long facets → Searchable category and brand facets** — Added labelled client-side search while preserving canonical Livewire filter state.
- **Unsupported Popularity → Truthful Featured ordering** — Default order remains featured-first/newest fallback but is no longer mislabeled as popularity.
- **Missing post-Phase-2 facets → Rating and category-aware attributes** — Approved-review averages drive the rating facet/card summary; normalized product and variant attribute assignments drive category-applicable facets only when real assigned values exist.
- **Visual mobile filter panel → Accessible drawer behavior** — Added focus entry/restoration, Escape handling, focus/scroll trapping, result status announcements and 44px mobile card actions.
- **Chunk 3 gates passed** — 28 tests / 108 assertions, Blade compilation, production build, npm production audit, changed-PHP syntax/Pint and diff checks passed.

## Active Decisions Still Needed

- Product Q&A/comments scope before Phase 5.
- PHP versus Python scraper before Phase 6.
- Product content/image commercial rights before production data launch.
