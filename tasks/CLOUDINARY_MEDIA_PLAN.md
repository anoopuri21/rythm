# Cloudinary media — phase 1 (products + categories) plan

**Status:** PARTIAL (verified) — code + docs + tests in tree; a static audit of
every product/category write path plus the automated suite below confirm that
nothing is written to `storage/app/public`. The PHPUnit run and one real
Cloudinary upload remain the owner's host step (this sandbox has no PHP).
**Executed:** 2026-10-05 · **Branch:** `arena/01a10a94-rythm`
**Runbook:** `docs/cloudinary-media.md` (install / verify / rollback)

**Owner decisions (confirmed):**

1. **Cloudinary for products + categories first** — other collections stay on
   `MEDIA_DISK` until the owner widens the list.
2. **Legacy images keep their URLs** — no re-import, no URL rewrite, no bulk
   migration of existing media rows.
3. **New uploads are Cloudinary-only** — stored on and served from Cloudinary;
   nothing is written to `storage/app/public` for them.
4. **Nothing new loads from the local server** for those new images.

---

## 1. Scope

| | Phase 1 (now) | Later (env-list edit, no code change) |
|---|---|---|
| Collections | product `gallery`, product `og`, variant `variant_gallery`, category `icon` | brand `logo`, hero `image`/`desktop_image`/`mobile_image`, homepage block `image` |
| Legacy rows | untouched (`MEDIA_DISK` + `/storage/...`) | same |
| Everything else | unchanged | —— |

## 2. What was built

1. `config/filesystems.php` → `cloudinary` disk (package driver + `CLOUDINARY_*`).
2. `config/media-library.php` → `media_model` = `App\Models\Media`; the
   `cloudinary` rollout block (`MEDIA_CLOUDINARY`, `MEDIA_CLOUDINARY_COLLECTIONS`).
3. `app/Support/MediaDisk.php` — the single disk decision (fail-safe: needs the
   switch **and** a resolvable cloud name).
4. `app/Support/CloudinaryDeliveryUrl.php` + `app/Models/Media.php` — derived
   CDN URLs, conversion names → delivery transformations, no Admin-API call.
5. `app/Filament/Components/MediaUpload.php` → `->disk(...)`; `Product`,
   `ProductVariant`, `Category` → `->useDisk(...)` on the phase-1 collections.
6. `MediaRelocationService` + `MediaDoctor` — cloud rows are intentional, never
   "misplaced"; new `checkCloudinary()` readiness report.
7. Tests: `tests/Feature/CloudinaryMediaTest.php` +
   `tests/automation/cloudinary-media.test.mjs`; `phpunit.xml` pins the rollout
   **off** so every other suite keeps the legacy contract.

## 3. Owner steps (one time, on the PHP host)

```bash
composer require cloudinary-labs/cloudinary-laravel
php artisan config:clear
```

`.env`: keep `MEDIA_DISK=public` + `FILESYSTEM_DISK=local`, add (two lines —
`CLOUDINARY_URL` carries cloud name + key + secret):

```
CLOUDINARY_URL=cloudinary://API_KEY:API_SECRET@CLOUD_NAME
MEDIA_CLOUDINARY=true
```

Then `php artisan media:doctor` (expect: *Cloudinary ready — collections …*).

## 4. Verification (acceptance)

1. Upload one **product gallery** image and one **category icon** in the panel.
2. `php artisan tinker` → the media row's `disk` is `cloudinary` and
   `thumbnail_url` / `icon_url` start with `https://res.cloudinary.com/`.
3. `storage/app/public` gained **no** file for those uploads.
4. An **old** product/category image still renders from its original
   `/storage/...` URL (mixed catalogue).
5. `php artisan media:relocate --dry-run` reports nothing to move;
   `php artisan media:doctor` stays green.
6. `php artisan test --filter=CloudinaryMediaTest` passes (**11** tests: product
   gallery/og, category icon, variant images, reuse = one asset, `media:dedupe`
   on cloud rows, legacy URL byte-identical, relocation/doctor exemption).
7. Reuse: Media library → *Use elsewhere* on a Cloudinary image — the target
   shows the same URL, the Cloudinary dashboard asset count does **not** grow.
8. Duplicates: `php artisan media:dedupe --dry-run` lists the duplicate cloud
   uploads, `php artisan media:dedupe` merges them, both products keep working.

## 5. Rollback

`MEDIA_CLOUDINARY=false` + `php artisan config:clear`. New uploads return to
`MEDIA_DISK`; Cloudinary rows keep rendering from Cloudinary until re-uploaded.

## 6. Risks / follow-ups

* Cloudinary free-tier limits (storage / transformations / bandwidth).
* Delivery is `f_auto,q_auto:good` — verify on a real device that the derived
  URLs match the account's cloud name.
* Next phases: extend `MEDIA_CLOUDINARY_COLLECTIONS`, then update
  `docs/cloudinary-media.md` + the media-architecture M-9 note.
