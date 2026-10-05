# Media reuse — ek image, kai jagah (plan)

**Status:** APPROVED (owner, 2026-10-05) → **IMPLEMENTED** (phases 1–5 code + docs + tests in tree; host verification pending — see §5).
**Date:** 2026-10-05 · **Branch:** `arena/01a10a94-rythm`
**Owner ask (verbatim):** "admin panel me media upload karne me same image ko multiple jageh use karne ke liye multiple time image upload karna padta hai jiski wajeh se ek hi image server pe bhi multiple time save ho jati hai jiski need nahi hoti, kyuki ek hi image url path se multiple jageh pe image use kiya jaa sakta hai."

---

## 1. Problem (aaj kya hota hai)

Har upload ek **nayi media row = naya file** banata hai:

| Case | Aaj | Result |
|---|---|---|
| Same photo product A + product B ki gallery me | 2 baar upload | disk/CDN par 2 copies |
| Product gallery image ko hi `og` (social) image banana | dobara upload | 1 extra copy |
| Product image ko category icon / homepage block me use karna | dobara upload | 1 extra copy (jahan collection support kare) |
| Ek hi image ka 5 products me use | 5 uploads | 5 copies |

Spatie Media Library me per-row path `{media-id}/{file_name}` hota hai
(`DefaultPathGenerator::getBasePath()` = `{prefix}/{key}`), isliye **do rows kabhi
ek file share nahi karti** — aur `Media::copy()` / `move()` sach me bytes copy
karte hain (temp file → naya upload). Isi wajah se duplicate storage banta hai.

Cloudinary rollout ke baad bhi yeh sach hai: har row ka `public_id`
`{media-id}/{file_name}` hota hai → Cloudinary par bhi duplicate asset + duplicate
storage/bandwidth usage.

## 2. Research — options jo soche gaye

| # | Option | Kya hota | Verdict |
|---|---|---|---|
| A | **Shared reference rows** — ek file disk/CDN par, doosri jagah "reference" row (koi byte copy nahi) | Path `{source-id}/file` hi rehta hai; reference rows usi path par resolve karte hain | ✅ **Chuna** — problem ko asli root par solve karta hai (local + Cloudinary dono) |
| B | Picker + `Media::copy()` (one-click duplicate) | Manual upload bachta hai, **storage duplicate rehta hai** | ❌ Owner ki asli shikayat (duplicate storage) rehti hai; sirf A ke saath "entry point" ke roop me useful |
| C | Sirf auto-dedupe (hash) — same bytes dobara upload ho to link | Upload ke waqt lazmi duplicate pakde, par "doosri jagah use karna" ka koi UX nahi | 🔸 A ke upar Phase 3 me value add karta hai |
| D | Purane models ke URL column me doosri jagah ki URL daal dena | Sirf un models me chalta jahan plain URL column ho (product gallery/reorder/variants me nahi); media table se sync toot jaata | ❌ Adhura aur M-7 contract ke against |

**Research facts (verified in package source):**
- `media-library.path_generator` ek documented extension point hai; `DefaultPathGenerator` ki `getBasePath()` hi originals, `conversions/` aur `responsive-images/` teeno ka base deta hai → ek chhoti custom subclass se **poori row ka base path** badla ja sakta hai (originals + conversions dono).
- `media-library.media_observer` bhi configurable hai; Spatie ka default `MediaObserver::deleted()` files hata deta hai — reference rows ke liye ise rökna zaroori hai.
- Conversion file name configured `file_namer` (`conversionFileName()`) se aata hai → base path share karne se reference row ke conversions **owner ki hi files** par resolve hote hain (koi nayi conversion file nahi).
- Conversion registration sirf `Product`, `ProductVariant`, `HeroSlide` me hai → guard lagane ki jagah sirf 3.
- `HasMedia` deletion paths (`deleteAllMedia()`, `clearMediaCollection()`, `singleFile` replace) sab `Media::delete()` → observer par jaate hain → ek hi guard jagah kaafi hai.

## 3. Chosen design

### 3.1 Mechanism (core)
1. **Migration** (`media` table, additive + nullable):
   - `shared_path` (string, nullable, index) — reference row ka base path, e.g. `12` (ya `{prefix}/12`). Row isi base par resolve karti hai (originals + conversions + responsive).
   - `source_media_id` (unsigned bigint, nullable, index) — kis owner row se aayi hai (UI/grouping/mirroring ke liye).
