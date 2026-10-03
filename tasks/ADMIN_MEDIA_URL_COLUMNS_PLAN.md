# Admin-uploaded media → resolved URL columns (plan)

**Status:** DONE (code) — pending `php artisan test` on a PHP host
**Executed:** 2026-10-03, commit on `arena/01a10254-rythm`
**Date:** 2026-10-03 · **Branch:** `arena/01a10254-rythm`
**Owner decisions (confirmed):**
1. **Hybrid storage** — Spatie Media Library keeps storing files + generating WebP conversions in the background; each media-bearing model also **persists the resolved URL(s) in DB columns**, and reads go column-first (Spatie remains the fallback).
2. **Import pipeline = dormant** — no deletion; code stays, but it is no longer an official catalogue source. Docs/rules only.
3. **Scope = all media resources** — products, variants, brands, categories, hero slides, homepage blocks.
4. **Existing rows = migrated** — one idempotent command backfills the columns from current media rows.

---

## 1. Why

Today the only way to get an image URL is to ask Media Library at render time
(`getAvailableUrl()` → `media` table → disk). That works, but:

- every read path pays a media query + URL build (list pages, homepage, cart);
- the admin list pulls `media` for every row just to draw a thumbnail;
- there is no single stored value an operator can see/verify in the DB.

With the columns, the answer to "what image will the site show?" is one stored
string per surface — same value the panel preview already fetches.

## 2. Target contract

| Model | Collections | New columns (nullable) |
|---|---|---|
| `Product` | `gallery` (multiple), `og` (single) | `gallery_urls` (json), `thumbnail_url`, `og_image_url` |
| `ProductVariant` | `variant_gallery` | `gallery_urls` (json), `thumbnail_url` |
| `Brand` | `logo` | `logo_url` |
| `Category` | `icon` | `icon_url` |
| `HeroSlide` | `desktop_image`, `mobile_image` | `desktop_image_url`, `mobile_image_url` |
| `HomepageBlock` | `image` | `image_url` |

Rules (to be added to `docs/media-architecture.md` as M-7/M-8):

- **Reads are column-first**: accessors return the column when set, else the
  Spatie value (legacy/unsynced row), else the committed fallback.
- **Writes are automatic**: a Media-model observer re-syncs the owning row on
  upload, delete, reorder (`order_column`) and conversion completion
  (`generated_conversions`) — so the column upgrades to the WebP URL once the
  queue has generated it.
- **Columns are a cache, never a source of truth**: no hand-editing, not
  mass-assignable (`forceFill` + `saveQuietly`, no audit-log noise).
- **Host-relative URLs only** (`/storage/...`) — the M-2 contract, so a domain
  or CDN change never invalidates stored values.
- Backfill/repair: `php artisan media:sync-urls [--only-missing] [--dry-run]`.

## 3. Work

1. Migration adding the columns above.
2. `App\Models\Contracts\HasResolvedMediaUrls` + `App\Models\Concerns\SyncsResolvedMediaUrls`
   (one place for the "resolve → compare → write quietly" mechanics).
3. Six models: implement `resolvedMediaUrls()`, make accessors column-first.
4. `App\Observers\MediaUrlObserver` registered on the configured media model
   in `AppServiceProvider`.
5. Command `media:sync-urls` (backfill, dry-run, only-missing, chunked).
6. Filament: list thumbnails read the stored URL (`ImageColumn`) instead of
   querying media; drop the now-unneeded `media` eager load; help text.
7. Consumers: `ProductController` (`ogImage()`), `HomepageDataService` (brand
   `logoUrl()`).
8. Deploy script runs the backfill (`--only-missing`) on setup/update.
9. Tests: feature coverage for upload → column → storefront read, delete,
   reorder, conversion upgrade, variant + other resources, backfill command;
   static guards in `tests/automation/media-architecture.test.mjs`.
10. Docs: `media-architecture.md`, `media-optimization.md`, `ARCHITECTURE.md`,
    `RULES.md` (+ dormant import), `MEMORY.md`, this plan's status.

## 4. Verification

- **Ran here:** `node --test tests/automation/*.test.mjs` → **186 tests, 176 pass / 10 fail**, the same 10 baseline failures as `HEAD` (0 new); 3 new guards (columns+observer+command+quiet-write+timestamp rules, admin stored-column rendering, deploy backfill) pass.
- **Not runnable here (no PHP/vendor/network):** `php artisan test` — the new
  `tests/Feature/ResolvedMediaUrlTest.php` must be run on a PHP host.
- Manual UAT: upload 2 images in `/admin` → reopen → featured image and list
  thumbnail come from the stored URL; storefront card/PDP unchanged.

## 4b. What the build turned up (and how it was handled)

| Finding | Handling |
|---|---|
| Filament's plain `ImageColumn` resolves its state as a **disk path** (`$disk->exists($state)` → `$disk->url($state)`), so a stored `/storage/…` URL renders nothing | New `App\Filament\Columns\StoredMediaUrlColumn` (returns app-relative state as-is); guarded by a static test |
| `MediaRelocationService` repoints media with `saveQuietly()`, bypassing the observer | It now syncs the owner explicitly after the move (guarded) |
| The homepage caches the resolved payload for an hour (models + brand logos), so a media change would keep rendering the URL of a deleted file | `MediaUrlObserver` flushes `HomepageDataObserver::CACHE_KEY` whenever a column actually changes (guarded) |
| The sync would bump `products.updated_at`, which drives Trending / Best Sellers ordering | The trait writes with `timestamps = false` (guarded) |

## 5. Risks / rollback

| Risk | Mitigation |
|---|---|
| Conversion finishes after the column was written | Observer re-syncs on `generated_conversions` save; column upgrades to WebP |
| Legacy rows have empty columns | Accessors fall back to Spatie; `media:sync-urls` backfills; deploy runs `--only-missing` |
| Bulk media work (import/regenerate) fires many syncs | Sync writes only when the value actually changed; importing is dormant anyway |
| Media deleted outside the app (raw SQL) | Column goes stale → `media:sync-urls` (documented repair path) |
| Rollback | Columns are additive and nullable; dropping them returns the system to pure Spatie reads (accessors keep the fallback either way) |
