# Phase 6A — Multi-Category Catalogue and Homepage Expansion Plan

**Owner:** Agent 0 — Project Lead
**Status:** IN PROGRESS — Chunks 0–2 complete; Chunk 3 QA; Chunk 4 next
**Priority:** Urgent owner-directed catalogue operation before canonical Phase 8
**Date:** 26 August 2026

## Purpose and sequencing

The owner accepted Phase 7 UAT and directed an urgent catalogue expansion before Phase 8. This is a bounded post-Phase-6 catalogue operation, not a new canonical delivery phase and not a production/deployment authorization. Canonical Phase 8 remains pending until this operation reaches its gate.

## Owner decisions recorded

- Expand across multiple categories with approximately 10 products per category.
- Perform additional acquisition, review and Homepage/Shop QA.
- Retain the existing homepage product sections and add category-led product sections.
- The owner authorizes publication use of the acquired product text/images. Rythme must still locally manage media and have no public runtime dependency on the source site.
- Real stock must be supplied or verified; source availability must never be converted into invented inventory.

## Target catalogue

The first bounded target is approximately **80 products total**, including existing imported products and deduplicated by source identity/slug.

| Product group | Total target |
|---|---:|
| Acoustic Guitars | 10 |
| Electric Guitars | 10 |
| Portable Keyboards / Digital Pianos | 10 |
| Electronic Drums / Hand Percussion | 10 |
| Microphones | 10 |
| Audio Interfaces | 10 |
| Studio Monitors / Headphones | 10 |
| Guitar Strings / Cables / Accessories | 10 |
| **Approximate total** | **80** |

If a public collection cannot supply ten suitable, non-duplicate products, the shortfall will be reported rather than filled with unrelated or fabricated records.

## Non-negotiable safeguards

- Public pages/documented public JSON only; no authentication or CAPTCHA bypass.
- Respectful pacing, bounded retries, per-run product/media limits and resumability.
- No competitor hotlinks or runtime dependency; images are copied into Rythme-managed storage.
- Provenance and payload hashes remain durable; changed source records never overwrite owner-managed edits.
- Existing products are reused or skipped; no duplicate products, variants, categories, brands or media.
- Persistent UAT is never targeted by destructive migrations, resets, seeders or automated test suites.
- New imports remain review-controlled until category mapping, price, content, media and real stock are verified. Owner publication authorization closes the content-rights decision but does not authorize invented stock.
- Repository remains free of raw catalogue dumps and downloaded media; disposable acquisition data stays outside the repository.

## Execution chunks

### Chunk 0 — Inventory, source and capacity qualification

1. Read-only inventory of current UAT/export evidence: products, source identities, categories, brands, active state, stock and media completeness.
2. Verify public collection handles and suitability for all eight product groups.
3. Measure duplicates and calculate exact category shortfalls from the existing catalogue.
4. Confirm an acquisition/import disk and request budget.
5. Produce the exact run manifest before any persistent import.

**Gate:** source handles, exact shortfalls and bounded run manifest accepted by Agent 0.

### Chunk 1 — Multi-category acquisition orchestration

1. Extend the existing PHP command/service with a multi-category manifest while retaining per-category limits.
2. Add resumable batch checkpoints and a compact combined report.
3. Reject duplicate handles, unsupported hosts, unbounded limits and repository output paths.
4. Keep each category independently rerunnable and auditable.
5. Add malformed response, partial failure, retry, resume and capacity tests.

**Gate:** isolated acquisition tests and one small real public-source qualification run pass.

### Chunk 2 — Category batches and normalized review

Execute in four bounded batches, approximately 20 products per batch:

1. Acoustic Guitars + Electric Guitars.
2. Keyboards/Digital Pianos + Drums/Percussion.
3. Microphones + Audio Interfaces.
4. Studio Monitors/Headphones + Accessories.

For every batch:

- Acquire and validate outside the repository.
- Verify normalized fields, variants, prices, image MIME/dimensions/hashes and local paths.
- Produce duplicate/conflict/failure counts.
- Stop on source-layout drift or unexpected volume.
- Delete disposable raw data/media after accepted import evidence.

