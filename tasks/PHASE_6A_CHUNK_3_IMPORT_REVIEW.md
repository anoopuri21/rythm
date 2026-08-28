# Phase 6A Chunk 3 — Safe Import, Review and Activation

**Status:** COMPLETE — implementation, isolated gates and owner-reported persistent MySQL UAT accepted
**Date:** 26 August 2026

## Delivered import workflow

- Added `catalogue:import-expansion` for completed one-/two-group batch reports.
- Dry-run is the default; `--commit` is explicit.
- A safe batch ID resolves under the platform's configured temporary directory, avoiding Windows/Linux path-copy requirements.
- Rejects incomplete acquisition reports, product failures, image failures, missing-media products, missing/out-of-batch group directories and repository staging paths.
- Produces an aggregate import report plus per-group details.
- Commit creates only inactive, zero-stock, review-controlled products and inactive zero-stock variants.
- Existing source identities remain idempotent; changed source or slug conflicts are reported and never overwrite owner-managed records.
- Review/category metadata is deliberately excluded from the source-content hash, so new governance metadata does not create false conflicts with the first five Phase 6 imports.
- Exact manifest target-category names/slugs are retained in normalized records and used for new category mappings.

## Durable review state

Migration `2026_08_26_000006_add_catalogue_review_to_product_import_sources` adds:

- Publication-review-required flag and reasons.
- Publication reviewer and timestamp.
- Commercial-use approver and timestamp.
- Indexed review-queue fields.
- Null-on-delete reviewer/approver foreign keys.

Existing imported records default to review required and receive no automatic approval or activation.

## Activation safeguards

Imported products cannot become active through the normal edit toggle or table toggle. Activation uses an authorized Catalogue Manager/Super Admin action requiring:

1. Confirmation that title/description contain no unsupported retailer promises.
2. Confirmation that price and real Rythme stock were verified.
3. Confirmation that local media is approved for commercial use.
4. A mandatory 5–500 character reason/review note.

The service transaction rechecks authorization, positive price, real product or active-variant stock and at least one locally managed gallery image. It records reviewer/approver identity and time, marks each local media item approved, activates its reviewed category/brand mappings with the product, activates the product and writes a durable audit event.

A model-level guard independently rejects direct activation of provenance-backed products unless review, commercial approval, price, stock and local-media requirements are all satisfied. Bulk activation is bounded to 20 reviewed products per operation and applies the same per-record transaction and audit controls.

## Automated evidence

Focused acquisition/import/governance/catalogue regression after implementation:

- **35 tests / 198 assertions passed** before final command-path refinement.
- Expansion dry-run writes no products.
- Explicit commit creates inactive zero-stock records.
- Direct activation is rejected.
- Reviewed activation succeeds only after real stock is entered.
- Local media approval, reviewer identity/timestamps, audit reason and storefront visibility are verified.
- Review/category metadata changes do not generate false source-content conflicts.
- Existing admin/catalogue route and role tests remain green.

Full regression: **288 tests / 1,123 assertions passed**.

Additional gates:

- Isolated migration forward → rollback → forward passed.
- New review columns verified after remigration.
- Production Vite build passed.
- Targeted Pint and `git diff --check` passed.
- No dependency changes were introduced.

## Persistent UAT procedure

Persistent UAT has not yet been imported by the agent. The owner must first pull this chunk and run the non-destructive migration. For each batch:

1. Acquire the exact two manifest groups using the recorded batch ID.
2. Run `catalogue:import-expansion <batch-id>` and inspect dry-run counts.
3. Re-run with `--commit` only if the dry-run has no unexplained conflicts/failures.
4. In Filament, enter verified stock, review source promises/price/media, then use **Approve & activate** with a reason.
5. Delete the disposable batch directory after accepted import/media verification.

If the first five Acoustic source payloads have genuinely changed, they remain conflicts for owner review and are never overwritten. After inspecting the dry-run report, `--commit --allow-conflicts` may be used to import only ready records while explicitly holding those existing conflicts. The command reports both created and held counts and still fails on actual validation/media errors.

## Persistent UAT result — 29 August 2026

The owner reported the following results from persistent MySQL UAT:

- **Imported:** 80
- **Active:** 80
- **Held from publication:** 0
- Imported products and locally managed media were visible and editable in Filament.
- Activation controls required reviewed content, approved local media and explicitly entered real stock; stock was not inferred.

## Agent 0 gate decision

Agent 0 accepts the owner-reported persistent MySQL evidence together with the existing automated qualification. Chunk 3 and Phase 6A are complete. This acceptance is not production or deployment authorization.
