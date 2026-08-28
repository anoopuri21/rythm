# Phase 6A Chunk 4.0 — Category-row Configuration

**Status:** COMPLETE
**Date:** 28 August 2026
**Accountable:** Agent 0
**Primary:** Agents 3 and 6
**Independent review:** Agents 9 and 11

## Delivered

- Additive `homepage_category_rows` migration with category foreign key, one-row-per-category uniqueness, title, product limit, order, visibility and query index.
- `HomepageCategoryRow` model with typed casts, category relation, observer registration and defensive 4–8 product limit.
- Reverse `Category::homepageCategoryRow()` relation.
- Homepage cache invalidation on row create/update/delete/restore.
- Filament 5 Category Rows manager with category uniqueness, bounded product count, order and visibility controls.
- Least-privilege `content.manage` policy mapping: Marketing/Super Admin can manage; Catalogue Manager cannot.
- Guest admin access remains denied.

## Gates

- Focused domain/admin/governance tests: **21 tests / 106 assertions passed**.
- New category-row tests: **6 tests passed**.
- PHP syntax: passed for all changed PHP files.
- Pint: 9 changed PHP files passed.
- Vite production build: passed.
- Isolated additive migration forward, targeted rollback and forward: passed.
- Git diff check: passed.

The first combined focused run exposed only a missing disposable Vite manifest after environment restoration; the production build was recreated and the exact tests then passed. Persistent MySQL/UAT was not touched. The full-project rollback warning for an existing legacy media migration was avoided by a targeted migration cycle and is unrelated to this additive migration.

## Safety

Rows default inactive. This chunk does not activate categories/products, infer stock, fetch remote media or change public Homepage output. Public query/presentation behavior begins in Chunk 4.1.
