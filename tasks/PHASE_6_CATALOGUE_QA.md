# Phase 6 — Controlled Catalogue Acquisition and Import QA

**Owner:** Agent 0
**Status:** IN PROGRESS — bounded acquisition pilot passed; import pipeline remains pending
**Date:** 26 August 2026

## Approved pilot contract

- PHP implementation.
- Public Bajaao pages/JSON only; no authentication or CAPTCHA bypass.
- Respectful request pacing and bounded retries.
- Acoustic Guitars: acquire 5 products first, then resume to 10 after validation.
- Maximum 3 gallery images per product for the pilot.
- All source artefacts remain disposable outside the repository.
- Scraped competitor text/images are staging-only and must not be published without commercial-use approval.

## Source-boundary evidence

Bajaao's public `robots.txt` allows public storefront/product browsing. Its public `agents.md` explicitly documents unauthenticated read-only collection JSON (`/collections/{handle}/products.json`) and product JSON (`/products/{handle}.json`). The implementation uses only those documented public surfaces plus `cdn.shopify.com` image URLs returned by product JSON.

The service rejects unapproved source/media hosts, limits a pilot to 10 products, enforces 1–10 second request delay bounds, limits images to five per product, applies per-image/run byte budgets, uses bounded retries, and refuses command output inside the repository.

## Real five-product pilot

Command contract:

```text
php artisan catalogue:acquire-pilot --collection=acoustic-guitars --limit=5 --delay=1500 --images=3
```

Result:

| Metric | Result |
|---|---:|
| Products | 5/5 |
| Product failures | 0 |
| Images | 15/15 |
| Image failures | 0 |
| Requests | 21 (1 collection + 5 product + 15 image) |
| Duration | 12.237 seconds |
| Average | 2.447 seconds/product |
| Raw response bytes | 708,337 |
| Normalized bytes | 89,142 |
| Media bytes | 2,024,780 |
| Temporary output size | 2.1 MiB |
| Initial 60-product media estimate | 24,297,360 bytes (23.17 MiB) |
| Initial 60-product time estimate | 146.8 seconds |

The five records represented Henrix, Vault, Ibanez, Yamaha and Cort and included 49 variants in total.

## Resume-to-ten pilot

The same run ID was resumed with `--limit=10`. All first five normalized records and media were reused without downloading them again.

| Metric | Result |
|---|---:|
| Products | 10/10 |
| Resumed without product/media download | 5 |
| Product failures | 0 |
| Images | 30/30 |
| Image failures | 0 |
| Requests in resume invocation | 21 (1 collection + 5 new product + 15 new image) |
| Duration | 19.785 seconds, including pacing across the ordered collection |
| Current raw response bytes | 915,678 |
| Cumulative normalized bytes | 161,275 |
| Cumulative media bytes | 3,056,464 |
| Temporary output size | 3.2 MiB |
| Revised 60-product media estimate | 18,338,784 bytes (17.49 MiB) |
| Reported 60-product time estimate | 118.7 seconds; allow additional category/network overhead |

The ten normalized records contained 95 variants and 30 local media manifest entries. A separate integrity scan verified every required normalized field, every media file SHA-256 and every local relative media path: **0 validation errors** and **0 hotlinks in imported media paths**.

After independent review added actual image-content/MIME and dimension validation plus script/style/iframe removal before plain-text normalization, the complete 5→10 pilot was run again. The hardened run completed 5/5 in 9.855 seconds and resumed to 10/10 in 16.82 seconds, with 95 variants, 30 dimension-validated images, 0 image/integrity/hotlink errors and the same 3.2 MiB temporary footprint.

## Automated evidence