**Gate per batch:** complete validation, no hotlinks, no unresolved malformed records and bounded disk use.

### Chunk 3 — Safe import, review and activation workflow

1. Run dry-run first; compare intended creates/skips/conflicts against the manifest.
2. Commit only after dry-run passes.
3. Reuse existing category/brand mappings and create missing mappings without duplication.
4. Surface imported records in Filament with provenance/review state.
5. Require explicit real stock, price and merchandising review before activation.
6. Support bounded admin bulk review/activation only for authorized Catalogue Managers/Super Admins, with confirmation, reason and audit records.
7. Prove idempotent reruns and owner-edit conflict protection.

**Gate:** approximately 80 deduplicated records available for admin review, with no invented stock and no unauthorized public activation.

### Chunk 4 — Homepage and Shop expansion

1. Preserve the existing dynamic product sections.
2. Add a bounded category explorer covering the main catalogue groups.
3. Add configurable category-led product rows, using admin-managed ordering and visibility.
4. Avoid eight permanently expanded rows on every request; use a responsive bounded layout and lazy/conditional queries where appropriate.
5. Ensure empty or undersupplied categories degrade truthfully without blank/broken sections.
6. Retain Rythme Red design tokens, current logo, semantic headings, keyboard access and mobile two-column product-card behavior.
7. Ensure Shop filters, category counts, brands, stock state and pagination operate correctly at the larger volume.

**Gate:** Homepage and Shop render correctly with realistic multi-category data at 1440, 768, 390 and 320 CSS pixels.

### Chunk 5 — Independent catalogue QA and operational handoff

1. Test approximately 80 realistic temporary products without destructively seeding UAT.
2. Validate query counts/performance, duplicate prevention, media ownership, broken images, SEO metadata and truthful stock/price rendering.
3. Run full PHP regression, production build, syntax/Pint and Composer/npm audits.
4. Run isolated migration/import forward and idempotency checks.
5. Owner UAT: category navigation, Homepage rows, Shop filters, product pages, admin review/activation and media.
6. Record exact imported/active totals and any products held from publication.

**Gate:** Agent 9 evidence accepted by Agent 0. This remains separate from production/deployment sign-off.

## Commit and cleanup cadence

Each completed chunk requires applicable tests, a bounded commit, push attempt, branch/hash report and cleanup/size check. Raw source responses, downloaded media, temporary databases and generated QA catalogues must not be committed.

## Progress

- **Chunk 0 — COMPLETE:** Eight public collections qualified, exact 80-record manifest produced with zero internal duplicate handles, UAT/local-fixture boundaries documented and capacity/request budget accepted. Evidence: `tasks/PHASE_6A_CHUNK_0_QUALIFICATION.md`.
- **Chunk 1 — COMPLETE:** Manifest-driven one-/two-group acquisition, deterministic resume, exact handle/source-ID validation, combined reporting and publication-review flags passed automated and real metadata-only qualification. Evidence: `tasks/PHASE_6A_CHUNK_1_ACQUISITION.md`.
- **Chunk 2 — COMPLETE:** Four real two-category runs acquired 80/80 selected products and 226 valid local images with zero product, media or integrity failures; every batch was cleaned after evidence extraction. Evidence: `tasks/PHASE_6A_CHUNK_2_REAL_BATCHES.md`.
- **Chunk 3 — COMPLETE:** Batch dry-run/commit, durable review state and activation safeguards passed automated qualification; owner-reported persistent MySQL UAT recorded 80 imported, 80 active and 0 held with Filament/local-media and real-stock controls verified. Evidence: `tasks/PHASE_6A_CHUNK_3_IMPORT_REVIEW.md`.
- **Chunk 4 — COMPLETE:** Bounded category-row configuration, cached Homepage queries, accessible responsive presentation and isolated 80-product Shop qualification passed. Evidence: `tasks/PHASE_6A_CHUNK_4_3_SHOP_QA.md`.

## Immediate next action

Phase 6A is complete. Begin Phase 8 with a read-only audit and bounded implementation plan; real financial actions remain human-gated.
