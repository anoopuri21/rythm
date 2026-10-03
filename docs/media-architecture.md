# Media architecture (uploads · preview · storefront URLs)

> Scope: every admin-managed image — product gallery / social image, variant
> images, brand logo, category icon, hero slides, homepage blocks.
> Optimisation (WebP conversions, queue): `docs/media-optimization.md`.

## 1. The contract (what must always be true)

| # | Rule | Where it lives |
|---|---|---|
| M-1 | **One media disk.** Panel uploads, programmatic imports and storefront URLs all use `MEDIA_DISK` (default `public`). It must be publicly readable. | `config/media-library.php` (`disk_name`) + `config/filament.php` (`default_filesystem_disk`) — same env var |
| M-2 | **Media URLs are host-relative** (`/storage/12/photo.jpg`), and **the media disk owns that path** — `serve => true` on `public`, `serve => false` on the private `local` disk. Never built from `APP_URL`, the request host or a signature. | `config/filesystems.php` → `disks.public.url` (`MEDIA_URL`, default `/storage`) + the `serve` flags · verify with `php artisan media:doctor` |
| M-3 | **One definition of an upload field**, with bounded MIME / byte size / pixel size / count and a fixed collection. | `app/Filament/Components/MediaUpload.php` |
| M-4 | **One way to resolve a URL**: "use the WebP conversion once it exists, else the original" = Spatie's `$media->getAvailableUrl([...])`. | `Product`, `ProductVariant`, `HeroSlide` |
| M-5 | **Absolute URLs only where crawlers need them** (`og:image`, JSON-LD) — made absolute at the output boundary with `url()`. | `layouts/app.blade.php`, `product/show.blade.php` |
| M-6 | **Media that is already stored on the wrong disk can be repaired** idempotently. | `php artisan media:relocate` → `MediaRelocationService` |
| M-7 | **Every media URL is also stored in DB columns** (`products.gallery_urls`/`thumbnail_url`/`og_image_url`, `product_variants.gallery_urls`/`thumbnail_url`, `brands.logo_url`, `categories.icon_url`, `hero_slides.desktop_image_url`/`mobile_image_url`, `homepage_blocks.image_url`). Reads are columns-first, Media Library is the fallback, and writes happen automatically on every media change. | `MediaUrlObserver` + `SyncsResolvedMediaUrls` + `php artisan media:sync-urls` |
| M-8 | **The admin panel is the only image intake.** Storefront and panel render the stored URL column (M-7); the catalogue acquisition/import pipeline is dormant and must not be reintroduced as a catalogue source without an owner decision. | `MediaUpload`, `docs/RULES.md` §7 |

`FILESYSTEM_DISK` is **not** part of this contract. It may stay `local` (the
`.env.example` default) — media no longer follows it.

## 2. What was broken (root cause)

Reported symptom: *the preview shows right after the first upload, but after
saving and re-opening the record it spins forever ("loading"), and the images do
not appear on the website either.*

Two defects combined (reproduced with the default `.env.example`):

1. **Wrong disk.** Filament's `SpatieMediaLibraryFileUpload` passes
   `config('filament.default_filesystem_disk')` — which is
   `env('FILESYSTEM_DISK', 'local')` — to Spatie. Every panel upload therefore
   went to the **private** `local` disk (`storage/app/private`), while the
   storefront links to `/storage/...`. That path is unsigned, so the private
   disk's `serve` route answers **403** (404 in production) → no images on the
   website.
2. **Host-bound URLs.** For that private disk Filament builds a *signed*
   temporary URL from the **request root**; for the `public` disk the URL was
   `APP_URL + /storage`. Whenever that origin differs from the one in the
   browser's address bar (TLS-terminating proxy → `http://`, preview domain,
   `www` vs apex, blank/stale `APP_URL`), FilePond's `fetch()` is blocked
   (mixed content / CORS / unreachable host). Filament's `server.load` has no
   error branch, so the item **never leaves the loading state**. The first
   upload "worked" only because it previews from Livewire's temporary upload,
   not from the saved file.