- Targeted tests after media-content hardening: **3 passed / 20 assertions**.
- Full regression after generating production assets: **268 passed / 994 assertions**.
- Production Vite build passed. The first full-suite attempt exposed only the absent generated Vite manifest; `npm ci` plus `npm run build` restored the disposable build artefact and the rerun passed completely.
- Covers bounded normalization/local staging, media integrity metadata, resume-without-redownload, limit rejection and repository-output rejection.
- Changed PHP syntax passed.
- Targeted Pint passed.
- Composer audit: no security advisories. npm production audit: zero vulnerabilities.

## Capacity and cleanup

- Repository size excluding Git/dependencies/build artefacts: approximately **28 MiB** after implementation.
- Real pilot artefacts existed only under `/tmp/rythme-catalogue` and were not staged or committed.
- External Composer dependencies remained under `/tmp/rythm-vendor`; no physical repository vendor directory was created.
- The disposable real pilot data/media was deleted after compact metrics and findings were recorded.

## Import pipeline qualification

A provenance-backed import layer now validates staged schema, approved source host, price/variant structure, local relative media paths, SHA-256 and image dimensions before any write. The normal command is dry-run; `--commit` is explicit.

Safety behavior:

- Imported products and variants always start inactive with zero stock.
- Scraped availability never becomes an invented stock quantity.
- Newly required category/brand mappings start inactive; existing slug mappings are reused.
- Media is copied into Rythme-managed Spatie storage with source hash/dimensions and `commercial_use_approved=false`; no CDN URL is used by the product.
- Source identity and payload hash are stored in `product_import_sources`, not raw competitor content.
- An unchanged rerun is skipped idempotently.
- Changed source content or a pre-existing slug becomes a review conflict and never overwrites owner-managed data.
- Database uniqueness independently enforces source identity.

Real isolated SQLite evidence used a newly created `/tmp/phase6-import.sqlite`, never persistent UAT:

| Step | Result |
|---|---|
| Five-product acquisition with one image each | 5/5 passed |
| Import dry run | 5 validated; 0 writes; 0 conflicts/failures |
| Explicit commit | 5 inactive products, 49 inactive variants, 5 local media, 5 provenance rows |
| Active products after import | 0 |
| Identical second commit | 5 skipped unchanged; 0 duplicates/writes |
| Isolated DB size | 648 KiB |
| Cleanup | Staged source/media, copied media and isolated DB deleted |

Focused acquisition/import tests after admin/storefront qualification: **6 passed / 43 assertions**.

Final local gates:

- Full regression: **271 tests / 1,017 assertions passed**.
- New migration isolated forward → rollback → forward passed.
- Production Vite build passed.
- Changed-file syntax and Pint passed.
- Inactive imported product returned storefront 404 while an explicit admin could see it in Filament.

## Curated approximately 60-product launch plan

This is a bounded acquisition target, not permission to publish competitor content. Suggested balance based on the existing Rythme category tree:

| Category | Target |
|---|---:|
| Acoustic Guitars | 10 |
| Electric Guitars | 8 |
| Portable Keyboards/Digital Pianos | 8 |
| Electronic Drum Kits/Hand Percussion | 6 |
| Microphones | 8 |
| Audio Interfaces | 6 |
| Studio Monitors/Headphones | 6 |
| Guitar Strings/Cables/Accessories | 8 |
| **Total** | **60** |

Each category must run separately with dry-run first, disk-budget checks and inactive imports. Client/admin may replace, edit or manually add products. No record becomes public until content/media rights, stock, price and merchandising review are explicitly completed.

## Current decision

The public acquisition and safe inactive-import mechanisms are viable for the agreed bounded catalogue plan. Five products completed cleanly, the run resumed to ten without re-downloading completed products, all attached media became locally managed files, and measured storage is modest.

Phase 6 technical scope is `QA`: acquisition, resume, validation, inactive import, provenance deduplication, media attachment, admin/storefront boundaries and the curated launch plan pass locally. Final acceptance remains pending owner-side MySQL 8.4.3 forward migration/status and manual Filament review after pull. Commercial content/image rights remain a production-publication gate; the technical phase does not authorize publication.