2. **`App\Support\MediaPathGenerator extends DefaultPathGenerator`**
   `getBasePath($media)` → `$media->shared_path ?: parent::getBasePath($media)`.
   `config/media-library.php` me `path_generator` set. Isse URL, conversions, responsive, `getPathRelativeToRoot()`, doctor aur relocation — sab automatically sahi path par chalte hain (koi formula replicate nahi).
3. **`App\Models\Media`**
   - `isShared()`, `sharedOwner()` (memoized), `effectiveBasePath()`.
   - `markAsConversionGenerated()` override: parent ke baad, same `shared_path` wali sibling rows ka `generated_conversions` mirror karega (owner ki conversion poori hote hi saari usages WebP URL par upgrade + `MediaUrlObserver` ke through owner models ke URL columns sync).
   - Cloudinary URL derivation (already present) shared rows par automatically wahi CDN URL detا hai.
4. **`App\Observers\MediaFileObserver extends Spatie MediaObserver`** (`media_observer` config):
   - `deleted()`: reference row → **files nahi** hataye; owner row jiske path par aur rows hain → files rakho (baaki usages chalti rahen), warna normal cleanup. Isse "reference counting" apne aap ho jaati hai — aakhri usage delete hone par file bhi jaati hai.
   - `updating()`: shared rows par `syncFileNames`/`syncMediaPath` guard.
5. **Conversion guard**: `if ($media?->isShared()) return;` `Product`, `ProductVariant`, `HeroSlide` ke `registerMediaConversions()` me (Cloudinary guard ke saath) → reference rows koi conversion file nahi banati.
6. **`App\Services\MediaReuseService`**
   - `attach(Media $owner, HasMedia $target, string $collection, ?int $order = null): Media` — reference row banata hai (DB only, **zero disk writes**), `singleFile` collections me pehle wali row replace karta hai, owner models ke URL columns observer se sync hote hain.
   - `usages(Media $media)`, `duplicates()`, `mergeDuplicates()` (Phase 3), `resolveOwnerForDelete()` (delete semantics).
7. **Tooling respect**: `MediaRelocationService` shared rows ko skip kare (yeh file own nahi karti) aur owner move hone par same `shared_path` wali rows ka `disk`/`conversions_disk` update kare; `MediaDoctor` shared rows ko "misplaced/stale" na gine aur reference health (owner missing hone par bhi URL chalta hai — `shared_path` row par store hai).

### 3.2 Admin UX (Phase 2)
- **Naya `Media Library` admin page** (`/admin/media`, table of all media):
  - columns: thumbnail, file name, size, disk, **"Used in"** (kis product/category/… me), created date;
  - search (file name / owner name), filters (collection, disk, *duplicates only*);
  - row action **"Use elsewhere"** → modal: target type → record (searchable select) → collection → **Attach** (reference; koi upload nahi) → toast + cache flush;
  - row action **Delete** → reference rules ke sath.
- Har media-bearing form field ka upload flow **waise hi** rahega; reuse ek click ka kaam hai. (Inline "choose existing" button, e.g. product form ke gallery field me, phase 2b me add ho sakta hai — Filament component ke action API par depend karta hai, isliye pehle library page deliver karenge.)

### 3.3 Duplicate cleanup (Phase 3)
- `php artisan media:dedupe [--dry-run] [--limit=…]` — checksum (sha256) se byte-identical originals dhoondta hai, sabse purani row ko owner rakhta hai, baaki ko **reference** bana deta hai, extra copies (original + `conversions/`) delete karta hai, aur URL columns re-sync karta hai. Idempotent; `--dry-run` pehle.
- `media:doctor` me "duplicate images: N (run media:dedupe)" report.
- *(Decision D3)* Upload ke waqt auto-link: same bytes dobara upload ho to write-then-link (nayi copy turant delete, row reference ban jaaye). Optional.

### 3.4 OG fallback *(Decision D4)*
`Product::ogImage()` khaali `og` collection par **pehli gallery image ka original URL** de — social image ke liye dobara upload karne ki zaroorat khatam (aur `og_image_url` column bhi wahi value dikhayega).

## 4. Phases / deliverables