Fix: M-1 (disk pinned in config, independent of `FILESYSTEM_DISK`) and M-2
(relative URLs). M-3 / M-4 remove the copy-pasted configuration that let this
drift, M-5 keeps SEO tags absolute, M-6 repairs rows created by the bug.
M-7 (stored URL columns) and M-8 (admin-only intake) came from the follow-up
"admin upload only, URL in DB" decision of 2026-10-03.

### 2b. Who answers `/storage`? (2026-10-03, second round)

Symptom after the M-1/M-2 fix, on a host **without** the `public/storage`
symlink: the first upload still previews (FilePond renders Livewire's temporary
file client-side), but after *Save* the preview is gone and the storefront `<img>`
404s.

Cause: **Laravel registers `GET /storage/{path}` itself for every local disk
with `serve => true`** (`FilesystemServiceProvider::serveFiles()`), and only the
first disk may claim a URI. The private `local` disk carried that flag with the
default `/storage` path, so whenever no symlink let the web server answer first,
the request went to the **private** disk's route instead of the media disk:
`ServeFile` requires a signature unless the disk's `visibility` is `public` → 403
in dev, 404 in production — for every image, however healthy the upload was.

Fix: `serve => false` on `local`, `serve => true` on the public media disk
(`config/filesystems.php`). With the symlink present the web server keeps serving
statically and the route is never reached; without it, Laravel now streams the
file from the media disk instead of rejecting it. The flag also registers
Laravel's `PUT /storage/{path}`, but that handler (`ReceiveFile`) requires
`upload=1` **plus** a valid signature, so no unsigned write path is opened. `php artisan media:doctor`
proves the whole chain (`/storage` owner, symlink, per-row files, stored URL
columns) and `--fix` repairs the safe parts.

## 3. How it fits together

```
admin form ──MediaUpload::single/gallery──▶ Filament SpatieMediaLibraryFileUpload
                                               │  disk = config('filament.default_filesystem_disk') = MEDIA_DISK
                                               ▼
imports / code ──addMedia()──────────────▶ Spatie Media Library ──▶ disk `public` (storage/app/public/{id}/…)
                                               │  disk = config('media-library.disk_name')      = MEDIA_DISK
                                               ▼
storefront / admin preview ◀── /storage/{id}/… (public/storage → storage/app/public symlink)
                                               ▲
   media change (upload/delete/reorder/conversion) ──▶ MediaUrlObserver
                                               │  writes the resolved URL(s) with forceFill()+saveQuietly()
                                               ▼
   products.gallery_urls / thumbnail_url / og_image_url · product_variants.… · brands.logo_url ·
   categories.icon_url · hero_slides.{desktop,mobile}_image_url · homepage_blocks.image_url
```

**Read order at render time (M-7).** Every accessor (`thumbnailImage()`,
`heroImage()`, `galleryImages()`, `galleryUrls()`, `logoUrl()`, `iconUrl()`,
`desktopImageUrl()`, `mobileImageUrl()`, `imageUrl()`, `Product::ogImage()`)
returns the stored column first, then asks Media Library, then the committed
`public/images/...` fallback. So the storefront, the cart, the SEO tags and the
admin list all render **one stored string** — the same one an operator can see
in the database — while a row that predates the columns (or a media change made
outside the app) still resolves correctly.

* **Edit-form preview** — `getUploadedFiles()` returns `$media->getUrl()` →
  `/storage/…` (same origin as the admin page → FilePond can always fetch it).
* **Storefront** — models expose `heroImage()`, `thumbnailImage()`,
  `galleryImages()`, `desktopImageUrl()` … (all built on `getAvailableUrl()`).
  Products without media fall back to the committed `public/images/products/{slug}.jpg`.
* **Conversions** are queued (`->queued()`); until the scheduled worker has
  generated them the original URL is served, so nothing breaks during the delay.

## 4. Adding a new media field

