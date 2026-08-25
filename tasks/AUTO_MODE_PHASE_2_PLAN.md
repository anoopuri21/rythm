# Auto Mode Execution Plan — Phase 2 MySQL Schema and Domain Architecture

**Activated:** 25 August 2026
**Mode:** Autonomous
**Status:** BLOCKED — all independent gates pass; exact MySQL 8.4.3 forward migration pending
**Primary agents:** Agent 4 (database), Agent 3 (Laravel domain), Agent 11 (independent architecture), Agent 12 (financial integrity), Agent 15 (commerce operations)
**Review authority:** Agent 0
**Total chunks:** 5

## Chunk 1 — Existing Schema and Invariant Audit

- Inventory migrations, models, constraints, indexes and transaction boundaries.
- Preserve existing Phase 0A payment, coupon, stock and refund invariants.
- Classify missing structures by immediate need, later-phase dependency or business-decision blocker.
- Lock forward-only migration safety for persistent `rhythm_db`.

## Chunk 2 — Catalog Attribute and Facet Foundation

- Add normalized attributes, values, category applicability and product/variant value assignments.
- Add explicit uniqueness, foreign-key behavior, indexes and Eloquent relations.
- Keep category-specific musical-instrument facets data-driven; do not hardcode “color” as a universal attribute.

## Chunk 3 — Inventory and Payment Integrity Foundations

- Add immutable inventory movement ledger with idempotency key and source references.
- Route paid stock decrement and paid-cancellation restoration through one transactional inventory service.
- Add redacted payment/webhook event ledger foundation and nullable order idempotency key for later gateway integration.
- Do not implement unapproved shipping, tax, partial-refund or return policies.

## Chunk 4 — Database and Regression Verification

- Run clean isolated migration and seed.
- Run schema/relationship/inventory-ledger tests.
- Run full PHP regression, syntax/Pint and relevant audits.
- Review MySQL 8 index-name/identifier limits, decimal precision and rollback order.

## Chunk 5 — Exact MySQL Forward Migration Gate

- Update architecture evidence, tracker and changelog.
- Commit and push independently green implementation to `rhythm-uat`.
- Run only `php artisan migrate --force` against persistent `rhythm_db`; never `migrate:fresh`, `db:wipe`, sample seeders or `RefreshDatabase` tests there.
- Agent 0 accepts Phase 2 only after owner-reported exact MySQL 8.4.3 forward-migration evidence.

## Phase Gate

Phase 2 completes only when normalized schema foundations, forward/rollback migrations, domain relations, transactional inventory ledger integration and isolated DB regressions pass, followed by successful non-destructive forward migration on persistent MySQL 8.4.3. Business-rule-dependent shipping, tax, returns, Q&A and full partial-refund execution remain assigned to later phases.
