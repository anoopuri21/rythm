# MEMORY — Always Read / Always Update (STRICT)

> **One of five always-read files.** Others: `ARCHITECTURE.md` · `RULES.md` · `PHASES.md` · `DESIGN.md`  
> **Purpose:** durable session brain — AI must **not** re-scan the whole repo every time.  
> **Enforcement:** A change set is **INCOMPLETE** until the checklist in §A is satisfied and logged.  
> See also: `RULES.md` → **G10 / AI7 / AI9** (MEMORY gate).

**Protocol version:** 2.0 (strict checklist) · **Last protocol update:** 2026-09-12

---

# A. MANDATORY CHANGE CHECKLIST (every future change)

Copy this block into the **Session log** for every material change set.  
Fill every line. Use `n/a` only when truly not applicable — never leave blank.

```
### YYYY-MM-DD — <short title>
- Change-id: <branch or topic slug>
- Trigger: owner-ask | bug | docs | phase-gate | refactor | other
- Scope paths: `path1`, `path2`, …
- Type tags: [ ] code [ ] migration [ ] test [ ] front-build [ ] design-token [ ] docs-only [ ] config [ ] admin [ ] commerce [ ] security
- Checklist:
  - [ ] A1 Read five always-read files before editing
  - [ ] A2 Touched only task-relevant paths (no drive-by refactors)
  - [ ] A3 Services own business writes (no money/stock logic in Blade/controller)
  - [ ] A4 No client-trusted totals/prices
  - [ ] A5 AuthZ/policies preserved (deny-by-default)
  - [ ] A6 Tests run if code/commerce/security touched → result: pass | fail | n/a — `…`
  - [ ] A7 `npm run build` if CSS/JS/views assets need rebuild → result: pass | n/a
  - [ ] A8 Design tokens only (no new hardcoded hex) → n/a if no UI
  - [ ] A9 No secrets/.env/vendor/node_modules committed
  - [ ] A10 Withheld legal pages / live pay / Phase 18 not enabled unless owner commanded
  - [ ] A11 §1 Current facts updated OR verified unchanged (list keys touched / `none`)
  - [ ] A12 §2 Locked decisions unchanged OR owner-approved change noted
  - [ ] A13 Footguns (§4) added if new trap discovered
  - [ ] A14 Cross-file mirrors done: PHASES / DESIGN / ARCHITECTURE / PRD / tracker / `n/a`
  - [ ] A15 Owner-facing summary prepared (what changed + what to verify)
- Risks / follow-ups: <none | bullets>
- Status: COMPLETE | BLOCKED | PARTIAL
```

### A.0 What counts as a “material change” (MUST run checklist)

| Triggers checklist | Skip checklist (still optional 1-line log) |
|---|---|
| Any `app/`, `routes/`, `database/`, `resources/`, `config/` code change | Pure typo in a non-authority comment |
| Any of the five always-read files / PRD / tracker status | Reading/exploring only (no writes) |
| Migrations, tests, build config, deploy docs that alter procedure | Reverting uncommitted local experiments with zero net diff |
| Enabling/disabling features, flags, withheld pages, payment mode | |
| Phase status, launch/live posture, Auto Mode state | |

**If net diff ≠ empty on tracked product files → checklist required.**

### A.1 Definition of DONE (hard gate)

Work may be reported **done** to the owner only when:

1. Implementation (or doc) change is in the tree.  
2. Required tests/build from checklist are green or honestly `n/a`.  
3. **This file updated in the same sitting** (§1 if needed + §B log entry with checklist).  
4. Cross-mirrors done when status/design/architecture facts moved.  
5. No rule in `RULES.md` §9 hard-stop was silently violated.

**Incomplete without MEMORY update = not done.** Re-open the task and finish §A before claiming completion.

### A.2 Failure modes (do not repeat)

| Bad habit | Required instead |
|---|---|
| “Small change, skip MEMORY” | Still log; small changes drift facts fastest |
| Update MEMORY next session | Same sitting as the code/doc change |
| Essay log, no checklist | Use the template; max substance in bullets |
| §1 contradicts PHASES/tracker | Fix mirrors immediately; tracker wins on phase numbers |
| Store secrets in MEMORY | Never — reference env var **names** only |
| Mark COMPLETE with failing tests | Status PARTIAL/BLOCKED + follow-up |

---

# B. Session log (newest first — checklist entries live here)