1. Model (`HasMedia`): `$this->addMediaCollection('banner')->singleFile();` — **no `useDisk()`**, the disk is global.
2. Admin: `MediaUpload::single('banner', 'banner', maxSizeKb: 4096)` (or `MediaUpload::gallery('photos', 'photos', maxFiles: 8)`). Never use `SpatieMediaLibraryFileUpload::make()` directly (a static test enforces this). Both helpers bound MIME, bytes (`maxSize`) and pixels (`dimensions:max_width/max_height`, default 6000 — raise per field only with a matching PHP `memory_limit`); **never add Filament's `->image()`**, it rewrites the mime list to `image/*` and would re-admit SVG.
3. Storefront: `$model->getFirstMedia('banner')?->getAvailableUrl(['<conversion>'])`.
4. Conversions are **collection-scoped** (`->performOnCollections('gallery')`) so a collection that is served as-is (e.g. `og` for crawlers) does not queue WebP copies nobody requests.

**A new media field also needs a URL column (M-7)** — otherwise its reads keep
hitting the media table and it is invisible to `media:sync-urls`:

1. Add a nullable column in a migration (`->json('gallery_urls')` for a list,
   `->string('image_url')` for a single image).
2. `implements HasResolvedMediaUrls` + `use SyncsResolvedMediaUrls`, with
   `resolvedMediaUrls(): array` returning `column => resolved URL(s)` from the
   collections (cast list columns as `'array'`).
3. Read column-first in the accessor: `return $this->image_url ?? $this->getFirstMedia('image')?->getUrl();`.
4. Add the model to `SyncMediaUrls::TARGETS` and render the stored column in the
   admin list with `App\Filament\Columns\StoredMediaUrlColumn` — **not**
   Filament's plain `ImageColumn`, which treats its state as a path on the disk
   and would look for `storage/app/public/storage/…`.

**Galleries are ordered, and order is the primary image.** `MediaUpload::gallery()` is
`->reorderable()`, which persists through Spatie's `order_column`
(`Media::setNewOrder()`), so the first image *is* the card/hero image
(`Product::thumbnailImage()` / `heroImage()`). Drag-to-reorder in the panel is the
supported way to change the primary photo — deleting and re-uploading is not needed.

## 5. Operations

**Environment** (`.env`, `.env.staging.example`, `.env.production.example`):

```
MEDIA_DISK=public        # publicly readable disk — leave as is
# MEDIA_URL=             # unset = relative /storage URLs (recommended). Set only for a CDN, e.g. https://cdn.example.com/storage
```

**Rolling this change out on an existing server (one time).** The first
`update` after merging is still run by the *previous* copy of
`deploy-cpanel.sh` (bash had already parsed it before `git pull` replaced it), so it
does not know the two new steps. After it finishes run them once by hand — or run
`update` a second time:

```bash
cd ~/rhythm
git branch --show-current            # must be the branch you merged into (main); update pulls THIS branch
bash scripts/deploy-cpanel.sh update
php artisan storage:link             # "already exists" is fine
php artisan media:relocate --dry-run # preview — expect: Storage link: ok
php artisan media:relocate           # moves old private-disk images; "Nothing to move" is fine
bash scripts/deploy-cpanel.sh sync-public   # ONLY if `public_html` is a real folder (Plan B) AND storage:link just created the link
```

From then on `update` hands over to the freshly pulled script (`update-steps`), so
future script changes also apply on their first deploy.

**Every deploy** (`scripts/deploy-cpanel.sh setup|update` already does both):

```bash
php artisan storage:link        # creates public/storage -> storage/app/public (idempotent)
php artisan media:relocate      # moves media stored on any other disk to MEDIA_DISK (idempotent)
```

**One command to diagnose "images are broken" (read-only; nothing is deleted):**

```bash
php artisan media:doctor        # checks: disk config, who owns /storage, symlink,
                                # per-row originals + conversion flags, stored URL columns
php artisan media:doctor --fix  # storage:link + media:relocate + media:sync-urls when needed
```

Exit code is non-zero when a real problem was found (a missing original file,
stale URL column, misplaced media), which makes it usable from monitoring.
`scripts/deploy-cpanel.sh check` runs it too (`media_doctor || true` keeps the
check report from aborting).

**Stored URL columns (M-7).** Normal admin uploads keep themselves in sync
(MediaUrlObserver runs on upload, delete, drag-reorder, conversion completion
and disk repair). Run the backfill by hand only after importing rows created
before the columns existed, or after changing media outside the app:

