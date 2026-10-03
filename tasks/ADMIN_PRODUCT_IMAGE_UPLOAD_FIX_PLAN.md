# Admin product image upload — fix plan

**Status:** FIXED (code change applied, regression test added)
**Date:** 2026-10-03 · **Branch:** `arena/01a10190-rythm`

---

## 1. Reported issue

Product image upload from the admin panel (`/admin → SHOP → Products`) fails.
Plain products (gallery-only) save fine; the failure appears as soon as a
**variant image** is involved — the file previews in the form, but saving the
product errors out (Filament/Livewire 500 during save).

## 2. Root cause (confirmed)

`app/Models/ProductVariant.php` registered its media collection like this:

```php
$this->addMediaCollection('variant_gallery')
    ->multiple()
    ->image()
    ->maxFiles(6)
    ->acceptAllMimeTypes();
```

`multiple()`, `image()`, `maxFiles()` and `acceptAllMimeTypes()` are methods of
**Filament's FileUpload form component** — they do **not** exist on Spatie
MediaLibrary's `MediaCollection` object (verified against the locked version
`spatie/laravel-medialibrary 11.23.5`; the class only exposes `useDisk`,
`storeConversionsOnDisk`, `acceptsFile`, `acceptsMimeTypes`, `singleFile`,
`onlyKeepLatest`, `withResponsiveImages`, fallback helpers).

Result: every code path that registers a variant's collections fatals with

```
Error: Call to undefined method Spatie\MediaLibrary\MediaCollections\MediaCollection::multiple()
```

### Why only variant uploads break

- Simple gallery/OG/brand/logo/category/hero uploads use correctly registered
  collections (only `singleFile()`), so they work.
- `registerMediaCollections()` is invoked by medialibrary **during media
  writes** — `FileAdder::toMediaCollection()` → `getMediaCollection()`
  (`src/MediaCollections/FileAdder.php:664`) — and by Filament's
  `SpatieMediaLibraryFileUpload::getDiskName()` →
  `getRegisteredMediaCollections()` when it saves an uploaded file.
- **Reads don't crash** (`getMedia()` goes through `MediaRepository` →
  `loadMedia()` and never registers collections), so the storefront and form
  loading looked healthy — which is why the bug surfaced specifically as a
  failed *upload/save*.

### Knock-on effects avoided by this fix

- Queued conversion jobs (`variant-thumb-webp`) call
  `registerAllMediaConversions()` → same fatal.
- Any future programmatic attach/detach of variant media.

## 3. The fix (applied) — simple & minimal

`app/Models/ProductVariant.php`:

```php
public function registerMediaCollections(): void
{
    $this->addMediaCollection('variant_gallery');
}
```

Why this is enough (practical, no over-engineering):

- MediaLibrary collections are **multiple by default** — no flag needed.
- "Images only + max 6 files + 5 MB" is already enforced one layer up on the
  admin form (`ProductResource` → Variant images: `->image()->maxFiles(6)
  ->maxSize(5120)`), which is where admins interact.
- No behaviour change for the import pipeline (it only touches the product
  `gallery` collection) or storefront readers.

Regression test: `tests/Feature/ProductVariantMediaCollectionTest.php`
(registration smoke + real attach via the same Filament save path:
`addMedia(...)->toMediaCollection('variant_gallery')`).

Optional hardening (not applied — kept the change minimal):
`->acceptsMimeTypes(['image/jpeg','image/png','image/webp','image/avif'])`
on the collection would also guard non-admin attach paths.

## 4. Verification steps

```bash
composer install
php artisan test --filter ProductVariantMediaCollectionTest
php artisan test            # full suite
```

Manual UAT (staging/prod):

1. `/admin → Products → Create`: simple product + 2–3 gallery images → Save →
   images visible in list + storefront PDP.
2. Create/Edit product → **Variants → Add item** → upload 1–2 variant images →
   Save → no error; reopen product → images still listed.
3. Storefront PDP → switch variant → gallery swaps to variant images.
4. Confirm WebP conversions appear within ~1 min (scheduled bounded worker:
   `routes/console.php` → `queue:work --stop-when-empty`, requires cron
   `* * * * * php artisan schedule:run`).

## 5. Ops checklist (unchanged code — worth confirming on the host)

- [ ] `php artisan storage:link` ran (else `/storage/...` image URLs 404).
- [ ] PHP limits: `upload_max_filesize` ≥ 12M / `post_max_size` ≥ 16M.
      Livewire uploads one temp file per request, so the ceiling is the
      **largest single file** — gallery 5 MB, hero 8 MB (which the old
      "≥ 8M" note would have rejected at exactly 8 MB), plus Livewire's
      default temp-upload rule of `max:12288`. `memory_limit` ≥ 256M for the
      queued WebP conversions (6000px originals ≈ 4 bytes/px in GD).
- [ ] `storage/app/public` writable by the web user; cron runs
      `schedule:run` every minute (conversion jobs).
- [ ] ~~Prod `.env`: `FILESYSTEM_DISK=public`~~ — **superseded 2026-10-03**: that
      advice treated the wrong disk as an ops detail, but it was the real cause of
      "preview stuck loading after save / no images on the website". Media now has
      its own pinned setting (`MEDIA_DISK`, independent of `FILESYSTEM_DISK`) and
      host-relative URLs. See `docs/media-architecture.md`; repair old rows with
      `php artisan media:relocate`.

## 6. What was *not* the problem

- Filament/Livewire versions are compatible (v5.7.6 / v4.4.2); the media
  table migration matches medialibrary 11 exactly.
- CSP middleware (`SecurityHeaders`) is not in the admin panel's middleware
  chain and allows same-origin XHR anyway.
- Queue config, conversion definitions, import pipeline and all other
  `registerMediaCollections()` implementations were audited and are correct.
