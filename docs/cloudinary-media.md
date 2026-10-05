# Cloudinary media (phase 1 — product + category images)

> Status: **implemented, opt-in**. Until `CLOUDINARY_*` credentials exist in
> `.env` **and** `MEDIA_CLOUDINARY=true`, every upload behaves exactly as before
> (MEDIA_DISK, `/storage/...` URLs). Nothing moves until the owner flips the
> switch.
>
> Companion to `docs/media-architecture.md` (M-1 … M-8); this doc is the
> exception layer for cloud-hosted media.

## 1. What this does

* **New uploads for the Cloudinary collections** (phase 1: product `gallery`,
  product `og`, product-variant `variant_gallery`, category `icon`) are written
  to the `cloudinary` disk and served from
  `https://res.cloudinary.com/<cloud>/...`. No file is written to
  `storage/app/public` for them.
* **Every existing image keeps its URL.** Rows uploaded before the switch keep
  their `disk = public` (or whatever they had) and keep rendering
  `/storage/12/photo.webp`. There is no bulk migration and no URL rewrite.
* Every other collection (brand logo, hero slides/images, homepage blocks)
  keeps using `MEDIA_DISK` — that is the next phase, an explicit list edit
  (`MEDIA_CLOUDINARY_COLLECTIONS`), not a code change.
* A mixed catalogue is normal: `categories.icon_url` etc. may hold
  `/storage/...` for old rows and `https://res.cloudinary.com/...` for new
  ones. M-7 resolution is per media row (`$media->getUrl()`), so both resolve.

## 2. Install (once, on the server)

```bash
composer require cloudinary-labs/cloudinary-laravel
php artisan config:clear
```

Add credentials to `.env` — **one line does it all** (cloud name, API key and
API secret are all inside the URL; copy the "API environment variable" value
from the Cloudinary dashboard):

```
CLOUDINARY_URL=cloudinary://API_KEY:API_SECRET@CLOUD_NAME
MEDIA_CLOUDINARY=true
```

`php artisan cloudinary:install` is **not needed** for this integration — it only
publishes the package's own `config/cloudinary.php` (used by its Blade
components/helpers, which this app does not use). The storage disk reads
`config/filesystems.php`.

Prefer separate keys? That also works:

```
# CLOUDINARY_CLOUD_NAME=...
# CLOUDINARY_KEY=...
# CLOUDINARY_SECRET=...
```

Both shapes are safe to use on their own; if you ever set both, `CLOUDINARY_URL`
wins on both sides (the disk driver and the delivery-URL builder check it first,
deliberately).

Keep `MEDIA_DISK=public` and `FILESYSTEM_DISK=local` exactly as they are.

> `MEDIA_CLOUDINARY=true` **without** credentials is safe: the rollout counts as
> off (`App\Support\MediaDisk::enabled()`), uploads stay on `MEDIA_DISK`, and
> `php artisan media:doctor` says so. With credentials present but the package
> missing, the doctor fails loudly instead of silently storing locally.

## 3. How it works

| Step | Where |
|---|---|
| Which disk a collection uses | `App\Support\MediaDisk::forCollection()` — `cloudinary` when the rollout is on **and** the collection is listed, else `MEDIA_DISK` |
| Panel uploads | `App\Filament\Components\MediaUpload` passes `->disk(MediaDisk::forCollection($collection))` to Filament's `SpatieMediaLibraryFileUpload` |
| Programmatic writes / imports | the models' `registerMediaCollections()` call `->useDisk(MediaDisk::forCollection(...))` on the phase-1 collections — both sides read the same class, so they cannot drift |
| URL generation | `App\Models\Media::getUrl()` / `getAvailableUrl()` (registered as `media-library.media_model`) derive `https://res.cloudinary.com/<cloud>/image/upload/<transformation>/<path>` from `App\Support\CloudinaryDeliveryUrl` — **no Admin API call per image** |
| "Conversions" | Cloudinary does them at delivery time. `Media::CLOUDINARY_TRANSFORMATIONS` maps the Spatie conversion names (`thumb-webp` → `c_fit,w_480,h_480,f_auto,q_auto:good`, etc.); cloud media queue **no** local conversions, so no original is ever downloaded back to this server |
| Stored URL columns (M-7) | unchanged mechanism — `MediaUrlObserver` + `SyncMediaUrls` write whatever `$media->getUrl()` returns, so old rows keep `/storage/...` and new rows get the CDN URL |
| Repair tooling | `MediaRelocationService` and `media:doctor` treat `cloudinary` rows as intentionally off-disk: they are never counted as "misplaced", never relocated, and their files are never stat-checked on a local disk |

