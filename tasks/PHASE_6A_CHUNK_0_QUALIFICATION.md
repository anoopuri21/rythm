# Phase 6A Chunk 0 — Inventory, Source and Capacity Qualification

**Status:** COMPLETE — accepted by Agent 0
**Date:** 26 August 2026
**Mode:** Read-only source/database qualification; no catalogue import or media download occurred

## Current catalogue baseline

- Owner-reported persistent UAT baseline: five imported Acoustic Guitars are currently visible after review.
- Those five source identities are the first five records in the approved Acoustic Guitars pilot: Henrix 38C, Vault EA20, Ibanez MD39C, Yamaha F310 and Cort AD810.
- The workspace SQLite database contains 33 development fixture products across 22 leaf categories, but it has no `product_import_sources` table and is not the persistent MySQL UAT database. Its counts were deliberately excluded from the UAT shortfall calculation.
- Persistent UAT was not connected, queried or modified during this chunk. The import dry-run must remain the authority for actual creates/skips/conflicts.

## Public collection qualification

The following documented public Shopify collection JSON endpoints each returned ten products for a bounded `limit=10` request on 26 August 2026:

| Rythme group | Public source collection | Result |
|---|---|---:|
| Acoustic Guitars | `acoustic-guitars` | 10 |
| Electric Guitars | `electric-guitars` | 10 |
| Portable Keyboards | `portable-keyboards` | 10 |
| Electronic Drum Kits | `electronic-drum-kits` | 10 |
| Microphones | `microphones` | 10 |
| Audio Interfaces | `audio-interfaces` | 10 |
| Studio Monitors | `monitors-speakers` | 10 |
| Guitar Accessories | `guitar-accessories` | 10 |

The initially considered `headphones` collection returned an empty product array and was rejected. `monitors-speakers` is the qualified source for the Studio Monitors group.

A second bounded qualification request retrieved at most 20 metadata-only candidates for seven new groups. Every selected manifest record had at least one source-available variant and at least one image at qualification time. No images were downloaded.

## Curation findings

The source collection order cannot be imported blindly:

- The first Microphones page included a lavalier mounting accessory classified as a dynamic microphone. It was excluded.
- Obvious open-box products were excluded from the selected records.
- Several source records contain retailer-specific warranty, free lesson, ebook, bundle or promotional wording. Chunk 1 must add a publication-content review/sanitization gate; source promises must not silently become Rythme promises.
- Product availability is volatile. Source `available` is candidate qualification only and must never become Rythme stock.
- Monitor single/pair variants and accessory multipacks require explicit title/variant review before activation.

## Exact acquisition manifest

Machine-readable manifest: `tasks/PHASE_6A_ACQUISITION_MANIFEST.json`

- Schema version: 1
- Groups: 8
- Selected source identities: 80
- Duplicate handles inside manifest: 0
- Expected existing UAT records: 5 Acoustic Guitars
- Expected creates before authoritative dry-run: 75
- Target after deduplication: approximately 80
- Manifest SHA-256: `4ef6d285892576da5d4a5f1c31efce0437b81194a09ce00bc0dd182569040ca3`

The expected create count is planning evidence, not permission to bypass import validation. Existing source identities are skipped idempotently and owner-edited conflicts must stop for review.

## Request and capacity budget

The execution manifest is split into eight independent ten-product category runs:

- Per category: one collection request, up to ten product requests and up to 30 gallery-image requests when using three images per product.
- Delay: minimum 1,500 milliseconds between product requests.
- Maximum images: three per product for this expansion unless Agent 0 records a later bounded change.
- Existing service ceiling: 100 MiB per run and 5 MiB per image.
- Measured Phase 6 pilot: approximately 3.2 MiB for ten products/30 images.
- Estimated 80-product staged media from the hardened pilot: approximately 24–26 MiB total.
- Planned peak: no more than two category runs retained together before accepted import evidence and cleanup; allow a 25 MiB operating envelope plus configured failure margin.
- Raw responses, normalized records and media remain in disposable storage outside the repository.

## Exact four-batch execution order

1. Acoustic Guitars + Electric Guitars — 20 selected; first five Acoustic records expected to skip.
2. Portable Keyboards + Electronic Drum Kits — 20 selected.
3. Microphones + Audio Interfaces — 20 selected.
4. Studio Monitors + Guitar Accessories — 20 selected.

Each category remains independently resumable. A batch must stop if source layout changes, selected handles disappear, records become malformed, media violates host/MIME/dimension limits, or dry-run counts differ without an explained existing identity/conflict.

## Agent 0 gate decision

Chunk 0 passes. The source collections can supply the bounded target, the exact 80-record manifest has no internal duplicate handles, capacity is modest, and known curation risks are explicit. Chunk 1 may begin by implementing manifest-driven multi-category acquisition and publication-content review controls. No persistent import or publication has yet occurred.
