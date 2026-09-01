# Media Optimization

## Product media pipeline

`Product` defines two queued WebP conversions:

- `thumb-webp`: maximum 480×480, quality 82, used by product cards, cart, checkout and wishlist;
- `gallery-webp`: maximum 1200×1200, quality 84, used by product detail galleries.

Products preserve aspect ratio and use `object-fit: contain`. Existing locally committed fallback images continue to work. Views use the original media URL until a conversion is generated, preventing broken images during queue delay.

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
- Conversion directories require writable shared-host permissions and public storage linkage.
- Do not infer publication approval from successful conversion.

## Operational checks

1. Upload representative JPEG/PNG media in UAT.
2. Confirm conversion jobs enter and leave the queue.
3. Verify WebP files, URL fallback and browser cache headers.
4. Measure representative encoded bytes against the performance budget.
5. Test missing/corrupt originals and worker timeout behavior.
6. Verify local disk consumption before bulk regeneration.
