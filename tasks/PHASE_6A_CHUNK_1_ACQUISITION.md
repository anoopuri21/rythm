# Phase 6A Chunk 1 — Manifest-Driven Acquisition Orchestration

**Status:** COMPLETE — accepted by Agent 0
**Date:** 26 August 2026
**Persistent import/publication:** Not performed

## Delivered controls

- Added `catalogue:acquire-expansion`, which reads the qualified machine manifest and accepts one or two explicitly selected groups per invocation.
- Enforces a maximum of two groups (20 products) per batch to preserve request, disk and review bounds.
- Uses deterministic batch/group directories so interrupted groups can resume without re-downloading completed normalized records/media.
- Keeps all output outside the repository and rejects unsafe repository output paths.
- Produces group reports plus a compact combined `batch-report.json` containing manifest hash, selected groups, completion/failure totals, media bytes and publication-review totals.
- Extended the existing acquisition service to select exact manifest handles from a bounded collection candidate pool rather than blindly importing collection order.
- Verifies every fetched or resumed source product ID against the committed manifest identity; identity drift fails the record/batch.
- Preserves the existing host allowlists, HTTP retry/timeouts, 1–10 second pacing, product/image/run limits, MIME/content/dimension checks and resumability.
- Added publication-review assessment for source-retailer references, warranty/guarantee claims, free item/lesson/trial claims, shipping/return promises, promotions, bundle contents and open-box condition.
- Publication review flags are staged with each normalized product and summarized at batch level. They do not automatically rewrite or approve source promises.

## Automated evidence

Focused catalogue acquisition/expansion/import regression:

- **10 tests / 63 assertions passed**.
- Exact selected-handle acquisition excludes collection decoys.
- Manifest duplicate, invalid source host/schema/identity and unbounded group/output paths are rejected.
- Source identity mismatch stops the batch.
- Retailer warranty/free-item wording is flagged for publication review.
- Existing single-category acquisition, resume and safe import behavior remains green.

Full regression after the production build:

- **284 tests / 1,101 assertions passed**.
- Production Vite build passed.
- Targeted Pint passed.
- Composer locked audit: no known advisories.
- npm production audit: zero vulnerabilities.

One intentionally parallel first full-suite invocation began before the disposable Vite build had produced `public/build/manifest.json`, causing five view failures. After the build completed, the bounded full-suite rerun passed completely. This was an execution-order issue, not an application failure.

## Real public-source qualification

Command shape:

```text
php artisan catalogue:acquire-expansion tasks/PHASE_6A_ACQUISITION_MANIFEST.json \
  --group=acoustic-guitars \
  --batch=phase6a-chunk1-qualification \
  --delay=1500 --images=3 --no-images \
  --output=<disposable-path>
```

Result:

| Metric | Result |
|---|---:|
| Manifest-selected products | 10/10 |
| Failures | 0 |
| First-run requests | 11 (one collection + ten products) |
| Resumed products | 10/10 |
| Resume requests | 1 (collection membership revalidation only) |
| Publication review required | 10 |
| Downloaded media | 0 |
| Normalized staging size | 184 KiB |
| Script/iframe tags in normalized text | 0 |

All ten Acoustic records were flagged because retailer-specific wording is present. This confirms the review gate is functioning and that publication must wait for Chunk 3 content/stock review even though the owner authorized commercial use.

## Data and capacity safety

- No database import occurred.
- No products were activated or published.
- No images were downloaded during the real qualification.
- Persistent UAT was not connected or modified.
- Real normalized output and logs were deleted after compact evidence extraction.
- Repository remains approximately 30 MiB excluding Git, external dependencies and generated build output.
- Repository `vendor` remains an external `/tmp/rythm-vendor` symlink only.

## Agent 0 gate decision

Chunk 1 passes. The exact manifest can now drive bounded, resumable category acquisition with source-identity drift protection and explicit publication-review flags. Chunk 2 may execute the four real two-category batches, starting with Acoustic and Electric Guitars. Import remains a separate Chunk 3 gate.