```bash
php artisan media:sync-urls --dry-run       # report only
php artisan media:sync-urls                 # rewrite stale columns
php artisan media:sync-urls --only-missing  # rows with a NULL column only (what deploy runs)
```

It is idempotent (writes only changed values), never deletes anything, skips
rows whose columns are already correct, and exits non-zero only when a model
fails to load. `scripts/deploy-cpanel.sh setup|update` run it with
`--only-missing` after `media:relocate`.

`media:relocate` first prints the media disk, its public URL and whether the
`public/storage` link exists (`MISSING — run: php artisan storage:link`), then
copies each misplaced item's original + conversions + responsive images, verifies
the copy, repoints the row and only then deletes the old files. Use
`--dry-run` to preview. Re-running is always safe. Exit code is non-zero if any
item failed (e.g. its original file is missing) — that row is left untouched.

**Troubleshooting**

| Symptom | Check |
|---|---|
| Images 403 / 404 on the site, preview never loads | `php artisan media:relocate` (old rows on the private disk) · `php artisan storage:link` |
| `/storage/...` returns 404 for a file that exists in `storage/app/public` | `public/storage` link missing / wrong target (cPanel "Plan B": re-run `bash scripts/deploy-cpanel.sh sync-public`) |
| Every image 404s right after Save, on a host with no `public/storage` | `php artisan media:doctor` — the private disk must not carry `serve` (M-2); fix with `php artisan config:clear` after deploying `config/filesystems.php` |
| New uploads land in `storage/app/private` | `MEDIA_DISK` overridden/blank in `.env`; `config/filament.php` replaced by a published copy; `php artisan config:clear` |
| Admin list thumbnail empty although the image exists | `php artisan media:sync-urls` (the row's URL column is NULL/stale); confirm it renders through `StoredMediaUrlColumn`, not a plain `ImageColumn` |
| A stored URL 404s after moving the storage root / changing `MEDIA_URL` | URLs are host-relative (M-2) — fix the symlink/disk, then `php artisan media:sync-urls` to rewrite the columns if the disk itself changed (`media:relocate` does both) |
| WebP not appearing | scheduler cron `* * * * * php artisan schedule:run` (see `docs/media-optimization.md`) — originals are served meanwhile |
| Changed `MEDIA_DISK` | run `php artisan media:relocate` afterwards |

## 6. What locks this in (tests)

* `tests/Feature/MediaStorageTest.php` — config contract; for **every**
  media-bearing resource (product gallery/og, variant images, brand logo,
  category icon, hero desktop/mobile, homepage block): upload in the panel →
  reopen the edit form → file is on the public disk, preview URL is
  host-relative, unsigned and backed by a real file; storefront renders it;
  `og:image` is absolute. Plus the gallery URL chain: product **and variant**
  galleries serve `thumb-webp` / `gallery-webp` / `variant-gallery-webp` once
  generated and the original until then, and the `og` collection registers no
  gallery conversions. Runs with `FILESYSTEM_DISK=local` (`phpunit.xml`).
* `tests/Feature/MediaRelocationTest.php` — repair command (move, dry-run,
  idempotency, split conversions disk, missing original, storage-link check).
* `tests/Feature/ResolvedMediaUrlTest.php` — the M-7 contract: upload persists
  the columns, accessors read them without touching `media`, legacy NULL rows
  fall back to Media Library, delete/reorder/conversion-completion re-sync,
  variants + brand/category/hero/homepage columns, and the backfill command
  (dry-run, idempotent, `--only-missing`).
* `MediaStorageTest` also pins the 2026-10-03 round: `GET /storage/{path}` is
  answered by the media disk (200, no symlink needed), the private disk's file is
  **not** served, exactly one disk may claim `/storage`, and `media:doctor` reports
  a healthy chain / fails on a missing original.
* `tests/automation/media-architecture.test.mjs` + the two upload-policy tests
  in `security-*.test.mjs` — static guards (factory only, config files, env examples,
  bounded px/byte/mime limits, gallery conversions, stored-URL columns and the
  deploy backfill step).