Two notes on the disk driver (`cloudinary-labs/cloudinary-laravel`):

* it is an ordinary Laravel filesystem disk (`Storage::disk('cloudinary')`), so
  Spatie and Filament need no custom code;
* its own `url()` resolves through Cloudinary's Admin API — which is why the app
  derives delivery URLs itself for every row that reaches a blade/column.

Why the derived URL is `…/<transformation>/<path>` (extension included): the
driver strips the extension before uploading (`prepareResource()` →
`public_id = dirname/filename`), so Cloudinary stores e.g. `12/front` and appends
the format on delivery — `…/12/front.jpg`. The app's URL is built from the same
disk path, so it matches the asset exactly and never doubles the extension.
Extension-less uploads deliver at the extension-less path.

## 4. Verify

```bash
php artisan media:doctor          # expect: Cloudinary ready — collections [...]
php artisan media:sync-urls       # backfills any column still NULL (idempotent)
```

In the panel: upload a product gallery image and a category icon, then check

1. the admin thumbnail renders immediately (no `/storage` path),
2. the DB row: `php artisan tinker` →
   `App\Models\Product::latest('id')->first()->thumbnail_url` starts with
   `https://res.cloudinary.com/`,
3. `Storage::disk('cloudinary')->exists('12/photo.jpg')` is `true`,
4. `storage/app/public` gained **no** file for that upload, and
5. an OLD product/category image still renders from its original `/storage/...`
   URL.

## 5. Operations

* **Rollback:** set `MEDIA_CLOUDINARY=false`, `php artisan config:clear`. New
  uploads go back to `MEDIA_DISK`; Cloudinary rows keep rendering from
  Cloudinary (their `disk` column never changes) and can be re-uploaded by the
  admin if you want them local again.
* **Next collections:** add names to `MEDIA_CLOUDINARY_COLLECTIONS`
  (comma separated) — no code change. Update this doc + the phase list first:
  brand `logo`, hero `image`/`desktop_image`/`mobile_image`, homepage block
  `image`.
* **Never** run `media:relocate` expecting it to move Cloudinary files, and
  never add a cloud disk to a `media:doctor` local-file check.
* Cloudinary free tier limits (transformations/bandwidth/storage) apply —
  monitor the account dashboard; delivery is `f_auto,q_auto:good`, which keeps
  pages light.
* `livewire`/Filament temp files still land in `storage/app/livewire-tmp` and
  `sys_get_temp_dir()` **during** an upload (framework pipeline). They are
  deleted after the request; the stored file itself never touches this server.

## 6. Footguns

1. Do **not** pass `->visibility(...)` on a cloudinary field — the adapter's
   `setVisibility()` throws, and Cloudinary media is public by delivery.
2. Leave `CLOUDINARY_PREFIX` unset for images: the package applies the prefix to
   `raw` resources only, and images are uploaded as `image`.
3. Never invent a filename extension: Cloudinary appends its own format. The
   stored path keeps the uploaded name; delivery URLs include it as the format
   hint.
4. `App\Models\Media` is required for URLs to work without per-image API calls
   (`config/media-library.php` → `media_model`). Do not point it back at the
   Spatie class.
5. `MEDIA_CLOUDINARY=true` + credentials but **missing package** = every
   product/category upload fails (`Disk [cloudinary] does not have a configured
   driver`). Run the `composer require` step first; `media:doctor` spells this out.
6. Do not delete the credentials while cloud rows exist: the derived URL needs
   the cloud name. The stored URL columns are **not** overwritten with an empty
   value in that state (`SyncsResolvedMediaUrls` keeps an already-resolved URL),
   so existing images keep rendering — but uploads/re-syncs cannot rebuild a URL
   until the credentials are back. Roll back with `MEDIA_CLOUDINARY=false`, not
   by removing the keys.
