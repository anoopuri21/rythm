# Media Optimization

> Where media lives, how URLs are built and how to repair stored files:
> **`docs/media-architecture.md`** (single `MEDIA_DISK`, host-relative `/storage` URLs, `php artisan media:relocate`).
>
> **Reused images (`docs/media-reuse.md`) are optimised once:** the file owner
> generates the WebP conversions, every reused usage mirrors the same
> `generated_conversions` and resolves into the owner's `conversions/`
> directory — so N usages cost one conversion, not N.
>
> **Cloudinary phase 1 (`docs/cloudinary-media.md`) changes the pipeline for
> product + category uploads only:** those rows are stored on Cloudinary and get
> their sizes at delivery time, so they queue **no local conversions** and are
> never relocated or downloaded back to this server. Everything below still
> describes every row on `MEDIA_DISK` (all legacy media + the other collections).

## Product media pipeline

`Product` defines two queued WebP conversions:

- `thumb-webp`: maximum 480×480, quality 82, used by product cards, cart, checkout and wishlist;
- `gallery-webp`: maximum 1200×1200, quality 84, used by product detail galleries.

Products preserve aspect ratio and use `object-fit: contain`. Existing locally committed fallback images continue to work. Views use the original media URL until a conversion is generated, preventing broken images during queue delay.

`ProductVariant` follows the same convention for its own images (the PDP swaps the gallery when a shopper selects an option):

- `variant-thumb-webp`: maximum 240×240, quality 80;
- `variant-gallery-webp`: maximum 1200×1200, quality 84, used by the PDP/variant gallery (`ProductVariant::galleryUrls()`).

`Product`'s gallery conversions are scoped with `->performOnCollections('gallery')`: the `og` social-share image is served to crawlers as uploaded, so no WebP copies are queued for it.

Gallery order decides which photo is the primary one (`heroImage()` / `thumbnailImage()` take the first item) — admins reorder with drag-and-drop in the panel (`MediaUpload::gallery()` → `->reorderable()`, persisted in `order_column`).

## Stored URL columns (M-7)

Each model also keeps the resolved URLs in DB columns
(`products.thumbnail_url`/`gallery_urls`/`og_image_url`, …) and all reads are
column-first. Consequence for optimisation: when the queued WebP conversion
finishes, `MediaUrlObserver` rewrites the column from the original URL to the
WebP URL — no cache warm-up or manual step needed, and the storefront picks the
smaller file up on the next request. Until then the column holds the original,
so nothing 404s during the queue delay. A row whose column has never been
resolved (pre-migration data) falls back to Media Library; `php artisan
media:sync-urls` fills those in (deploy runs it with `--only-missing`).

## Hero media pipeline

`HeroSlide` defines collection-specific queued conversions:

- desktop: maximum 1920×1080 WebP, quality 84;
- mobile: maximum 768×1024 WebP, quality 82.

The first hero image is eager/high priority. Subsequent slides are lazy/low priority. Mobile uses `<picture>` source selection.

## Shared-hosting queue behavior

Conversions are queued, but no daemon is required. `schedule:run` starts a worker every minute that drains available jobs and exits within 50 seconds. Large upload batches must be bounded; do not regenerate the full library during a customer traffic peak.

After rollout, regenerate legacy conversions from an external/disposable runtime or a controlled cPanel command:

```bash
php artisan media-library:regenerate --only-missing
```

Confirm the installed Media Library version supports the option before execution. If not, use its documented bounded model/ID command. Never blindly rerun a timed-out full-library operation; reconcile generated files first.

## Markup rules

- Product and category cards: explicit width/height, square aspect ratio, lazy loading, async decoding.
- Product detail primary image: eager/high priority; alternate images and thumbnails lazy.
- Cart, checkout and wishlist use the 480px thumbnail source.
- Header logo and first hero are above-fold and are not lazy.
- Below-fold media must be lazy unless it is the measured LCP candidate.
- Decorative images use empty alt text; product images use concise product names.

## Storage and acquisition

- All acquired product media is locally managed; no source hotlink at runtime.
- Upload MIME, pixel dimensions and file size must be bounded by admin validation — all three live in `app/Filament/Components/MediaUpload.php` (mime list, `maxSize` in KB, `dimensions:max_width/max_height`). The pixel bound is what keeps a small-but-huge-decoded file (a 5 MB flat PNG can decode to gigabytes) from killing the shared-hosting queue worker, because conversion runs through GD/Imagick.
- PHP limits must fit the largest *single* file, not the whole gallery: Livewire uploads one temp file per request, so `upload_max_filesize` ≥ 12M and `post_max_size` ≥ 16M cover the 8 MB hero field and Filament's 5 MB gallery fields (Livewire's unpublished default temp-upload rule is `max:12288`; a value above that needs `config/livewire.php`). Conversion memory needs roughly 4 bytes per pixel of the original — keep `memory_limit` ≥ 256M for 6000px sources.
- Preserve originals for controlled regeneration, subject to storage policy.
- Admin panel is the only image intake (M-8): the catalogue acquisition/import pipeline is dormant; uploaded media is stored locally by `MediaUpload`, and its resolved URL is persisted in the model's URL column.
- Conversion directories require writable shared-host permissions and public storage linkage (`php artisan storage:link`).
- Originals, conversions and responsive images all live on the one public media disk (`MEDIA_DISK`); `php artisan media:relocate` moves anything stored elsewhere.
- Do not infer publication approval from successful conversion.

## Operational checks

1. Upload representative JPEG/PNG media in UAT.
2. Confirm conversion jobs enter and leave the queue.
3. Verify WebP files, URL fallback and browser cache headers.
4. Measure representative encoded bytes against the performance budget.
5. Test missing/corrupt originals and worker timeout behavior.
6. Verify local disk consumption before bulk regeneration.
7. Run `php artisan media:relocate --dry-run`: it must report "Nothing to move" and a working storage link.
