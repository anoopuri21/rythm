# Media Optimization

> Where media lives, how URLs are built and how to repair stored files:
> **`docs/media-architecture.md`** (single `MEDIA_DISK`, host-relative `/storage` URLs, `php artisan media:relocate`).

## Product media pipeline

**Product images are plain uploads — there is no conversion queue.** The file an
admin uploads to `public/uploads/products` is exactly what the browser gets, and
its URL is stored on the row (`products.image` / `.gallery` / `.og_image`); see
`docs/media-architecture.md` §7.

Consequences for weight:

- Nothing is resized or re-encoded server-side, so upload images at the size you
  want served. A good default is **1200×1200 JPEG/WebP, quality ~80** — that
  covers the product page, and cards scale it down with CSS
  (`object-fit: contain`, explicit width/height, lazy loading).
- Prefer WebP over JPEG for the same quality; the field accepts
  JPEG/PNG/WebP/AVIF (never SVG).
- The admin limit is 5 MB per file (3 MB for the social image), max 12 gallery
  images — set in `App\Filament\Components\ImageUpload`.
- Products with no upload fall back to the committed
  `public/images/products/{slug}.jpg`, so a missing upload never renders a
  broken image.

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
- Upload MIME, pixel dimensions and file size must be bounded by admin validation.
- Preserve originals for controlled regeneration, subject to storage policy.
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
