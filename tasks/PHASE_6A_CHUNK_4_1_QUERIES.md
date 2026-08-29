# Phase 6A Chunk 4.1 — Bounded Homepage Queries

**Status:** COMPLETE
**Date:** 29 August 2026
**Accountable:** Agent 0
**Primary:** Agent 3
**Independent review:** Agents 9 and 11

## Delivered

- Maximum four active category-led rows per Homepage request.
- Defensive 4–8 product bound per configured row.
- Active category and active-product requirements.
- Eager-loaded brand, category parent and local media for shared product cards.
- Admin order followed by deterministic ID ordering.
- Configured categories lead the existing discovery fallback.
- Active product counts are grouped instead of queried once per category.
- Empty, inactive and inactive-product-only categories are omitted truthfully.

## Gates

- Focused external-QA tests: **10 tests / 25 assertions passed**.
- Query-specific tests: **4 passed**.
- Pint applied and verified in disposable external QA copy.
- Git diff check passed.
- Workspace `vendor` directory/symlink remained absent.

The first test exposed only Faker's deliberately small unique brand pool while creating eleven products. The fixture was corrected to reuse one real brand; production logic was unaffected. No persistent-UAT data, public activation, stock or remote media was changed.