| Phase | Deliverable | Files (approx) |
|---|---|---|
| **1** | Core sharing: migration, path generator, `Media` overrides, file observer, conversion guards, reuse service, relocate/doctor respect | `database/migrations/2026_10_06_00000X_add_shared_path_to_media_table.php`, `app/Support/MediaPathGenerator.php`, `app/Models/Media.php`, `app/Observers/MediaFileObserver.php`, `app/Services/MediaReuseService.php`, `app/{Models/Product,Models/ProductVariant,Models/HeroSlide}.php`, `config/media-library.php`, `app/Services/MediaRelocationService.php`, `app/Console/Commands/MediaDoctor.php` |
| **2** | Media Library page + "Use elsewhere" + delete rules | `app/Filament/Resources/MediaLibraryResource*`, `app/Filament/Resources/CategoryResource`-style page classes |
| **3** | `media:dedupe` command (+ optional upload auto-link), doctor report | `app/Console/Commands/DedupeMedia.php`, `app/Services/MediaReuseService.php`, `app/Models/Media.php` |
| **4** | OG fallback (agar approve) | `app/Models/Product.php`, `app/Services/SeoService.php` (agar zaroorat) |
| **5** | Tests + docs + protocol | `tests/Feature/MediaReuseTest.php`, `tests/automation/media-reuse.test.mjs`, `docs/media-architecture.md` (M-10), `docs/cloudinary-media.md` (reuse note), `docs/MEMORY.md` (§B, §C, §F, §H), `tasks/MEDIA_REUSE_PLAN.md` |

**Tests (Phase 5):** reference attach → same URL + **koi nayi file nahi** (local aur fake Cloudinary), delete semantics dono taraf (reference delete → file zinda; owner delete → baaki usages zinda; aakhri usage delete → file gaya), owner ki conversion complete hone par saari usages ke URL columns upgrade, `media:dedupe` dry-run/apply, relocate/doctor shared rows par safe, Media Library page actions (Filament Livewire test), static automation guards.

## 5. Acceptance criteria

1. Ek image 2+ jagah use karne par disk/CDN par **exactly ek file**.
2. Admin: ek baar upload → ji jagah chahiye wahan "Use elsewhere" se lagao (product gallery, variant, og, category icon, brand, hero, homepage block).
3. Kisi ek jagah se image hataane par baaki jagah ki image **chalti rahe** (koi 404 nahi).
4. WebP conversion complete hone par **har usage** ka stored URL upgrade ho (M-7 columns sync) — storefront par stale original URL na rahe.
5. Purani duplicates `media:dedupe --dry-run` me dikhein, apply karne par merge ho jaayein (storage kam ho).
6. `media:doctor` healthy; `media:relocate`, `media:sync-urls`, `media:dedupe` idempotent; PHPUnit suite pass (owner host par).
7. Cloudinary: shared image ka **ek hi public_id** — duplicate asset nahi.

## 6. Risks / non-goals

- **Risk:** path generator + delete semantics package internals ko touch karte hain — isliye tests (local + fake Cloudinary) aur `--dry-run` wale commands, aur koi bhi destructive step default se pehle preview.
- **Risk:** kisi row ka `file_name`/manipulations badalna shared rows par asar daalta hai → shared rows ke liye woh operations guard/block honge (shared file ko edit karne ka matlab hai: sab jagah badlega).
- **Non-goal:** "media library" me per-image alt text/tags/categories (chaho to aage add ho sakta hai); stock/licensing metadata.
- **Non-goal:** existing files ko Cloudinary par migrate karna (wo alag decision hai, `docs/cloudinary-media.md`).

## 7. Owner decisions (approval ke liye)

- **D1 — Approach:** (a) shared reference rows + Media Library page + dedupe command **(recommended)**; (b) sirf Media Library page (file copy, storage duplicate rahega); (c) sirf auto-dedupe command, koi admin page nahi.
- **D2 — Purani duplicates clean karna:** `media:dedupe` is phase me haan / baad me.
- **D3 — Upload par auto-link:** same bytes dobara upload → automatically existing file se link (haan/nahi).
- **D4 — OG fallback:** `og` khaali ho to pehli gallery image use ho (haan/nahi).

### Approval record (2026-10-05)

| Decision | Owner ka jawab |
|---|---|
| **D1 — Approach** | **shared reference rows** (option a) — ek file, baaki jagah lightweight reference rows |
| **D2 — Purani duplicates** | **haan**, is phase me `media:dedupe` se clean |
| **D3 — Upload par auto-link** | **nahi** — dobara upload karne par koi automatic link nahi |
| **D4 — OG fallback** | **haan** — `og` khaali ho to pehli gallery image (original) use ho |

Ab implementation ho chuki hai (code + docs + tests is branch me); owner host par
`php artisan migrate` → `media:dedupe --dry-run` → `media:dedupe` → `media:doctor`
→ `php artisan test` chala kar verify karega (`docs/media-reuse.md`).
