# Media architecture (uploads · preview · storefront URLs)

> Scope: every admin-managed image — product images, variant images, brand logo,
> category icon, hero slides, homepage blocks.
> Optimisation (WebP conversions, queue): `docs/media-optimization.md`.
>
> **Product images no longer use the media library** — they are plain files in
> `public/uploads` with their URL stored on the product row. See §7; everything
> above it still applies to the media-library resources.

## 1. The contract (what must always be true)

| # | Rule | Where it lives |
|---|---|---|
| M-1 | **One media disk.** Panel uploads, programmatic imports and storefront URLs all use `MEDIA_DISK` (default `public`). It must be publicly readable. | `config/media-library.php` (`disk_name`) + `config/filament.php` (`default_filesystem_disk`) — same env var |
| M-2 | **Media URLs are host-relative** (`/storage/12/photo.jpg`). Never built from `APP_URL`, the request host or a signature. | `config/filesystems.php` → `disks.public.url` (`MEDIA_URL`, default `/storage`) |
| M-3 | **One definition of an upload field**, with bounded MIME / size / count and a fixed collection. | `app/Filament/Components/MediaUpload.php` |
| M-4 | **One way to resolve a URL**: "use the WebP conversion once it exists, else the original" = Spatie's `$media->getAvailableUrl([...])`. | `Product`, `ProductVariant`, `HeroSlide` |
| M-5 | **Absolute URLs only where crawlers need them** (`og:image`, JSON-LD) — made absolute at the output boundary with `url()`. | `layouts/app.blade.php`, `product/show.blade.php` |
| M-6 | **Media that is already stored on the wrong disk can be repaired** idempotently. | `php artisan media:relocate` → `MediaRelocationService` |
| M-7 | **Product images are plain uploads**: file in `public/uploads/products`, root-relative URL (`/uploads/products/…`) on the row. No media table, no conversion queue, no `public/storage` symlink. | `config/filesystems.php` (`disks.uploads`) · `App\Support\ImageStore` · `App\Filament\Components\ImageUpload` · `products.image` / `products.gallery` / `products.og_image` |
| M-8 | **Moving a product off the media library is repeatable and verifiable** (also reports products whose stored URL has no file). | `php artisan product-images:migrate [--dry-run]` |

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

## 3. How it fits together

```
admin form ──MediaUpload::single/gallery──▶ Filament SpatieMediaLibraryFileUpload
                                               │  disk = config('filament.default_filesystem_disk') = MEDIA_DISK
                                               ▼
imports / code ──addMedia()──────────────▶ Spatie Media Library ──▶ disk `public` (storage/app/public/{id}/…)
                                               │  disk = config('media-library.disk_name')      = MEDIA_DISK
                                               ▼
storefront / admin preview ◀── /storage/{id}/… (public/storage → storage/app/public symlink)
```

* **Edit-form preview** — `getUploadedFiles()` returns `$media->getUrl()` →
  `/storage/…` (same origin as the admin page → FilePond can always fetch it).
* **Storefront** — models expose `heroImage()`, `thumbnailImage()`,
  `galleryImages()`, `desktopImageUrl()` … (all built on `getAvailableUrl()`).
  Products without media fall back to the committed `public/images/products/{slug}.jpg`.
* **Conversions** are queued (`->queued()`); until the scheduled worker has
  generated them the original URL is served, so nothing breaks during the delay.

## 4. Adding a new media field

1. Model (`HasMedia`): `$this->addMediaCollection('banner')->singleFile();` — **no `useDisk()`**, the disk is global.
2. Admin: `MediaUpload::single('banner', 'banner', maxSizeKb: 4096)` (or `MediaUpload::gallery('photos', 'photos', maxFiles: 8)`). Never use `SpatieMediaLibraryFileUpload::make()` directly (a static test enforces this).
3. Storefront: `$model->getFirstMedia('banner')?->getAvailableUrl(['<conversion>'])`.

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
| New uploads land in `storage/app/private` | `MEDIA_DISK` overridden/blank in `.env`; `config/filament.php` replaced by a published copy; `php artisan config:clear` |
| WebP not appearing | scheduler cron `* * * * * php artisan schedule:run` (see `docs/media-optimization.md`) — originals are served meanwhile |
| Changed `MEDIA_DISK` | run `php artisan media:relocate` afterwards |
| A product shows no image / the link looks wrong | `php artisan product-images:migrate` — it lists every product whose stored URL has no file on disk |
| `/uploads/...` returns 404 | `public/uploads` missing from the document root: on cPanel Plan B re-run `bash scripts/deploy-cpanel.sh sync-public` (it links `public_html/uploads`) |

