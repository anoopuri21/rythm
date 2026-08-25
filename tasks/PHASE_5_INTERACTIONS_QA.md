# Phase 5 Reviews, Product Q&A and Coupons — Qualification Evidence

**Date:** 26 August 2026  
**Branch:** `rhythm-uat`  
**Authority:** Agent 0 with Agents 3, 6, 9, 11, 12 and 14  
**State:** QA — implementation and isolated gates passed; exact MySQL UAT forward migration pending

## Locked scope

The owner selected verified-purchase reviews plus moderated product Q&A. Blog comments are excluded.

## Implemented and qualified

### Verified reviews

- Review eligibility now requires a paid, delivered order containing the product.
- One review per customer/product is enforced in service logic and by a database unique constraint; concurrent duplicate insertion is translated to a bounded customer error.
- Reviews have explicit pending, approved and rejected states with moderator/timestamp audit fields.
- Bounded merchant replies record staff identity/time and render only with approved reviews.
- Product detail and Shop aggregates use only approved reviews. The synthetic product `4.8` rating was removed.
- Rating controls, review labels, status/error feedback and independent pagination were remediated for accessibility.

### Moderated product Q&A

- Added `product_questions` with customer/product ownership, pending/approved/rejected status, staff answer and moderation/answer audit fields.
- Signed-in customers can submit validated 10–1,000 character questions.
- Submission is rate limited to three attempts per customer/product per ten minutes.
- Only approved questions with a non-empty staff answer are public; Blade escaping prevents stored markup execution.
- Added responsive Livewire storefront UI and a deny-by-default Filament `Product Q&A` moderation resource.

### Coupons and truthfulness

- Coupon codes normalize to uppercase.
- Unknown types, non-positive values, percentages over 100 and invalid active periods are rejected server-side.
- Direct usage increments are transaction-locked and cannot exceed the configured limit; Phase 0A reservation/release and checkout repricing invariants remain intact.
- Removed unsupported warranty, shipping, dispatch, EMI, refund-timeline, synthetic metric and synthetic testimonial content from active templates and seed sources.
- Added a data-safe remediation migration: only untouched seeded CMS rows (`created_at = updated_at`) are corrected; owner-edited rows are preserved. Unsupported seeded testimonials are removed and are intentionally not restored on rollback.

## Automated evidence

- Full application regression: **265 tests / 974 assertions passed**.
- Phase 5 migration forward → rollback (two migrations) → forward on isolated SQLite: passed.
- Changed PHP syntax and changed-file Pint: passed across 32 Phase 5 PHP files.
- Blade compilation and `git diff --check`: passed.
- Production Vite build: passed.
- Composer locked audit: no advisories.
- npm audit: zero vulnerabilities.
- Public claim scan: passed. The Filament shipping-threshold configuration label is intentionally excluded because it describes an admin-configurable rule rather than asserting an active customer policy.
- External Composer dependency rule passed: repository `vendor` remains a symlink to `/tmp/rythm-vendor`.

## Rendered responsive and accessibility evidence

An isolated disposable SQLite fixture rendered the populated review and Q&A journey at:

- 1440 × 900
- 768 × 1024
- 390 × 844
- 320 × 700

Across all four runs:

- Axe WCAG 2 A/AA and WCAG 2.1 AA violations: **0**
- Horizontal viewport overflow: **0**
- Console/page errors: **0**
- Desktop question submission showed the pending-moderation status successfully.
- Review and Q&A section screenshots plus machine-readable results are under `tasks/evidence/phase5/`.

The browser run exposed one pre-existing stock-text contrast failure. It was changed from `emerald-600` to `emerald-700`; the complete run then passed.

## Data safety and remaining gate

- Persistent `rhythm_db` was not connected to, reset, seeded or targeted by destructive tests.
- Migration `2026_08_26_000001` performs a non-destructive duplicate-review preflight before adding the unique constraint. If duplicate customer/product reviews exist, it stops before schema changes and reports the required remediation instead of deleting data.
- Migration `2026_08_26_000002` preserves owner-edited CMS records and only changes untouched seeded rows.
- Exact MySQL Community Server 8.4.3 forward-migration/status evidence on persistent UAT remains required before Agent 0 can accept Phase 5 as COMPLETE.

## Bounded limitations

- Final commercial shipping, return, warranty, privacy and legal terms still require owner/legal approval before production; the storefront now states bounded operational facts rather than inventing policy.
- Notification workflows for review approval/rejection, staff replies and Q&A answers belong to the later notifications phase.
- Phase 8 security/compliance hardening, Phase 9 full UAT and Phase 10 production-readiness remain pending.
- Agent 10 is inactive; this document is not deployment authorization or production sign-off.
