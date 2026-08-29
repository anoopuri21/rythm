# Phase 6A Chunk 4 — Homepage and Shop Expansion Implementation Plan

**Status:** COMPLETE — Subchunks 4.0–4.3 accepted by Agent 0
**Date:** 27 August 2026
**Accountable:** Agent 0
**Primary:** Agents 2, 3 and 6
**Independent review:** Agents 1, 9 and 13

## Audit findings

- Existing Homepage product sections and section order are stable and must remain intact.
- Existing Popular Categories carousel is bounded and responsive, but its ten slugs and image paths are hard-coded.
- Existing curated product sections depend on older slugs and do not provide category-led discovery for the eight expansion groups.
- `HomepageBlock` is content-oriented and cannot safely model a category relation, row product limit and unique row ordering.
- `HomepageDataService` is the correct cached query boundary; product cards already render truthful stock, price, review and local media states.
- Shop already supports category, brand, price, rating, attribute, stock, sale, search, sort and pagination. Larger-catalogue regression/performance evidence is required rather than a parallel Shop implementation.

## Locked design

Create a dedicated `homepage_category_rows` configuration model instead of overloading text content. Each row references one category and stores optional display title, bounded product limit, order and visibility. Public rows require an active category and active products; empty/undersupplied rows disappear or render only real available records.

The category explorer will use configured active row categories first and retain the existing popular-category fallback. Category imagery will prefer locally managed category media or a real local product image, with a neutral embedded/local fallback—not a source-site hotlink.

## Subchunks

### 4.0 — Configuration domain and admin

- Add MySQL-safe `homepage_category_rows` migration with FK, unique category, bounded limit, visibility and ordering.
- Add model, relationship, factory support if needed and cache observer integration.
- Add least-privilege Filament management with category selection, title, limit, order and visibility.
- Add migration/model/admin authorization and cache-invalidation tests.

### 4.1 — Bounded queries and discovery data

- Add eager-loaded, cached category-row queries to `HomepageDataService`.
- Fetch a bounded product set per configured row without N+1 behavior.
- Return truthful active-product counts and local image candidates.
- Preserve all existing Homepage datasets and implement configured-first/fallback explorer behavior.
- Add empty, inactive, undersupplied, ordering and query-behavior tests.

### 4.2 — Storefront presentation

- Add category-led section after existing category discovery without removing existing product sections.
- Use shared product cards and accessible headings/links.
- Add responsive bounded row layout with mobile horizontal discovery or two-column cards as appropriate.
- Avoid rendering eight permanently expanded expensive rows; cap configured visible rows and products per row.
- Add keyboard, semantic, empty-state and 1440/768/390/320 behavior checks.

### 4.3 — Larger-catalogue Shop and final Chunk 4 QA

- Exercise Shop filters, counts, brands, sorting and pagination against an isolated realistic approximately-80-product catalogue.
- Verify truthful inactive/zero-stock behavior and no public imported product before activation.
- Run focused/full PHP regression, migration cycle, production build, syntax/Pint and dependency audits as applicable.
- Record rendered evidence or retain QA when the environment cannot supply it.

## Safety and rollback

- Additive migration only; rollback drops only `homepage_category_rows`.
- No persistent-UAT destructive test or seed operation.
- Imported products remain inactive until reviewed stock/media/content activation.
- No Bajaao runtime calls or hotlinks.
- Existing Homepage sections remain in their current order and are regression-covered.
- Configuration can be disabled per row without deleting products/categories.

## Completion gate

Chunk 4 remains QA until schema/admin/query/view tests, full regression, production build, isolated migration cycle and responsive Homepage/Shop evidence pass. Missing owner-side active catalogue evidence is reported truthfully and does not permit invented stock or activation.