## 7. Simple product images (public/uploads · URL in the database)

Product images are the one part of the catalogue that is deliberately *not* in
the media library any more. What an admin uploads is written to a real folder in
the web root and its address is stored on the product:

```
admin form ──ImageUpload::single/gallery──▶ App\Support\ImageStore::store()
                                                │  disk `uploads` = public/uploads (UPLOADS_URL, default /uploads)
                                                ▼
                                   public/uploads/products/01J9ZQ…​.jpg
                                                │
             products.image / .gallery / .og_image = "/uploads/products/01J9ZQ….jpg"
                                                │
storefront ◀── Product::heroImage() / thumbnailImage() / galleryImages() / ogImage()
```

Why this shape:

* **The link cannot drift away from the file.** The URL in the row is the path
  the browser requests; there is no media id, no conversion name and no
  `public/storage` symlink between them. `/uploads/...` is served by the web
  server straight out of the document root.
* **Nothing is queued.** The uploaded file is what gets served, so an image is
  visible the moment the record is saved (no waiting for a conversion worker).
* **The database is self-describing** — an operator can paste `products.image`
  into a browser and see the image.

Rules that keep it honest:

| # | Rule | Where |
|---|---|---|
| P-1 | Every product image value written anywhere is normalised by `ImageStore::url()` (a disk path becomes `/uploads/…`; a root-relative or `https://` value is kept as is). | `App\Models\Product` mutators |
| P-2 | Uploads are bounded in one place: JPEG/PNG/WebP/AVIF only (never SVG), explicit `maxSize`, explicit `maxFiles`. | `App\Filament\Components\ImageUpload` |
| P-3 | Files are named `{ulid}.{ext}` inside `products/` — unique and free of anything the uploader chose. | `ImageStore::store()` |
| P-4 | Replacing or removing an image deletes the old file; a soft-deleted product keeps its files, a force-deleted one loses them. | `Product::booted()` (`updated` / `forceDeleted`) |
| P-5 | A product with no upload falls back to the committed `public/images/products/{slug}.jpg`, then to `null`. | `Product::heroImage()` |
| P-6 | URLs stay host-relative; only the SEO boundary makes them absolute (`url()`). | `resources/views/product/show.blade.php`, `layouts/app.blade.php` |

**Operations**

```bash
php artisan product-images:migrate --dry-run   # what would be copied (writes nothing)
php artisan product-images:migrate             # copy media-library images → public/uploads, fill the columns,
                                               # and list products whose stored URL has no file on disk
```

`scripts/deploy-cpanel.sh setup|update` runs it on every deploy (idempotent).
Exit code is non-zero when a product points at a missing file — that is the
"the image link is wrong" report; re-upload the image in the panel.

On cPanel "Plan B" (a real `public_html` folder) the deploy script links
`public_html/uploads` → `app/public/uploads` once, the same way `storage:link`
works for `/storage`; `rsync` deliberately skips `uploads` so a deploy never
deletes what was uploaded since the last one.

**Rolling this change out on an existing server (one time).** After the deploy
that includes it:

```bash
cd ~/rhythm
php artisan migrate                            # adds products.image / .gallery / .og_image
php artisan product-images:migrate --dry-run   # look at what will be copied
php artisan product-images:migrate             # do it
```

## 6. What locks this in (tests)

* `tests/Feature/MediaStorageTest.php` — config contract; for **every**
  media-library resource (variant images, brand logo, category icon, hero
  desktop/mobile, homepage block): upload in the panel → reopen the edit form →
  file is on the public disk, preview URL is host-relative, unsigned and backed
  by a real file; storefront renders it; `og:image` is absolute. Runs with
  `FILESYSTEM_DISK=local` (`phpunit.xml`).
* `tests/Feature/ProductImageUploadTest.php` — the §7 contract: panel upload →
  file in `public/uploads/products` → host-relative URL on the row → preview on
  reopen → storefront + absolute `og:image`; replaced/removed files are deleted;
  SVG is rejected; `product-images:migrate` copies media-library images and
  reports broken links.
* `tests/Feature/MediaRelocationTest.php` — repair command (move, dry-run,
  idempotency, split conversions disk, missing original, storage-link check).
* `tests/automation/media-architecture.test.mjs` + the two upload-policy tests
  in `security-*.test.mjs` — static guards (factory only, config files, env examples).