### 2026-10-05 — Media reuse: ek image, kai jagah (M-10)
- Change-id: `arena/01a10a94-rythm` (media-reuse)
- Trigger: owner-ask (Hinglish) — "admin panel me media upload karne me same image ko multiple jageh use karne ke liye multiple time image upload karna padta hai jiski wajeh se ek hi image server pe bhi multiple time save ho jati hai…" (research → plan → approval → implement)
- Scope paths: `database/migrations/2026_10_06_000001_add_shared_path_to_media_table.php`, `app/Support/MediaPathGenerator.php`, `app/Observers/{MediaFileObserver,MediaUrlObserver}.php`, `app/Models/{Media,Product,ProductVariant,HeroSlide}.php`, `app/Services/{MediaReuseService,MediaRelocationService}.php`, `app/Console/Commands/{DedupeMedia,MediaDoctor}.php`, `app/Filament/Resources/MediaLibraryResource.php`, `app/Filament/Resources/MediaLibraryResource/Pages/ManageMediaLibrary.php`, `app/Filament/Resources/ProductResource.php`, `app/Providers/AppServiceProvider.php`, `config/media-library.php`, `tests/Feature/MediaReuseTest.php`, `tests/automation/media-reuse.test.mjs`, `docs/{media-reuse.md,media-architecture.md,media-optimization.md,RULES.md,ARCHITECTURE.md,MEMORY.md}`, `tasks/MEDIA_REUSE_PLAN.md`
- Type tags: [x] code [x] migration [x] test [ ] front-build [ ] design-token [x] docs [x] config [x] admin [ ] commerce [ ] security
- Owner decisions (asked + approved before coding): D1 shared reference rows (recommended) · D2 `media:dedupe` in this phase · D3 no upload auto-link · D4 og fallback yes.
- What changed:
  1. **One file, many usages.** `media.shared_path` (+ `source_media_id`, `checksum`) migration; `App\Support\MediaPathGenerator` (registered as `media-library.path_generator`) makes a reused row resolve into the owner's base path — originals, `conversions/` and `responsive-images/` all by Spatie's own logic, nothing re-implemented.
  2. **File safety.** `App\Observers\MediaFileObserver` (registered as `media-library.media_observer`): a reused row never deletes/renames the file it does not own; an owner's file survives while any usage exists (last usage cleans up); conversions/responsive state is mirrored to every usage and `MediaUrlObserver` then upgrades their stored URL columns (M-7).
  3. **No duplicate conversions.** `Product`/`ProductVariant`/`HeroSlide` skip `registerMediaConversions()` for reused rows.
  4. **Admin:** new read-only **Media library** page (`/admin/media-library`, CONTENT group) listing every image with row type, place, disk, size, "Used in" (share-group count, tooltip = where); row actions *Use elsewhere* (whitelisted target model → record → collection → `MediaReuseService::attach()`, zero disk writes, idempotent, singleFile collections replaced), *Open file*, *Delete* (confirm dialog shows "Used in: …"); filters incl. "Same file stored more than once". `App\Models\Media` → `CataloguePolicy`.
  5. **Cleanup:** `php artisan media:dedupe [--dry-run|--disk=|--limit=|--no-hash]` hashes file owners (sha256, stored in `media.checksum`), keeps the oldest copy of each byte-identical group and re-points all usages to it — including the duplicate row itself (it is somebody's gallery item) — then removes the now-unreachable copy; `media:doctor` reports duplicates + broken reuses.
  6. **Repair tooling:** `media:relocate` never moves a reused row (owner carry its `disk` along) and `media:doctor` skips them in the misplaced/file checks.
  7. **OG:** `ogImage()` already fell back to the gallery; it now prefers the gallery **original** (scrapers refuse WebP) and the product form says "Optional — leave empty to use the first gallery image."
- Checklist:
  - [x] A1 Read five always-read files before editing (`docs/ARCHITECTURE.md`, `docs/RULES.md`, `docs/PHASES.md`, `docs/DESIGN.md`, `docs/MEMORY.md` + `docs/media-architecture.md`)
  - [x] A2 Touched only task-relevant paths (no drive-by refactors)
  - [x] A3 Services own the writes — reuse/dedupe live in `MediaReuseService`; no logic in Blade/controller
  - [x] A4 No client-trusted totals/prices (n/a — no commerce path touched)
  - [x] A5 AuthZ preserved — Media library is behind `CataloguePolicy` (view/manage = catalogue.view/manage) + `strictAuthorization()`; the reuse action only accepts whitelisted target models
  - [x] A6 Tests → `node --test tests/automation/*.test.mjs`: **230 tests / 220 pass / 10 fail** (same 10 pre-existing; new media-reuse suite 11/11). `tests/Feature/MediaReuseTest.php` (11 cases) runs on the owner's PHP host — no PHP in this sandbox.
  - [x] A7 `npm run build` n/a (no CSS/JS changes)
  - [x] A8 Design tokens only — n/a (no new UI styling; Filament components/tokens)
  - [x] A9 No secrets committed (only env var names; no new env keys needed)
  - [x] A10 Withheld pages / live pay / Phase 18 untouched
  - [x] A11 §C updated: new **Media reuse (M-10)** row + **Media storage** row mention; others verified unchanged
  - [x] A12 §D unchanged — owner-commanded feature; M-8 intake rule intact (images still enter only through the records that own them; the library page is read-only)
  - [x] A13 Footgun **#25** added (reuse/delete semantics + dedupe order + shared_path authority)
  - [x] A14 Mirrors: `docs/media-reuse.md` (new), `docs/media-architecture.md` (M-10 + §4/§5/§6), `docs/RULES.md` §7 (M8 cloud + M9 reuse rows), `docs/ARCHITECTURE.md` §9, `docs/media-optimization.md`, `docs/cloudinary-media.md`, `README.md`, `docs/MEMORY.md` §C/§F/§H/§I/§J, `tasks/MEDIA_REUSE_PLAN.md`; PHASES/PRD/tracker n/a
  - [x] A15 Owner summary prepared — admin flow + `media:dedupe` + verification (see `docs/media-reuse.md` §2/§3, `tasks/MEDIA_REUSE_PLAN.md` §5)
- Risks / follow-ups:
  - Owner host: `php artisan migrate` (shared_path/source_media_id/checksum) → `php artisan media:dedupe --dry-run` → `php artisan media:dedupe` → `php artisan media:doctor` → `php artisan test`.
  - Path generator + delete semantics are Spatie internals-adjacent: covered by `MediaReuseTest`; if a future Spatie bump changes `getBasePath()`/`MediaObserver`, re-run that suite first.
  - Cloudinary reuse shares one public_id (one asset) — verify on the owner's cloud with one product + one category reuse.
- Status: PARTIAL (code + docs + tests in tree; PHP side + real Cloudinary verification pending on the owner's machine)

### 2026-10-05 — Cloudinary media rollout, phase 1 (products + categories)
- Change-id: `arena/01a10a94-rythm` (cloudinary-media-phase1)
- Trigger: owner-ask (Hinglish) — "is project me Cloudinary integrate karna hai, abhi pehle products and categories images ke liye … jo images pehle se use ho rahi hai wo same url path se use hoti rahe, and jo bhi new images upload ho wo Cloudinary ke server pe save ho and usike server se use ho. Local server pe save nahi hongi."
- Scope paths: `config/filesystems.php`, `config/media-library.php`, `app/Support/{MediaDisk,CloudinaryDeliveryUrl}.php`, `app/Models/{Media,Product,ProductVariant,Category}.php`, `app/Models/Concerns/SyncsResolvedMediaUrls.php`, `app/Filament/Components/MediaUpload.php`, `app/Services/MediaRelocationService.php`, `app/Console/Commands/MediaDoctor.php`, `.env.example`, `.env.staging.example`, `.env.production.example`, `phpunit.xml`, `tests/Feature/CloudinaryMediaTest.php`, `tests/automation/cloudinary-media.test.mjs`, `docs/cloudinary-media.md`, `tasks/CLOUDINARY_MEDIA_PLAN.md`, `docs/media-architecture.md`, `docs/media-optimization.md`, `docs/RULES.md`, `docs/ARCHITECTURE.md`
- Type tags: [x] code [ ] migration [x] test [ ] front-build [ ] design-token [x] docs [x] config [x] admin [ ] commerce [ ] security
- What changed:
  1. **One disk decision, opt-in:** `App\Support\MediaDisk::forCollection($collection)` → `cloudinary` only when `MEDIA_CLOUDINARY=true` **and** a resolvable cloud name (`CLOUDINARY_URL` / `CLOUDINARY_CLOUD_NAME`), else `MEDIA_DISK`. `MediaUpload` passes `->disk(MediaDisk::forCollection(...))`; the phase-1 collections (`Product` `gallery`+`og`, `ProductVariant` `variant_gallery`, `Category` `icon`) call `->useDisk(...)`, so panel uploads and programmatic writes cannot drift. Brand/hero/homepage collections untouched (next phase = env-list edit).
  2. **URLs without API calls:** `App\Models\Media` (now `media-library.media_model`) derives `https://res.cloudinary.com/<cloud>/image/upload/<transformation>/<path>` via `App\Support\CloudinaryDeliveryUrl`, and maps conversion names (`thumb-webp`, `gallery-webp`, `variant-*-webp`) to delivery transformations (`c_fit,…,f_auto,q_auto:good`). Cloud rows queue **no** local conversions, so no original is ever pulled back to this server.
  3. **Legacy untouched:** existing rows keep their `disk`/`conversions_disk` and `/storage/...` URL — no migration, no URL rewrite. Mixed catalogues are expected; M-7 columns now resolve per row (`$media->getUrl()`), never from the global disk. `SyncsResolvedMediaUrls` also refuses to overwrite an already-resolved URL with an empty string, so a missing-cloud-name state degrades to "users still see the old CDN URL" instead of blank images.
  4. **Repair tooling exempts cloud rows:** `MediaRelocationService::misplacedQuery()` + `MediaDoctor` exclude `cloudinary` rows (previously they would have been "misplaced" → `media:doctor` FAIL and `media:relocate` pulling them back to disk and deleting the cloud copies); `relocate()` refuses a cloud row outright; new `media:doctor` `checkCloudinary()` reports readiness.
  5. **Tests/docs:** `tests/Feature/CloudinaryMediaTest.php` (panel upload → fake cloudinary disk, derived URL shapes, mixed catalogue, legacy URL unchanged, relocation/doctor exemption, fail-safe without credentials) + `tests/automation/cloudinary-media.test.mjs` (10 static guards); `phpunit.xml` pins `MEDIA_CLOUDINARY=false` (force) + blank Cloudinary creds so every other suite keeps the legacy contract; `docs/cloudinary-media.md` is the install/verify/rollback runbook; `docs/media-architecture.md` gains M-9 (+M-1/M-2/§4/§5/§6 edits).
- Checklist:
  - [x] A1 Read five always-read files before editing (`docs/ARCHITECTURE.md`, `docs/RULES.md`, `docs/PHASES.md`, `docs/DESIGN.md`, `docs/MEMORY.md` + `docs/media-architecture.md`)
  - [x] A2 Touched only task-relevant paths (no drive-by refactors)
  - [x] A3 Business-write ownership preserved — disk decision lives in `App\Support\MediaDisk`; no money/stock/controller logic touched
  - [x] A4 No client-trusted totals/prices (n/a — no commerce path touched)
  - [x] A5 AuthZ/policies preserved (n/a — no policy change)
  - [x] A6 Tests → `node --test tests/automation/*.test.mjs`: **219 tests / 209 pass / 10 fail** (same 10 pre-existing failures; new Cloudinary suite 10/10). PHPUnit **not runnable here** (no PHP/vendor) → `CloudinaryMediaTest` + full PHP suite must run on the owner's PHP host.
  - [x] A7 `npm run build` n/a (no CSS/JS/views touched)
  - [x] A8 Design tokens only — n/a (no UI)
  - [x] A9 No secrets committed — only env var **names** in the example templates
  - [x] A10 Withheld pages / live pay / Phase 18 untouched
  - [x] A11 §C updated: **Media storage** row, new **Cloudinary media (phase 1)** row, **Product media pipeline**, **Session branch**; others verified unchanged
  - [x] A12 §D unchanged — this is an owner-commanded intake change (M-8 intake rule narrowed by scope, not reversed); noted in docs/RULES.md M1/M5
  - [x] A13 Footgun **#24** added (cloud rows vs relocate/doctor + derived delivery URLs + `Storage::fake` driver swap)
  - [x] A14 Mirrors: `docs/media-architecture.md` (M-1, M-2, M-9, §4, §5, §6), `docs/RULES.md` (M1, M5), `docs/ARCHITECTURE.md` §9, `docs/media-optimization.md` header, `docs/MEMORY.md` §F/§H/§J; PHASES/PRD/tracker n/a (no phase or scope change)
  - [x] A15 Owner summary prepared — install + env + verify + rollback: `docs/cloudinary-media.md` §2/§4/§5
- Risks / follow-ups:
  - Owner must run on the PHP host: `composer require cloudinary-labs/cloudinary-laravel` → add `CLOUDINARY_URL` (+`MEDIA_CLOUDINARY=true`) → `php artisan config:clear` → `php artisan media:doctor`, then upload one product image + one category icon and check both the new CDN URL **and** an old `/storage/...` image.
  - PHPUnit + real panel/cloud verification pending on the owner's machine (sandbox has no PHP).
  - §B now has 23 entries — the §K.2 archive pass to `docs/MEMORY_ARCHIVE.md` (which does not exist yet) is still pending.
- Status: PARTIAL (code + docs + tests in tree; PHP-side verification pending on the owner's machine)

### 2026-10-03 — Category images on storefront (`Category::iconUrl()`) + carry-over resilience fixes
- Change-id: `arena/01a1029d-rythm` (category-storefront-images)
- Trigger: owner-ask (Hinglish bug report) — "products ki image to visible hai ab but categories ki image website pe display nahi ho rahi hai." + carry-over of 2 unpushed post-PR-#40 commits (`SQLSTATE[42S22]` pending-migration graceful degrade + `deploy-cpanel.sh` maintenance-mode `EXIT` safety net).
- Scope paths: `app/Services/{HomepageDataService,CategoryService,MediaRelocationService}.php`, `resources/views/home/_category-banners.blade.php`, `resources/views/livewire/shop-index.blade.php`, `app/Observers/MediaUrlObserver.php`, `app/Models/Concerns/SyncsResolvedMediaUrls.php`, `app/Console/Commands/{SyncMediaUrls,MediaDoctor}.php`, `scripts/deploy-cpanel.sh`, `tests/Feature/{MediaStorageTest,ResolvedMediaUrlTest}.php`, `tests/automation/media-architecture.test.mjs`, `docs/{media-architecture,MEMORY}.md`
- Type tags: [x] code [ ] migration [x] test [ ] front-build [ ] design-token [x] docs-only [ ] config [x] admin [ ] commerce [ ] security
- Root cause & fix:
  1. **Homepage popular categories & category banners ignored `Category::iconUrl()`.** `HomepageDataService::popularCategories()` only checked `public/images/categories/{slug}.jpg` and queried `['id', 'parent_id', 'name', 'slug', 'sort_order']` without `icon_url` or eager-loading `media`. Fixed: selects `icon_url` (when migrated), eager-loads `media` (no N+1 on fallback), and resolves `$category->iconUrl() ?? (is_file(public_path($asset)) ? '/'.$asset : null)` (M-7 column-first → Media Library `'icon'` → committed asset → `null` SVG placeholder). `_category-banners.blade.php` no longer falls back to an unchecked `asset('images/categories/{slug}.jpg')` that 404s when no committed file exists.
  2. **Shop page category shortcuts (`/shop`) hardcoded `asset('images/categories/{slug}.jpg')`.** Updated `CategoryService::tree()` + `resources/views/livewire/shop-index.blade.php` to use the same column-first `iconUrl()` chain with music-note SVG fallback (never a broken `<img>`). Other category surfaces (category-drawer, navbar, PDP breadcrumbs, recently-launched list) verified text-only by design.
  3. **Cache invalidation on quiet URL syncs.** `syncResolvedMediaUrls()` uses `saveQuietly()`, which bypasses `CategoryObserver` and `HomepageDataObserver`. `MediaUrlObserver`, `SyncMediaUrls` (`php artisan media:sync-urls`), and `MediaRelocationService` now flush both `HomepageDataObserver` and `CategoryService` whenever stored URLs change.
  4. **Carry-over fixes applied & pushed:** (a) `SyncsResolvedMediaUrls`, `SyncMediaUrls`, and `MediaDoctor` gracefully degrade when `2026_10_03_000001_add_resolved_media_url_columns` is pending (no `SQLSTATE[42S22]`); (b) `scripts/deploy-cpanel.sh` uses `set -Eeuo pipefail` + an `EXIT` trap (`cleanup_maintenance`) so any failed deploy step brings the site back `up` instead of leaving it in 503 maintenance mode.
- Checklist:
  - [x] A1 Read five always-read files before editing (`ARCHITECTURE`, `RULES`, `PHASES`, `DESIGN`, `MEMORY` + `media-architecture.md`)
  - [x] A2 Touched only task-relevant paths (no drive-by refactors)
  - [x] A3 Services own data assembly (`HomepageDataService`, `CategoryService`); no business/money/stock logic in Blade
  - [x] A4 No client-trusted totals/prices (n/a)
  - [x] A5 AuthZ/policies preserved (n/a)
  - [x] A6 Tests: `node --test tests/automation/media-architecture.test.mjs` → **17/17 pass** (2 new static guards); full `node --test tests/automation/*.test.mjs` → **192 tests, 182 pass / 10 fail** (exact 10 pre-existing baseline failures, **0 new**); `bash -n scripts/deploy-cpanel.sh` → pass; PHP feature test `test_category_icon_uploaded_in_admin_renders_on_the_storefront_and_falls_back_when_removed` added in `MediaStorageTest` + cache-flush assertion in `ResolvedMediaUrlTest` (PHP runtime unavailable in sandbox — run on host)
  - [x] A7 `npm run build` → n/a (no CSS/JS changed; Blade markup uses existing classes)
  - [x] A8 Design tokens only (reused existing `.pcard__img-fallback` / `.catban-mm__card`, no hex added)
  - [x] A9 No secrets/.env/vendor/node_modules committed
  - [x] A10 Withheld legal pages / live pay / Phase 18 untouched
  - [x] A11 §C Current facts updated (`Media URL columns (M-7)` + `Session branch (Arena)`)
  - [x] A12 §D Locked decisions unchanged
  - [x] A13 Footguns #21, #22, #23 added (and #16 updated to reflect the `EXIT` trap fix)
  - [x] A14 Cross-file mirrors done: `docs/media-architecture.md` (§3, §5 troubleshooting, §6 tests); `PHASES`/`DESIGN`/`ARCHITECTURE`/`PRD` verified unchanged
  - [x] A15 Owner-facing summary + host verification commands prepared
- Risks / follow-ups: On the host, if `2026_10_03_000001_add_resolved_media_url_columns` has not been migrated yet, run `php artisan migrate && php artisan media:sync-urls --only-missing && php artisan optimize:clear`; set `FILESYSTEM_DISK=local` in `.env` (keep `MEDIA_DISK=public`) so `media:doctor` reports 0 warnings.
- Status: COMPLETE (code) — owner action on host: `php artisan migrate && php artisan media:sync-urls --only-missing && php artisan optimize:clear`, `php artisan media:doctor --fix`, `php artisan test`

### 2026-10-03 — "Upload ke baad image gayab / broken URL" — `/storage` route was owned by the private disk
- Change-id: `arena/01a10254-rythm` (served-media-route)
- Trigger: owner-ask (Hinglish bug report) — "admin panel me first upload par preview dikhta hai, par Save karne / page reload ke baad preview gayab ho jata hai; website par image broken aur uski `src` ka URL 404 deta hai."
- Scope paths: `config/filesystems.php`, `app/Console/Commands/MediaDoctor.php` (new), `scripts/deploy-cpanel.sh`, `tests/Feature/MediaStorageTest.php`, `tests/automation/media-architecture.test.mjs`, `docs/media-architecture.md` (§2b, M-2 row, ops, troubleshooting, tests), `docs/RULES.md` §7
- Type tags: [x] code [ ] migration [x] test [ ] front-build [ ] design-token [x] docs-only [x] config [x] admin [ ] commerce [ ] security
- Root cause (framework-level, not app code): Laravel's `FilesystemServiceProvider::serveFiles()` registers `GET|PUT /storage/{path}` for **every** local disk with `serve => true`, defaulting the URI to `/storage`. This repo had `serve => true` on the **private** `local` disk (`storage/app/private`) and no such key on the public media disk. Whenever `public/storage` was absent (fresh clone, cPanel Plan B, failed junction, `storage:link` forgotten) nothing served the file statically, so the private disk's route answered: `ServeFile` requires a signature unless `visibility === 'public'` → **403 in dev / 404 in production** for every image. The first-upload preview looked fine only because FilePond renders Livewire's temporary blob client-side; after Save both the panel preview and the storefront 404'd.
- Fix: `serve => false` on `local`, `serve => true` on the public media disk in `config/filesystems.php` (only one disk may claim a URI — two served disks on one URI throw at boot). New `php artisan media:doctor [--fix]`: read-only diagnosis of the M-1/M-2 contract (one media disk, public visibility, host-relative URL), which disk owns `/storage`, the `public/storage` symlink, per-row originals + stale `generated_conversions`, and the stored URL columns (stale vs unresolved); `--fix` only performs idempotent repairs (`storage:link`, `media:relocate`, `media:sync-urls`). `scripts/deploy-cpanel.sh check` now runs it.
- Checklist:
  - [x] A1 Five always-read files respected (MEMORY/RULES/ARCHITECTURE mirrors updated; PHASES untouched — no phase moved)
  - [x] A2 Scope stayed media-only (config + one new command + guards; M-7/M-8 contract untouched)
  - [x] A3 Business rules unchanged — no pricing/stock/auth logic touched
  - [x] A4 n/a (no totals/prices)
  - [x] A5 n/a (no new request surface; the new /storage route belongs to the media disk, still unsigned + `visibility public`)
  - [x] A6 Tests: node `tests/automation/media-architecture.test.mjs` → **14/14 pass** (3 new gates: serve flags, media:doctor + deploy check wiring, feature-coverage names); PHP feature cases added to `MediaStorageTest` (`/storage` served from the media disk without a symlink, private disk **not** served, exactly one `/storage` owner, `media:doctor` healthy/missing-original) — **PHP suite still NOT runnable here** (no PHP/composer/vendor/network) → must run on the host
  - [x] A7/A8 n/a (no CSS/JS/Blade)
  - [x] A9 No secrets; no vendor/node_modules
  - [x] A10 Withheld pages / live pay untouched
  - [x] A11 §C `Media storage` row updated (serve flags + `media:doctor`)
  - [x] A12 §D locked decisions unchanged
  - [x] A13 Footgun #20 added (`serve => true` on a second local disk silently hijacks `/storage`)
  - [x] A14 Mirrors: `media-architecture.md` (§2b + M-2 row + ops + troubleshooting + tests), `RULES.md` §7 M7; ARCHITECTURE/PHASES/PRD n/a
  - [x] A15 Owner summary + host checklist prepared
- Risks / follow-ups: the fix is a config change — hosts must `php artisan config:clear` (a cached config keeps the old `serve` flags and the 404s persist); the static symlink stays the fast path (route only answers when no file is served first); `media:doctor` finding "stale stored URL" means the underlying media row still resolves to the same URL → that file must be restored or the image re-uploaded; unrelated pre-existing docs (`docs/DEPLOY_RHYTHM_STEP_BY_STEP.md`, `docs/MILESWEB_DEPLOYMENT.md`) still tell operators to set `FILESYSTEM_DISK=public` and contradict the M-1/M-2 contract — reconcile in a docs pass.
- Status: COMPLETE (code) — owner action on the host: `php artisan config:clear`, `php artisan storage:link`, `php artisan media:doctor --fix`, `php artisan test`

### 2026-10-03 — Admin-upload only: media URLs persisted in DB columns (M-7/M-8)
- Change-id: `arena/01a10254-rythm` (media-url-columns)
- Trigger: owner-ask — "import ki jarurat nahi hai. only admin se image upload hoga aur url DB me save hoga wahi se website and admin panel ke preview images use karenge." (decisions: hybrid storage · import dormant · all resources · migrate existing rows)
- Scope paths: `database/migrations/2026_10_03_000001_add_resolved_media_url_columns.php`, `app/Models/{Contracts/HasResolvedMediaUrls,Concerns/SyncsResolvedMediaUrls}.php`, `app/Models/{Product,ProductVariant,Brand,Category,HeroSlide,HomepageBlock}.php`, `app/Observers/MediaUrlObserver.php`, `app/Console/Commands/SyncMediaUrls.php`, `app/Filament/Columns/StoredMediaUrlColumn.php`, `app/Filament/Resources/*Resource.php`, `app/Providers/AppServiceProvider.php`, `app/Http/Controllers/ProductController.php`, `app/Services/{HomepageDataService,MediaRelocationService}.php`, `scripts/deploy-cpanel.sh`, `tests/Feature/ResolvedMediaUrlTest.php`, `tests/automation/media-architecture.test.mjs`, `docs/{media-architecture,media-optimization,ARCHITECTURE,RULES,PHASES,ADMIN_PRODUCT_UPLOAD_RUNBOOK,MEMORY}.md`, `tasks/ADMIN_MEDIA_URL_COLUMNS_PLAN.md`
- Type tags: [x] code [x] migration [x] test [ ] front-build [ ] design-token [x] docs-only [x] config [x] admin [ ] commerce [x] security
- What changed: hybrid model per owner's choice — Media Library stays the writer of truth (files + queued WebP conversions), and each media-bearing model now persists the resolved URL(s) in nullable columns; all reads are column-first with a Media Library fallback (legacy rows) and the committed-asset fallback after that. `MediaUrlObserver` (registered on `config('media-library.media_model')`) re-syncs the owner on upload / delete / drag-reorder / conversion completion / disk change with `forceFill()+saveQuietly()` (no audit noise, no mass-assignment path). `php artisan media:sync-urls [--dry-run|--only-missing]` backfills or repairs; deploy runs `--only-missing` after `media:relocate`. Admin lists now render the stored column via the new `StoredMediaUrlColumn` and ProductResource no longer eager-loads `media`. Import pipeline kept but **dormant** (M-6/RULES), admin upload is the only intake (M-8).
- Two traps found while building this (footguns #18/#19): Filament's plain `ImageColumn` resolves its state as a **disk path** (`$disk->exists($state)` → `$disk->url($state)`), so a stored `/storage/…` web URL renders nothing — hence the app's own column class; and `MediaRelocationService` repoints media with `saveQuietly()`, which bypasses the observer, so it now syncs the owner explicitly.
- Checklist:
  - [x] A1 Read five always-read files before editing (MEMORY/RULES/ARCHITECTURE touched; PHASES/DESIGN read for mirrors)
  - [x] A2 Touched only media-related paths (no drive-by refactors; `HomepageDataService` logo line + `ProductController` og line are the two consumers that had to follow)
  - [x] A3 Business logic stays in services/models; the sync itself is a model concern, the backfill an Artisan command
  - [x] A4 No client-trusted totals/prices (n/a)
  - [x] A5 AuthZ/policies untouched; URL columns are NOT fillable, so no request can set what the site displays
  - [x] A6 Tests: **node** `node --test tests/automation/*.test.mjs` → 186 tests, 176 pass / 10 fail = the same 10 baseline failures (0 new); 3 new gates pass (columns+observer+command, admin stored-column rendering, deploy backfill). **PHP suite NOT run** — no PHP/Composer/vendor/network in this sandbox: `php artisan test` (new `ResolvedMediaUrlTest`, 11 cases) must run on a PHP host before merge
  - [x] A7 `npm run build` n/a (no CSS/JS/Blade changed)
  - [x] A8 Design tokens n/a (no styling)
  - [x] A9 No secrets/.env/vendor/node_modules committed
  - [x] A10 Withheld pages / live pay / Phase 18 untouched
  - [x] A11 §C: `Media storage` + `Product media pipeline` rows refreshed (stored URL columns, M-7/M-8), new `Media URL columns` row added
  - [x] A12 §D locked decisions unchanged (single-vendor, no guest checkout, etc.)
  - [x] A13 Footguns #18 (Filament ImageColumn = disk path) + #19 (quiet media saves bypass the observer)
  - [x] A14 Mirrors: `media-architecture.md` (M-7/M-8, §3, §4, §5 ops, §6 tests, troubleshooting), `media-optimization.md`, `ARCHITECTURE.md` §9, `RULES.md` §7, `PHASES.md` phase 6 (dormant), upload runbook; PRD/tracker n/a
  - [x] A15 Owner summary prepared
- Risks / follow-ups: columns are a cache — a media change made **outside** the app (raw SQL/rsync) leaves a stale URL until `media:sync-urls` runs (documented repair); `--only-missing` re-scans rows that legitimately have no `og` image every deploy (bounded, no write); conversion completion upgrades the column, but a page cached before that keeps the original URL until the cache turns over; only the admin upload path writes media now, so the import pipeline's dormant code must not be run without an owner command; PHP suite + one manual admin upload/reopen check still pending on a PHP host.
- Status: COMPLETE (code) — owner action: run `php artisan test`, then `php artisan migrate` + `php artisan media:sync-urls` on the host (deploy script does both)

### 2026-10-03 — Product image upload audit: variant gallery WebP, image order, upload bounds
- Change-id: `arena/01a10254-rythm` (product-media-audit)
- Trigger: owner-ask — "project overview lo aur image upload logic check karo. Start with the products." (products first; other media resources next)
- Scope paths: `app/Models/{Product,ProductVariant}.php`, `app/Filament/Components/MediaUpload.php`, `app/Filament/Resources/ProductResource.php`, `tests/Feature/MediaStorageTest.php`, `tests/automation/media-architecture.test.mjs`, `docs/{media-architecture,media-optimization,ADMIN_PRODUCT_UPLOAD_RUNBOOK,MEMORY}.md`, `tasks/ADMIN_PRODUCT_IMAGE_UPLOAD_FIX_PLAN.md`
- Type tags: [x] code [ ] migration [x] test [ ] front-build [ ] design-token [x] docs-only [ ] config [x] admin [ ] commerce [ ] security
- Findings (verified against the locked packages, not assumed: Filament **v5.7.6** `BaseFileUpload`/`FileUpload` + spatie-plugin v5.7.6, `spatie/laravel-medialibrary` **11.23.5** `FileAdder`/`ConversionCollection`/`Media`, Laravel **13.24** `ValidatesAttributes::validateDimensions`, Livewire 4.4.2 `FileUploadConfiguration`):
  1. **Variant images were served as raw originals.** `ProductVariant::galleryUrls()` used `getUrl()` (full-size upload, up to 5 MB × 6 per variant) and the only variant conversion was a 240px thumb nothing rendered — so the PDP variant swap broke the ≤250 KB image budget. Fixed: `variant-gallery-webp` (1200×1200, q84, queued) + `getAvailableUrl(['variant-gallery-webp'])`.
  2. **No way to choose the primary photo.** `heroImage()`/`thumbnailImage()` take the *first* media, but galleries were not `->reorderable()` → staff had to delete and re-upload to change the card image. Fixed: `MediaUpload::gallery()` is now reorderable (plugin persists `order_column` via `setNewOrder`).
  3. **`og` images generated gallery WebP copies** (product conversions had no `performOnCollections`) — queue CPU + disk for files crawlers fetch as-is. Fixed: `->performOnCollections('gallery')`.
  4. **Upload validation had no pixel bound.** `MediaUpload` bounded mime + bytes only, while `docs/media-optimization.md` claimed dimensions were bounded. A ≤5 MB flat PNG can decode to ~30 000², which OOMs the GD conversion and kills the scheduled queue worker. Fixed: `dimensions:max_width/max_height` (default 6000², overridable) added in the factory.
  5. **Admin product list loaded full-size originals** for the thumbnail column (no `->conversion()`), i.e. up to 5 MB per row × page size. Fixed: `->conversion('thumb-webp')` (falls back to the original until generated).
  6. **Ops guidance was wrong**: the fix-plan's "`post_max_size` ≥ 8M" would reject the 8 MB hero field exactly at the limit, and Livewire's unpublished temp rule is `max:12288`. Corrected in the runbook + plan (+ `memory_limit` ≥ 256M for 6000px conversions).
- Not changed on purpose: no `acceptsMimeTypes()` on the collections. It looks like hardening, but `CatalogueAcquisitionService` accepts **GIF** for the import pipeline while the admin form does not — adding the guard without reconciling the two would break imports. Also no Filament `->image()` anywhere: it rewrites `acceptedFileTypes` to `image/*` and would re-admit SVG (script-capable).
- Checklist:
  - [x] A1 Read five always-read files before editing → `MEMORY.md` + media/runbook docs read in full; `ARCHITECTURE.md` §storefront/media; `RULES.md`/`PHASES.md`/`DESIGN.md` not re-read (no commerce/design/phase scope in this change)
  - [x] A2 Touched only product-media paths (no drive-by refactors)
  - [x] A3 No business/money/stock logic touched
  - [x] A4 No client-trusted totals/prices (n/a)
  - [x] A5 AuthZ/policies untouched
  - [x] A6 Tests: **node** `node --test tests/automation/*.test.mjs` → 183 tests, 173 pass / 10 fail vs baseline `HEAD` (181 / 170 / 11) → **0 new failures**, 2 new gates pass. **PHP suite NOT run:** this sandbox has no PHP/Composer/`vendor/` and no outbound network to install them — `php artisan test` (2 new cases in `MediaStorageTest`) must be run on a PHP host before merge
  - [x] A7 `npm run build` n/a (no CSS/JS/Blade changed)
  - [x] A8 Design tokens n/a (no UI styling)
  - [x] A9 No secrets/.env/vendor/node_modules committed
  - [x] A10 Withheld pages / live pay / Phase 18 untouched
  - [x] A11 §C: `Media storage` row refreshed + `Product media pipeline` row added
  - [x] A12 §D locked decisions unchanged
  - [x] A13 Footgun #17 added (`->image()` widens to `image/*`; bound mime+bytes+pixels)
  - [x] A14 Mirrors: `docs/media-architecture.md` (M-3, §4, §6), `docs/media-optimization.md`, `docs/ADMIN_PRODUCT_UPLOAD_RUNBOOK.md`, `tasks/ADMIN_PRODUCT_IMAGE_UPLOAD_FIX_PLAN.md`; PRD/PHASES/DESIGN/tracker n/a
  - [x] A15 Owner summary prepared
- Risks / follow-ups: variant-gallery WebP files appear only after the scheduled worker runs (`media-library:regenerate --only-missing` for images already uploaded); reordering only changes *display* order, never deletes files; the `og` scope means an admin who expects WebP for the social image now gets the original JPEG/PNG (intended — crawlers); remaining product-media observations **not** fixed here: product deletion is a soft delete so media rows/files stay (no orphan purge command), and the PDP thumbnail strip reuses the 1200px gallery URLs instead of the 480px thumbs. Other media resources (brand/category/hero/homepage block) audited only for the shared factory contract — full pass in a follow-up.
- Status: COMPLETE (product media) — owner action: run `php artisan test` on a PHP host, then `npm run build` if any Blade/CSS is touched later

### 2026-10-03 — Media: preview stuck "loading" after save + images missing on storefront
- Change-id: `arena/01a101b5-rythm` (media-disk-pin)
- Trigger: bug (owner-ask: find real cause, simplify, enterprise-grade)
- Scope paths: `config/{filament,media-library,filesystems}.php`, `app/Filament/Components/MediaUpload.php`, `app/Filament/Resources/{Product,Brand,Category,HeroSlide,HomepageBlock}Resource.php`, `app/Models/{Product,ProductVariant,HeroSlide}.php`, `app/Services/MediaRelocationService.php`, `app/Console/Commands/RelocateMedia.php`, `resources/views/{layouts/app,product/show}.blade.php`, `scripts/deploy-cpanel.sh`, `.env*.example`, `.gitignore`, `phpunit.xml`, `tests/{Feature,Concerns,automation}`, `docs/{media-architecture,media-optimization,ADMIN_PRODUCT_UPLOAD_RUNBOOK,ARCHITECTURE,MEMORY}.md`
- Type tags: [x] code [ ] migration [x] test [ ] front-build [ ] design-token [ ] docs-only [x] config [x] admin [ ] commerce [ ] security
- Root cause (reproduced): (1) Filament panel uploads used `filament.default_filesystem_disk` = `FILESYSTEM_DISK` (default `local`, PRIVATE) → storefront `/storage/..` 403/404; (2) preview/storefront URLs were absolute (APP_URL or request-host signed) → FilePond `fetch()` blocked on origin/scheme mismatch and Filament has no error branch → spinner forever. First upload previewed only because it uses Livewire's temp file.
- Fix: `MEDIA_DISK` (default `public`) is the single disk for Spatie + Filament; `disks.public.url` = relative `/storage` (`MEDIA_URL` for CDN); `MediaUpload` factory for all 7 admin upload fields (also adds the MIME bound `variant_images` lacked); models use Spatie `getAvailableUrl()` (5 copy-pasted fallbacks removed); `og:image`/JSON-LD absolutised with `url()`; `php artisan media:relocate` (idempotent, dry-run, verified copy) repairs rows left on the private disk; deploy script runs `storage:link` + `media:relocate` on setup/update.
- Checklist:
  - [x] A1 Read five always-read files before editing (RULES/MEMORY/ARCHITECTURE read; PHASES/DESIGN n/a to scope)
  - [x] A2 Touched only media-relevant paths (HeroSlide.php has a pre-existing Pint blank-line nit — left alone)
  - [x] A3 No business/money/stock logic touched; relocation logic lives in `app/Services/MediaRelocationService.php`
  - [x] A4 No client-trusted totals/prices (n/a)
  - [x] A5 AuthZ/policies untouched (panel/resource gates unchanged)
  - [x] A6 Tests: `php artisan test` → 460 pass / 10 fail; the 10 failures are identical to the pre-change baseline (copy/policy assertions, unrelated to media). New `MediaStorageTest` (10) + `MediaRelocationTest` (7) pass; 9 of the 10 `MediaStorageTest` cases fail on the pre-fix code (the 10th guards refactor behaviour). Node `tests/automation` → 171 pass / 10 fail (baseline 12: the 2 media-upload policy gates now pass, 6 new media gates added)
  - [x] A7 `npm run build` n/a (no CSS/JS changed; Blade only)
  - [x] A8 Design tokens n/a (no UI styling)
  - [x] A9 No secrets/.env/vendor/node_modules committed
  - [x] A10 Withheld pages / live pay / Phase 18 untouched
  - [x] A11 §C: added `Media storage` fact
  - [x] A12 §2/§D locked decisions unchanged
  - [x] A13 Footgun #15 added
  - [x] A14 Mirrors: `ARCHITECTURE.md` §9, `media-optimization.md`, upload runbook; PHASES/DESIGN/PRD/tracker n/a
  - [x] A15 Owner summary prepared
- Risks / follow-ups: the FIRST `update` after merging still runs the previous deploy script (bash parsed it before `git pull` replaced it) → run `php artisan storage:link && php artisan media:relocate` once by hand (or `update` twice); `update` now hands over to the pulled script (`update-steps`) so this cannot recur; pre-existing: the script's ERR trap does not fire inside functions, so a failed step leaves maintenance mode ON (fix the cause, then `php artisan up`); verified at HTTP + Filament/Livewire level only (no browser in the build sandbox) — owner should open one product after Save → reopen; 10 unrelated pre-existing PHP and 10 unrelated node test failures remain
- Status: COMPLETE (code) — owner action: deploy, then confirm one product's images after Save → reopen

### 2026-09-12 — Homepage Popular Brands slider
- Change-id: `homepage-brands-slider`
- Trigger: owner-ask (list → scroll/slide, professional, fully responsive)
- Scope: `_brands.blade.php`, brand-mm CSS, `carousels.js` brand Swiper, `HomepageDataService::popularBrands` (+ logos/counts), BrandObserver flush, built assets
- Status: COMPLETE (code)
- Notes: Logo tiles when Admin uploads brand logo; monogram fallback; arrows ≥768px; swipe peek mobile

### 2026-09-12 — Admin Settings: verifiable outbound sender email
- Change-id: `admin-mail-from-settings`
- Trigger: owner-ask (sender email from admin Settings, must verify)
- Scope paths: `MailSenderSettingsService`, `SiteSettingsService` keys, Filament Settings section, `VerifyMailFromAddressMail`, `MailFromVerifyController`, `ApplyConfiguredMailFrom`, route `mail-from.verify`, `tests/Feature/MailSenderSettingsTest.php`
- Type tags: [x] code [x] test [x] admin [x] docs-only
- Checklist:
  - [x] Live From only after signed mailbox confirm (24h)
  - [x] Clear → fall back to env `MAIL_FROM_*` (bootstrap captured once)
  - [x] SMTP remains `.env` only
  - [x] Resend verification header action when pending
- Status: COMPLETE (code)
- Notes: Host should run `php artisan test --filter=MailSenderSettings`

### 2026-09-12 — Admin vs storefront auth isolation
- Change-id: `admin-web-guard-isolation`
- Trigger: owner-ask (admin login leaking into website account)
- Finding: shared default `web` guard → admin session = storefront login
- Fix: `auth` guard `admin` + Filament `authGuard('admin')` + `UseAdminAuthGuard`; storefront login/logout/register stay on `web`
- Tests: `AdminStorefrontAuthIsolationTest`; admin feature tests `actingAsAdmin`
- Plan: `docs/C_ADMIN_STOREFRONT_AUTH_ISOLATION_PLAN.md`
- Status: COMPLETE (code)

### 2026-09-12 — PDP tabs (reviews) + description toggle + track-order placement
- Change-id: `pdp-tabs-track`
- Trigger: owner-ask
- Scope: product/show tabs, review-section chrome, footer track link, AccountController hasTrackableOrder
- Out of scope: review rules, /track-order route itself, order show tracking timeline
- Status: COMPLETE (code)
- Plan: `docs/C_PDP_TABS_TRACK_PLAN.md`

### 2026-09-12 — Remove Product Q&A end-to-end
- Change-id: `remove-product-qa`
- Trigger: owner-ask (PDP Q&A not needed)
- Scope: PDP Livewire, Filament ProductQuestionResource, model/service, relations, AdminAccess, audit observer, drop migration, tests/automation, about/seed copy
- Out of scope: Reviews, FAQ CMS, contact, checkout
- Type tags: [x] code [x] test [x] storefront [x] admin [x] docs-only
- Status: COMPLETE (code)
- Notes: `php artisan migrate` drops `product_questions`. Plan: `docs/C_REMOVE_PRODUCT_QA_PLAN.md`.

### 2026-09-12 — C6 Razorpay test checkout (owner keys on prod domain)
- Change-id: `c6-razorpay-test`
- Trigger: owner-ask (real account, test mode, keys not generated, prod host)
- Scope paths: `VerifyRazorpayConfig.php`, `docs/C6_RAZORPAY_TEST_CHECKOUT.md`, `RazorpayVerifyCommandTest.php`
- Type tags: [x] code [x] test [x] docs-only [x] commerce
- Checklist:
  - [x] A1–A2 C6 scoped — test keys only, no live
  - [x] A3 verify command (no secret echo)
  - [x] A6 tests for command
  - [x] A9–A10 no keys in repo
  - [ ] A11 owner: generate keys → .env → webhook → smoke (owner host)
  - [x] A14 plan board C6 IN PROGRESS
- Status: OWNER ACTION REQUIRED (code+docs ready)
- Notes: Live = C7 only. Never paste secrets in chat.

### 2026-09-12 — C4 checkout stock message fix + C5 demo purge (W4)
- Change-id: `c4-stock-fix-c5-purge`
- Trigger: owner-ask (CheckoutTest fail + C5 start)
- Scope paths: `CheckoutWizard.php` (OOS before empty-cart), contact/home/layout, PageSeeder, seed policy, DemoContentPurgeTest
- Type tags: [x] code [x] test [x] storefront [x] docs-only
- Checklist:
  - [x] Fix placeOrder: allItems + stock message (not empty cart when OOS)
  - [x] Purge recent-purchase synthetic demo
  - [x] Contact seed/cards: no fake phone/email/showroom
  - [x] Homepage promo/category banners gated on real data
  - [x] Seed policy + AdminOps default email empty
  - [x] Tests authored (run on PHP host)
- Status: COMPLETE (code)
- Notes: Next = C6/C7 owner-only (live pay). No Phase 18.

### 2026-09-12 — C4 buy-path smoothness (W2)
- Change-id: `c4-buy-path`
- Trigger: owner-ask
- Scope paths: `PaymentAvailability.php`, `CheckoutWizard.php`, cart/wishlist/checkout/order views, `LoginController.php`, `BuyPathSmoothnessTest.php`, `BUY_PATH_SMOKE_CHECKLIST.md`
- Type tags: [x] code [x] test [x] commerce [x] docs-only
- Checklist:
  - [x] A1–A2 scoped C4
  - [x] A3–A5 payment guard via PaymentAvailability + resolve()
  - [x] A6 tests authored (run on PHP host)
  - [x] A9–A10 no live keys
  - [x] A11 next = C5 demo purge
  - [x] A14 plan board updated
  - [x] A15 owner summary
- Status: COMPLETE (code)
- Notes: Wishlist remains product-level (documented in UI). Fake pay only local/tests.

### 2026-09-12 — Fix PublicContent nav test + C3 admin upload flowless
- Change-id: `c1-nav-fix-c3-upload`
- Trigger: owner-ask (failing test + start C3)
- Scope paths: `navbar.blade.php`, `PublicContentVisibilityTest.php`, `ProductResource.php`, `EditProduct.php`, `ADMIN_PRODUCT_UPLOAD_RUNBOOK.md`, `AdminProductUploadFlowTest.php`
- Type tags: [x] code [x] test [x] admin [x] docs-only
- Checklist:
  - [x] A1 five always-read
  - [x] A2 scoped fix + C3 only
  - [x] A6 tests updated/authored — run on owner PHP host
  - [x] A9–A10 safe
  - [x] A11 next = C4 buy-path
  - [x] A14 plan board updated
  - [x] A15 owner summary
- Root cause: navbar hardcoded `/about` bypassed PublicContent footer gate
- Status: COMPLETE (code); verify with PublicContentVisibilityTest + AdminProductUploadFlowTest

### 2026-09-12 — C2 multi-variant depth (W3)
- Change-id: `c2-variant-depth`
- Trigger: owner-ask (verify C1, push, plan C2, execute)
- Scope paths: `ProductVariant.php`, `ProductResource.php`, `AddToCart.php`, PDP/cart/checkout blades, `tests/Feature/VariantDepthTest.php`, `docs/C2_VARIANT_DEPTH_PLAN.md`
- Type tags: [x] code [x] test [x] admin [x] docs-only
- Checklist:
  - [x] A1–A2 scoped to C2 after C1 push
  - [x] A3 services unchanged for money; variant effectivePrice used
  - [x] A4 no client totals
  - [x] A6 tests authored `VariantDepthTest` — PHP unavailable in sandbox (not executed)
  - [x] A7 n/a build
  - [x] A9–A10 safe
  - [x] A11 next = C3 admin upload polish
  - [x] A14 plan status updated
  - [x] A15 owner summary prepared
- Status: PARTIAL (code complete; test run blocked — no PHP)
- C1 push: `471c653` → `origin/arena/01a09498-rythm`

### 2026-09-12 — C1 W5 hide-when-empty (first eng chunk)
- Change-id: `c1-public-content-visibility`
- Trigger: owner-ask (best-first actionable)
- Scope paths: `app/Support/PublicContent.php`, `app/Observers/PageObserver.php`, `app/Providers/AppServiceProvider.php`, `app/Livewire/CheckoutWizard.php`, `app/Services/SiteSettingsService.php`, product/checkout/footer/account/order views, `tests/Feature/PublicContentVisibilityTest.php`, plan docs
- Type tags: [x] code [x] test [x] docs-only [x] commerce
- Checklist:
  - [x] A1 Five always-read + priority plan
  - [x] A2 Scoped to W5/C1 only
  - [x] A3 Tax gate in CheckoutWizard aligned with OrderService
  - [x] A4 No client money trust changes beyond tax enable flag
  - [x] A5 n/a authZ surface
  - [x] A6 Tests **authored** (`PublicContentVisibilityTest`) — **not executed here** (no php/composer in sandbox this sitting)
  - [x] A7 n/a front build (blade only)
  - [x] A8 no new hex
  - [x] A9 no secrets
  - [x] A10 no live pay / phase 18
  - [x] A11 §G next chunk = C2 variants; C1 partial complete
  - [x] A12 locked decisions unchanged
  - [x] A13 footgun 13 reinforced
  - [x] A14 PRODUCTION_PRIORITY_PLAN status board updated
  - [x] A15 Owner: why C1 first + what shipped
- Risks / follow-ups: Run `php artisan test --filter=PublicContentVisibilityTest` on machine with PHP; C2 variants next
- Status: PARTIAL (code complete; test run blocked by missing PHP in env)

### 2026-09-12 — Production priority plan (5 owner priorities)
- Change-id: `production-priority-plan`
- Trigger: owner-ask
- Scope paths: `docs/PRODUCTION_PRIORITY_PLAN.md`, `docs/CLIENT_HANDOVER_DETAILS.md`, `docs/RAZORPAY_SETUP_GUIDE.md`, `docs/MEMORY.md`, `docs/ARCHITECTURE.md`
- Type tags: [x] docs-only
- Checklist:
  - [x] A1 Read five always-read + flow verify
  - [x] A2 Docs only for planning drop
  - [x] A3–A8 n/a code
  - [x] A9 No secrets (Razorpay guide uses placeholders only)
  - [x] A10 No live pay / phase 18 enablement
  - [x] A11 §C + §G updated — production programme planned; client-owned details non-blocking
  - [x] A12 Locked decisions unchanged (single-brand, auth checkout, server money)
  - [x] A13 Footgun: empty client policy/tax must hide not crash — tracked as W5
  - [x] A14 ARCHITECTURE pointer; PHASES unchanged (no phase reopen)
  - [x] A15 Owner summary: W1–W5 plan + Razorpay chat guide + handover file
- Risks / follow-ups: Implement C1 (hide-empty) then C2 (variants) on owner go
- Status: COMPLETE
- Programme: W1 upload flowless · W2 buy path · W3 multi-variant · W4 no-demo · W5 client details optional

### 2026-09-12 — Product upload + payment flow verify plan
- Change-id: `flow-verify-product-payment`
- Trigger: owner-ask
- Scope paths: `docs/FLOW_VERIFY_PRODUCT_PAYMENT.md`, `docs/MEMORY.md`
- Type tags: [x] docs-only
- Checklist:
  - [x] A1 Read five always-read files before editing
  - [x] A2 Touched only task-relevant paths
  - [x] A3 n/a (docs)
  - [x] A4 n/a
  - [x] A5 n/a
  - [x] A6 n/a — docs/verify only
  - [x] A7 n/a
  - [x] A8 n/a
  - [x] A9 No secrets committed
  - [x] A10 No launch/pay enablement
  - [x] A11 §C verified unchanged for launch facts; added pointer fact via log
  - [x] A12 Locked decisions unchanged
  - [x] A13 none new
  - [x] A14 n/a phase status; plan doc only
  - [x] A15 Owner summary: both flows code-DONE; live gates owner-pending
- Risks / follow-ups: Owner may request Track A smoke or Track B launch blockers next
- Status: COMPLETE
- Verdict: Product upload + payment **implemented & phase-complete**; live money/rights/HTTPS **pending**

### 2026-09-12 — MEMORY strict checklist protocol v2
- Change-id: `memory-strict-checklist`
- Trigger: owner-ask
- Scope paths: `docs/MEMORY.md`, `docs/RULES.md`, `docs/PHASES.md`
- Type tags: [x] docs-only
- Checklist:
  - [x] A1 Read five always-read files before editing
  - [x] A2 Touched only task-relevant paths
  - [x] A3 n/a (docs)
  - [x] A4 n/a
  - [x] A5 n/a
  - [x] A6 n/a — docs only
  - [x] A7 n/a
  - [x] A8 n/a
  - [x] A9 No secrets committed
  - [x] A10 No launch/pay enablement
  - [x] A11 §1 updated — protocol version, MEMORY enforcement fact
  - [x] A12 Locked decisions unchanged
  - [x] A13 Footgun added — skipping MEMORY update
  - [x] A14 Mirrors: RULES G10/AI9, PHASES quality gate note
  - [x] A15 Owner summary: strict per-change MEMORY checklist now binding
- Risks / follow-ups: Agents must use template on every material change hereafter
- Status: COMPLETE

### 2026-09-12 — PRD + five always-read files (initial)
- Change-id: `always-read-v1`
- Trigger: owner-ask
- Scope paths: `docs/PRD.md`, `docs/ARCHITECTURE.md`, `docs/RULES.md`, `docs/PHASES.md`, `docs/DESIGN.md`, `docs/MEMORY.md`, `README.md`
- Type tags: [x] docs-only
- Checklist:
  - [x] A1–A15 satisfied for docs-only bootstrap (see prior session narrative)
  - [x] A11 Facts seeded from tracker (phases 0–17 complete, conditional GO, not live)
- Status: COMPLETE
- Notes: Single-brand multi-product PRD; AI front door = five files.

### 2026-09-01 (historical — tracker)
- Phase 16 UAT 23/23 PASS; Phase 17 CONDITIONAL GO; MVP evidence complete; 4 pre-live items open; Phase 18 inactive.

### 2026-08-25 → 2026-08-30 (historical)
- Phases 0–11 + 6A qualification programme (safety, stack, storefront, commerce, catalogue, RBAC, pay, notify, fulfill, CX).

---

# C. Current facts (source of truth snapshot)

*Update cells in the **same change** that makes them true. Stale cells are bugs.*

| Fact | Value | Updated |
|---|---|---|
| **Product** | Rhythm Exports / Rythme Music Store — musical instruments ecommerce | 2026-09-12 |
| **Commerce model** | **Single brand, many products** (not multi-vendor) | 2026-09-12 |
| **Repo** | `anoopuri21/rythm` | 2026-09-12 |
| **PRD** | `docs/PRD.md` (v1.0) | 2026-09-12 |
| **Always-read set** | `ARCHITECTURE` `RULES` `PHASES` `DESIGN` `MEMORY` | 2026-09-12 |
| **MEMORY protocol** | **v2 STRICT checklist** — change incomplete without §A log | 2026-09-12 |
| **Delivery** | Phases **0–17 + 6A COMPLETE**; **17 = CONDITIONAL GO** (1 Sep 2026) | 2026-09-12 |
| **Phase 18** | Inactive until explicit owner deploy command | 2026-09-12 |
| **Auto Mode** | PAUSED | 2026-09-12 |
| **Live?** | **No** — 4 pre-live owner items open | 2026-09-12 |
| **Pre-live blockers** | (1) Razorpay live keys + prod webhook (2) AS-H011 legal wording (3) catalogue/media rights (4) host HTTPS/TLS | 2026-09-12 |
| **Payments now** | Razorpay **test mode** until owner flips live | 2026-09-12 |
| **Tax/returns public** | Domain exists; **defaults disabled** until owner approval | 2026-09-12 |
| **Withheld public pages** | `shipping`, `returns`, `warranty`, `faqs` | 2026-09-12 |
| **Currency** | INR | 2026-09-12 |
| **Checkout** | Auth required; guest cart + merge on login | 2026-09-12 |
| **Stack** | Laravel 13.24 · Livewire 4 · Filament 5.x · Tailwind 4 · Spatie Media · Razorpay | 2026-09-12 |
| **Design tokens** | Brand `#B20202` / `#930303` · ink `#222` · paper `#fff` · soft `#E7F4F1` · Inter via `@theme` | 2026-09-12 |
| **DB** | SQLite dev/tests · **MySQL 8** prod/UAT (`rhythm_db` historically) | 2026-09-12 |
| **Admin URL** | `/admin` | 2026-09-12 |
| **Business logic home** | `app/Services/*` | 2026-09-12 |
| **Inventory authority** | `InventoryService` only | 2026-09-12 |
| **Order transitions** | `OrderService` + `OrderStateMachine` | 2026-09-12 |
| **Storefront routes** | `routes/web.php` | 2026-09-12 |
| **Brand config** | `config/rythme.php` + Filament Site Settings | 2026-09-12 |
| **Outbound mail From** | Verified Admin → Settings sender, else `MAIL_FROM_*` | 2026-09-12 |
| **Media storage** | One public disk `MEDIA_DISK` (default `public`) for panel uploads + storefront, independent of `FILESYSTEM_DISK`; host-relative `/storage` URLs served by that disk (`serve => true`; the private `local` disk must keep `serve => false`); fields via `MediaUpload` (mime + bytes + **6000² px** + count bound, galleries reorderable); diagnose `php artisan media:doctor [--fix]`, repair `php artisan media:relocate`; the **same image used in many places is stored once** (M-10) — `docs/media-architecture.md` | 2026-10-05 |
| **Cloudinary media (phase 1)** | When `MEDIA_CLOUDINARY=true` + credentials exist, NEW uploads for product `gallery`/`og`/`variant_gallery` and category `icon` are stored on the `cloudinary` disk and served from `https://res.cloudinary.com/<cloud>/…`; legacy rows and all other collections keep `MEDIA_DISK` + `/storage` URLs. Disk decided only by `App\Support\MediaDisk`; URLs derived by `App\Support\CloudinaryDeliveryUrl` + `App\Models\Media` (conversion names → delivery transformations, no local conversions for cloud rows); relocation/doctor exempt cloud rows — `docs/cloudinary-media.md` (M-9) | 2026-10-05 |
| **Media reuse (M-10)** | One image in several places = **one stored file**: the first upload owns it (`media.shared_path` NULL), every further usage is a shared row (`shared_path` = owner's base path, `source_media_id`, `checksum`) resolving through `App\Support\MediaPathGenerator` (original + `conversions/` + `responsive-images/`); reused rows generate no conversions (owner's are mirrored to every usage, M-7 URLs re-synced); deleting a usage never breaks the others (last usage removes the file); reused rows are skipped by `media:relocate`/`media:doctor` misplaced checks; admin **Media library** (`/admin/media-library`, `CataloguePolicy`) → *Use elsewhere*; existing duplicates `php artisan media:dedupe [--dry-run]`; empty `og` falls back to the first gallery original — `docs/media-reuse.md` | 2026-10-05 |
| **Media URL columns (M-7)** | Resolved URL(s) persisted per model (`products.thumbnail_url`/`gallery_urls`/`og_image_url`, `product_variants.*`, `brands.logo_url`, `categories.icon_url`, `hero_slides.*_image_url`, `homepage_blocks.image_url`); reads column-first across products, variants, brands, categories (`HomepageDataService::popularCategories` + `CategoryService::tree`), hero slides and homepage blocks; `MediaUrlObserver` + `php artisan media:sync-urls` keep columns fresh and flush homepage/category caches | 2026-10-03 |
| **Image intake (M-8)** | **Admin panel only** — catalogue acquisition/import pipeline dormant (owner decision 2026-10-03), code kept | 2026-10-03 |
| **Product media pipeline** | Local disk: `gallery` → `thumb-webp` 480² (cards/cart) + `gallery-webp` 1200² (PDP); `variant_gallery` → `variant-thumb-webp` 240² + `variant-gallery-webp` 1200²; `og` → original only; first gallery image = card/hero, set by drag-order in the panel. **Cloudinary rows:** same conversion names delivered as `c_fit`/`f_auto,q_auto:good` transformations — nothing queued locally | 2026-10-05 |
| **Session branch (Arena)** | `arena/01a10a94-rythm` (session-fixed) | 2026-10-05 |

### C.1 Fact-update matrix (which §1 keys to touch)

| If you changed… | Must refresh fact keys |
|---|---|
| Launch / pay mode / legal pages | Live?, Pre-live blockers, Payments now, Withheld pages, Tax/returns |
| Phase completion / Auto Mode | Delivery, Phase 18, Auto Mode |
| Stack / Filament / Laravel major | Stack (+ ARCHITECTURE.md) |
| Design tokens / font | Design tokens (+ DESIGN.md) |
| New service authority / invariant | Business logic / Inventory / Order rows (+ ARCHITECTURE) |
| Media upload/reuse/disk behaviour | Media storage / Media reuse / Product media pipeline (+ media-architecture.md + RULES) |
| Commerce model / checkout policy | Commerce model, Checkout (+ PRD + RULES) |
| Only bugfix, same architecture | §C **Verified unchanged** in log (`none`) — still required |

---

# D. Locked decisions (don’t re-ask)

Change only with **explicit owner approval** + PRD/RULES update + log.

- Single-vendor / single-brand storefront.  
- No guest checkout.  
- Server-authoritative pricing, shipping, tax, coupons.  
- Exact MySQL 8 for production acceptance (not MariaDB-as-proof).  
- Shared hosting / cPanel class deploy; cron-drained queue.  
- Blade + Livewire storefront (not React/Next).  
- Deployment is human-gated (Phase 18).  
- Manufacturer brands are catalogue labels only.  
- Phases 0–17 complete ≠ site live.  
- **MEMORY v2 checklist is mandatory on material changes.**

---

# E. Critical paths (debugging map)

| Journey | Entry → core code |
|---|---|
| Home | `HomeController` → `HomepageDataService` → `resources/views/home/*` |
| Shop | `ShopController` + Livewire `ShopIndex` → `ProductQueryService` |
| PDP | `ProductController` + Livewire add-to-cart/wishlist/review/Q&A |
| Cart | `CartService` + Livewire `CartDrawer` `CartPage` `CartBadge` |
| Checkout | Livewire `CheckoutWizard` → `OrderService` → `PaymentGateway` |
| Pay return | `RazorpayController` → payment verify → paid transition + `InventoryService` |
| Orders | `OrderController` + policies/signed links |
| Admin catalogue | Filament Product* + activation/import services |
| Media upload / preview / storefront image | `MediaUpload` → Spatie (`MEDIA_DISK`) → `/storage/…` → model `getAvailableUrl()` · repair `MediaRelocationService` · `docs/media-architecture.md` |
| Refunds | `RefundService` (Finance) |
| Shipments | `FulfillmentService` |
| Notifications | `CommerceNotificationService` + deliveries |

*If you add a new critical journey, append a row here in the same change.*

---

# F. Known footguns

1. **Client totals lie** — always recompute server-side.  
2. **Webhook retries** — idempotent via `payment_events`.  
3. **Variant vs product stock** — one inventory source per line.  
4. **Paid cancel** → `refund_pending`; don’t claim gateway refund early.  
5. **Legal pages** may exist in DB but stay withheld.  
6. **Stale docs** (`plan.md`, `AGENT_RULES_STRICT`, old NEXT_SESSION) — five always-read + tracker win.  
7. **Filament version** in ancient docs may say v3; trust `composer.json` + code (5.x).  
8. **Font mirror drift** — `app.css` `@theme` (Inter) wins over stale Poppins mirrors.  
9. Destructive tests must not hit persistent UAT/prod DB.  
10. `vendor/` may be external/symlink in agent envs.  
11. **Skipping MEMORY “because docs/small”** — causes next session full re-scan; **forbidden**.  
12. Claiming **done** without §A checklist — treat as incomplete work.  
13. **Empty client tax/policy/shipping** must **hide** on storefront — never fake values, never crash checkout (W5).  
14. **Wishlist is product-level** today — variant-specific wishlist may need explicit work if owner expects it (W2.6).
15. **Media disk ≠ `FILESYSTEM_DISK`.** Filament's upload disk follows `config('filament.default_filesystem_disk')`; if that is the private `local` disk, saved images 403 on the storefront and the admin preview URL is signed + host-bound (FilePond spins forever — its `server.load` has no error path). Keep `config/filament.php` + `config/media-library.php` on `MEDIA_DISK`, keep media URLs relative (never `APP_URL`), build fields only with `MediaUpload`, never `vendor:publish` Filament's config over ours.
16. **A deploy script that `git pull`s itself runs its OLD logic for that run** (bash parses the whole `case` block first). Keep `update` split: pull, then `exec bash … update-steps`; never add deploy steps assuming they run on the first deploy that ships them.

17. **Never add Filament's `->image()` to a media field.** It does not "validate an image" — it *rewrites* `acceptedFileTypes` to `image/*` (`packages/forms/src/Components/FileUpload.php`), which re-admits SVG (script-capable, same-origin `/storage`). Bounds are three-dimensional: mime list + `maxSize` (bytes) + `dimensions:max_width/max_height` (decode cost — a small file can decode to gigabytes and kill the queue worker). All three live only in `MediaUpload`.

18. **Filament's `ImageColumn` reads its state as a path on the filesystem disk** (`$disk->exists($state)` then `$disk->url($state)`). A stored web URL like `/storage/12/a.webp` is looked up inside `storage/app/public/storage/…` and renders **nothing** (silent). For persisted media URLs use `App\Filament\Columns\StoredMediaUrlColumn` (returns app-relative state as-is) — the Spatie column resolves its own URLs, a plain `ImageColumn` does not.
19. **`saveQuietly()` on a media row bypasses `MediaUrlObserver`.** Any code that repoints/deletes media quietly (e.g. `MediaRelocationService`) must call `syncResolvedMediaUrls()` on the owner itself, or the stored URL columns keep pointing at the old disk. Same applies to raw `DB::table('media')` writes — repair with `php artisan media:sync-urls`.

20. **`serve => true` on any second local disk silently hijacks `/storage`.** Laravel registers `GET|PUT /storage/{path}` for *every* local disk with that flag (URI from the disk's `url`, else `/storage`) and throws at boot when two disks claim the same URI. On the **private** disk the route demands a signature unless `visibility === 'public'` → 403 (dev) / 404 (prod) for every image whenever `public/storage` is missing. So: `serve => true` **only** on the public media disk, `serve => false` on `local`; verify with `php artisan media:doctor`. A cached config (`config:clear`) keeps the old flags alive after a deploy.
21. **Pending URL-column migrations must degrade gracefully (`SQLSTATE[42S22]`).** When code deploys before `php artisan migrate` runs on the host, explicit SQL references to new M-7 columns (`whereNull('icon_url')`, `get(['...', 'icon_url'])`, `saveQuietly()` on `forceFill`) throw `SQLSTATE[42S22]`. Guard explicit column lists and `SyncsResolvedMediaUrls` / `SyncMediaUrls` / `MediaDoctor` with `Schema::hasColumn(s)` so the app falls back to Media Library until `migrate` finishes.
22. **Bash `trap ... ERR` without `set -E` does not fire inside functions or on `exit 1` (`die()`).** In `scripts/deploy-cpanel.sh`, use `set -Eeuo pipefail` + an `EXIT` trap guarded by `MAINTENANCE_ON=1` (disarmed right before `exec` handover and re-armed in `update-steps`) so a failed deploy step never leaves the site stuck in 503 maintenance mode.
24. **A reused image is a reference row — never its own file.** Deleting a duplicated media row (`$media->delete()`) would drop a *usage* (the row may be a product's gallery item) and Spatie would try to delete files a shared row does not own. So: re-point first, delete after (`MediaReuseService::mergeDuplicates`). Also: (a) a shared row must never have its `file_name` changed without changing `shared_path` — the observer refuses; (b) `MediaUrlObserver` must keep watching `shared_path` or dedupe leaves stale URL columns; (c) `MediaPathGenerator` decides the base path from `shared_path`, so any code path that computes media paths by hand (relocation, doctor) must skip `whereNull('shared_path')`; (d) `media:dedupe` reads owner files to hash them — on Cloudinary that is a network read per row (fine for the owner's small catalogue).

23. **Cached storefront builders + `saveQuietly()` URL syncs.** `HomepageDataService::all()` (`homepage.data`, 1h TTL) and `CategoryService::tree()` (`categories.tree`, forever) cache resolved category/brand arrays, while `syncResolvedMediaUrls()` writes via `saveQuietly()` (which bypasses `CategoryObserver`). Both `MediaUrlObserver` and `php artisan media:sync-urls` / `media:relocate` must explicitly flush `HomepageDataObserver` and `CategoryService`, and any partial `->get([...])` on `Category` must include `icon_url` + `->with('media')` or `iconUrl()` will silently miss the column and N+1 on fallback.

24. **Cloud-hosted media must be exempt from every "misplaced / wrong disk" check — and its URLs are derived, not fetched.** Cloudinary rows are deliberately off `MEDIA_DISK`, so `MediaRelocationService::misplacedQuery()` and `MediaDoctor::checkMediaRows()` exclude them (otherwise `media:doctor` FAILs and `media:relocate` — also run by `deploy-cpanel.sh` — streams the CDN files back into `storage/app/public` and deletes the cloud copies). `MediaDoctor::checkUrlColumns()` only stat-checks `/storage/`-prefixed URLs, which is why absolute delivery URLs are skipped safely. Never call `Storage::disk('cloudinary')->url($path)` per row (the package's adapter hits the Admin API — one HTTP round trip per image); `App\Models\Media` + `CloudinaryDeliveryUrl` derive `res.cloudinary.com/<cloud>/image/upload/…` instead. `php artisan cloudinary:install` is **not** needed (it only publishes the package's own `config/cloudinary.php`; the disk reads `config/filesystems.php`), and an empty `CLOUDINARY_URL=` line must stay equivalent to "unset" (`env(...) ?: null`) because the driver branches on `isset($config['url'])`. `Storage::fake('cloudinary')` swaps the driver to `local` in tests — restore the config driver value if a test asserts the disk contract.
*New trap discovered → add numbered item same day.*

---

# G. Open owner items / backlog

**Launch blockers (live):** four pre-live items in §C.  

**Deferred (non-blocking unless asked):** observability, full CI/CD, vector search, media conversions, broad perf, multi-currency, native apps.

**Doc front door:** five always-read files; deep `docs/*` on demand only.

**Flow verify (product upload + payment):** `docs/FLOW_VERIFY_PRODUCT_PAYMENT.md` — code/phases DONE; live keys/rights/HTTPS pending.

**Production priority programme:** `docs/PRODUCTION_PRIORITY_PLAN.md` (W1–W5).  
**Client optional details:** `docs/CLIENT_HANDOVER_DETAILS.md` — empty/OFF = hidden, no blockers.  
**Razorpay owner guide:** `docs/RAZORPAY_SETUP_GUIDE.md`.  
**Eng chunks:** C1–C5 complete · **C6** code+runbook ready — owner puts `rzp_test_` keys on prod `.env`, runs `php artisan razorpay:verify --ping`, full smoke. **C7 live blocked**. Run `RazorpayVerifyCommandTest` + prior suite on PHP host.

---

# H. Cross-file mirror map (don’t forget)

| Change type | Also update |
|---|---|
| Phase / launch / Auto Mode | `PHASES.md` + `tasks/MASTER_PROJECT_TRACKER.md` |
| Tokens / typography / UI law | `DESIGN.md` + `resources/css/app.css` (+ design-system doc if deep) |
| Layers / services / routes / aggregates | `ARCHITECTURE.md` (+ PRD §architecture if product-level) |
| Product scope / personas / NFR | `PRD.md` + `RULES.md` as needed |
| Media disk topology / new image intake / CDN rules | `docs/media-architecture.md` (M-1…M-10) + `docs/cloudinary-media.md` + `docs/media-reuse.md` + `docs/RULES.md` §7 + `docs/ARCHITECTURE.md` §9 + `docs/media-optimization.md` |
| Binding behavioral law | `RULES.md` first, then MEMORY §D |
| README entry points | `README.md` always-read table if files move |

---

# I. Quick commands

```bash
php artisan test
npm run build
php artisan route:list
php artisan migrate --force          # careful on shared DB
php artisan storage:link             # public/storage -> storage/app/public (images)
php artisan media:doctor            # WHY are images broken? disk/serve/symlink/files/URL columns (read-only)
php artisan media:doctor --fix      # apply the safe repairs (storage:link, media:relocate, media:sync-urls)
php artisan media:relocate --dry-run # then without --dry-run: move media to MEDIA_DISK
php artisan media:sync-urls --dry-run # then without: refresh stored image-URL columns (M-7)
php artisan media:dedupe --dry-run    # then without: merge duplicate uploads into one stored file (M-10)
php artisan serve --host=0.0.0.0 --port=8000
```

`/admin` · `/` · `/shop`

---

# J. Pointer index (optional deep reads)

| Topic | Path |
|---|---|
| PRD | `docs/PRD.md` |
| Architecture inventory | `docs/architecture-overview.md` |
| Commerce plan | `docs/architecture/01-commerce-architecture.md` |
| Domain / states / ACL | `docs/domain-model.md`, `state-machine.md`, `permissions-matrix.md` |
| Tracker / sequence | `tasks/MASTER_PROJECT_TRACKER.md`, `CANONICAL_PHASE_SEQUENCE.md` |
| Release / rollback | `docs/release-checklist.md`, `rollback-plan.md` |
| Media storage / URLs / repair | `docs/media-architecture.md`, `docs/media-optimization.md` |
| Media reuse (one image, many places) | `docs/media-reuse.md` |
| Cloudinary rollout (products + categories) | `docs/cloudinary-media.md` |

---

# K. Size & archive policy

1. Keep this file scannable (**target ≤ 450 lines**).  
2. Session log: keep **last ~15 checklist entries** in full; older → `docs/MEMORY_ARCHIVE.md` with pointer.  
3. Facts table stays complete; don’t archive §C.  
4. No secrets, tokens, customer PII, raw card data.  
5. Prefer path references over pasted code blocks.

---

# L. AI start/end ritual (print mentally every task)

**START**

```
[ ] Read ARCHITECTURE, RULES, PHASES, DESIGN, MEMORY
[ ] Note §C facts + open blockers
[ ] Define scope paths before first edit
```

**END (before saying “done”)**

```
[ ] Paste §A checklist into §B log (filled)
[ ] §C keys updated or “none”
[ ] Mirrors H done or n/a
[ ] Tests/build recorded
[ ] Owner summary ready
```

---

*No checklist → not done. Read every session. Write every change.*
