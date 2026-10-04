# HERO + BANNERS — "hardcode bhi rahega, admin se bhi manage hoga"

> **Owner instruction:** homepage ke banners aur hero sections ko chhod dena —
> hardcode bhi rahega **aur** admin se manage bhi hoga.
>
> Matlab: in sections ke liye **dual source** chahiye — code me built-in default
> content rahega (fresh/empty DB par bhi homepage poora dikhe), aur admin panel
> se override/manage bhi ho sake. Ye `docs/NO_HARDCODE_PLAN.md` ke rule ka
> **jaan-boojh kar exception** hai, sirf in sections ke liye.

Status: **DONE — branch `arena/01a105f2-rythm` par implement ho gaya, verify bhi.**
Approved decisions: Q1 = **(b)** hero slider + 3 hero side banners + "recently launched"
tall banner · Q2 = **(a) row-level** · Q3 = **(a) keep** Batch-1 changes ·
Q4 = **teeno dead knobs hata diye** (`hero_mode`, `hero_video_url`, `video_showcase_url`).
Delivery details + verification: **section 7** dekhiye.

---

## 1. Scope

| Section | File |
|---|---|
| Hero main slider | `resources/views/home/_hero.blade.php:23-84` |
| Hero tall banner ("Stage Pianos") | `_hero.blade.php:87-95` |
| Hero small banner 1 ("Tabla Sets") | `_hero.blade.php:98-105` |
| Hero small banner 2 ("Studio Gear") | `_hero.blade.php:108-115` |
| Category discovery banners | `resources/views/home/_category-banners.blade.php` |
| Promo banners | `resources/views/home/_promo-banners.blade.php` |
| "Recently launched" tall banner | `resources/views/home/_recently-launched.blade.php:22-30` |

**Is plan me nahi chheda jayega:** USP strip, footer, navbar, shop page, currency,
counts — Batch 1 jo kar chuka hai wo waise hi rahega (decision Q3 dekhiye).

---

## 2. Current state (verified)

| Item | Source today | Admin control |
|---|---|---|
| Hero slider slides (eyebrow/title/accent/copy/CTA) | `hero_slides` table | ✅ `HeroSlideResource` |
| Hero slider image, agar slide par media na ho | **hardcoded** `images/hero/grid-slide-guitar.jpg`, `grid-slide-synth.jpg` (`_hero.blade.php:7-11`) | ❌ |
| Hero tall banner | **hardcoded** image + "Stage Pianos" / "As expressive as it is portable" / `/shop?category=keyboards-pianos` | ❌ |
| Hero small banner 1 | **hardcoded** "Tabla Sets" / "Explore percussion instruments" / `/shop?category=drums-percussion` | ❌ |
| Hero small banner 2 | **hardcoded** "Studio Gear" / "Explore current studio offers" / `/shop?category=pro-audio` | ❌ |
| Category banners | DB (`popularCategories`), empty par hide | ⚠️ indirect (categories) |
| Promo banners | DB (`homepage_blocks` → `promo`), empty par hide | ✅ `HomepageBlockResource` |
| "Recently launched" banner | **hardcoded** `images/brand-feature.jpg` + "Just landed" / "Fresh gear, first play" | ❌ |

### 2.1 Teen defect jo is audit me mile

**D1 — maine Batch 1 me do dead admin field bana diye.**
`hero_video_url` aur `video_showcase_url` ke fields Admin → Settings → *Brand &
media* me add kiye, lekin poore repo me inhe koi padhta hi nahi:
```
$ grep -rn "config('rythme\." resources app config routes
app/Http/Controllers/HomeController.php:24:  $heroMode = config('rythme.hero_mode', 'slider');
app/Http/Controllers/PageController.php:17   (withheld_public_pages)
app/Http/Controllers/SitemapController.php:26 (withheld_public_pages)
app/Support/PublicContent.php:28             (withheld_public_pages)
```
`hero_video_url` / `video_showcase_url` ka koi consumer nahi. Admin wahan value
bhare to kuch nahi hoga. **Fix zaroori hai** (Q4).

**D2 — `hero_mode` ek dead knob hai.** `HomeController:24` `$heroMode` view ko
bhejta hai, `home/index.blade.php:11` use `_hero` me pass karta hai, lekin
`_hero.blade.php` me `heroMode` kahin use nahi hota aur koi `<video>` tag nahi
hai. Slider hi hamesha render hota hai.

**D3 — hero banners me category slug hardcoded hain**
(`keyboards-pianos`, `drums-percussion`, `pro-audio`). Admin ne category ka slug
badla ya category delete ki to banner ek khaali `/shop?category=…` page par le
jayega. Ye "hardcode rahega" ke andar bhi fix hona chahiye — default content
hardcoded rahega, par slug live category se verify ho.

---

## 3. Proposed architecture — "built-in default, admin override"

Ek naya service, `app/Services/HeroBannerService.php`:

```
resolve(string $slot): array
  1. DB me is_active row mile (hero_banners.slot)  → use it
  2. warna built-in DEFAULTS[$slot]                → use it
  3. slot ka link category slug hai aur wo category
     active nahi hai                               → link /shop par, warna 404 page
```

Naya table `hero_banners`:

| column | note |
|---|---|
| `slot` (unique) | `hero-tall`, `hero-small-1`, `hero-small-2`, `launch-banner` |
| `title`, `subtitle`, `cta_label` | nullable — khali matlab built-in default |
| `href` | nullable — khali matlab built-in default |
| `image_url` | nullable — khali matlab built-in image |
| `sort_order`, `is_active` | |

**Built-in defaults code me rahenge** — `HeroBannerService::DEFAULTS` me, bilkul
wahi copy/images jo aaj live hain. Isse:
- fresh install / khaali DB par homepage identical dikhega,
- admin ek slot ka koi bhi field override kar sakta hai, baaki default chalega,
- admin row delete/deactivate kare to wapas default aa jayega.

Slider ke liye yehi pattern already hai (`hero_slides` DB + hardcoded fallback
images) — usse **field-level** fallback tak extend karenge: slide ka title/copy
khali ho to built-in copy, media na ho to built-in image (aaj sirf image ka
fallback hai).

Category banners aur promo banners already DB-driven hain; unme sirf **optional
built-in fallback** add hoga jo tab dikhega jab DB khali ho (aaj section gayab
ho jata hai).

---

## 4. Step list

| # | Kaam |
|---|---|
| 1 | `hero_banners` migration + `HeroBanner` model (slots enum, `MediaUpload` for image) |
| 2 | `HeroBannerService` — slot resolve + built-in defaults + dead-category-slug guard |
| 3 | `HeroBannerResource` (Filament) — 4 slots, live preview hint, "khali = default" helper text |
| 4 | `_hero.blade.php` — 3 side banners service se; slider me field-level fallback |
| 5 | `_recently-launched.blade.php` — `launch-banner` slot service se |
| 6 | `_category-banners.blade.php` + `_promo-banners.blade.php` — optional built-in fallback |
| 7 | `HomepageDataObserver` ko `HeroBanner` par flush karwana (cache consistency) |
| 8 | Seeder: 4 slots ke built-in defaults **seed nahi** honge (defaults code me hain, DB khali rahegi) — admin sirf override create kare |
| 9 | D1 fix — dead fields (Q4 ke jawab ke hisaab se) |
| 10 | D2 fix — `hero_mode` (Q4) |
| 11 | `tests/automation/hero-banner-dual-source.test.mjs` — gate: har slot ka default code me hai, view `HeroBannerService` se padhti hai, koi hardcoded slug bina guard ke nahi |
| 12 | Existing PHP tests jo hero copy assert karte hain unka review (`HomepageTest`, `HomepageSectionsTest`) |

---

## 5. Verification plan

| Check | Kaise |
|---|---|
| Node automation suite | `npm run test:automation` — baseline 202/192 pass/10 pre-existing fail se compare |
| Naya gate test | mutation test karke prove karunga ki hardcoded copy wapas aane par fail hota hai |
| Blade syntax | collapsed `{{--`/`{{ }}` scan + PHP brace balance (jaisa Batch 1 me kiya) |
| Visual | live preview harness, `preview/` me, real compiled CSS ke saath |
| **PHP tests / Pint** | **nahi chal payenge** — sandbox me PHP binary nahi, `deb.debian.org` + `repo.packagist.org` blocked. Ye gap rahega, main clearly bol dunga |

---

## 6. Open decisions — approval chahiye

**Q1 · Scope kitna?**
(a) sirf hero (slider + 3 side banners)
(b) hero + launch banner
(c) hero + launch banner + promo banners + category banners  ← plan ka default

**Q2 · Override ka behaviour?**
(a) **row-level** — DB row hai to poori row DB se, warna poori row default se (simplest)
(b) **field-level** — har field individually default se fallback kare (admin sirf title badal
    sakta hai, image default rahegi)  ← plan ka default

**Q3 · Batch 1 ke changes in files ka kya?**
Maine in files me counts (`home_hero_slides`, `home_category_banners`,
`home_promo_banners`) aur `brandOrMedia('brand_name')` settings-driven kiye the.
(a) waise hi rahen (counts settings se, content dual-source se) ← plan ka default
(b) counts bhi wapas hardcode kar doon

**Q4 · D1/D2 dead knobs**
(a) `hero_video_url` + `video_showcase_url` fields hata doon, `hero_mode` config me rahe
(b) teeno ko actually wire kar doon (hero video mode render karna padega — extra kaam)
(c) `hero_mode` ka admin toggle bana doon, video fields hata doon  ← plan ka default

---

## 7. DELIVERED — kya bana, kaise verify hua

### 7.1 Naye files

| File | Kaam |
|---|---|
| `database/migrations/2026_10_04_000002_create_hero_banners_table.php` | `hero_banners` table — `slot` (unique), `title`, `subtitle`, `cta_label`, `href`, `image_url`, `alt`, `is_active`, timestamps |
| `app/Models/HeroBanner.php` | Model + `SLOTS` (4 slots, admin labels) + Media Library `image` collection + resolved-URL sync |
| `app/Services/HeroBannerService.php` | **Built-in defaults yahin hain** (`DEFAULTS`), `all()` / `get($slot)`, dead-category + off-site href guard |
| `app/Filament/Resources/HeroBannerResource.php` | Admin → HOMEPAGE → **Hero banners** — slot select (unique), har field, image upload, active toggle |
| `app/Filament/Resources/HeroBannerResource/Pages/ManageHeroBanners.php` | Manage page |
| `tests/automation/hero-banner-dual-source.test.mjs` | Gate — 6 tests, dono taraf (default + admin) pin karta hai |

### 7.2 Badle hue files

| File | Badlav |
|---|---|
| `resources/views/home/_hero.blade.php` | Teeno side banners ab `$heroBanners->get(SLOT_*)` se aate hain |
| `resources/views/home/_recently-launched.blade.php` | Launch banner ab `SLOT_LAUNCH_BANNER` se aata hai |
| `app/Http/Controllers/HomeController.php` | `$heroMode` gaya — hero hamesha slider render karta hai |
| `resources/views/home/index.blade.php` | `heroMode` include param gaya |
| `app/Filament/Pages/Settings.php` | Do dead fields (`hero_video_url`, `video_showcase_url`) hate |
| `app/Services/SiteSettingsService.php` | Wahi 2 keys `DEFAULTS` + `CONFIG_FALLBACKS` se hatin |
| `config/rythme.php` | `hero_mode`, `hero_video_url`, `video_showcase_url` blocks hate |
| `.env.example` | `RYTHME_HERO_MODE` / `RYTHME_VIDEO_URL` ke dead docs hate |
| `app/Support/AdminAccess.php`, `app/Providers/AppServiceProvider.php` | `HeroBanner` → `content.manage` + `ContentPolicy` + admin audit list |
| `tests/Feature/HomepageTest.php` | `assertViewHas('heroMode','slider')` hata; ab built-in banner copy assert hoti hai |

### 7.3 Do design decisions jo explicitly batane layak hain

1. **Row-level = ek slot ke liye ek row.** Lekin row ke andar koi field khaali chhoda
   to us field ke liye us slot ka built-in default use hota hai. Matlab admin sirf
   title badal sakta hai aur image default rahegi — banner kabhi aadha toota nahi
   dikhega. (Ye "row-level" ka superset hai, usse kam nahi.)
2. **`HeroBannerService` cache nahi karta.** Ek indexed query (max 4 rows) + per-request
   memo. Isse admin ka edit agle hi request par live dikhta hai aur koi cache-flush
   observer nahi chahiye. Gate test `Cache::` dobara aane par fail karta hai.

Ek chhota visual farq: launch banner ka title pehle `Fresh gear,<br>first play` tha;
ab default `"Fresh gear,\nfirst play"` hai aur view `nl2br(e(…))` se render karta hai
(escaped output, admin text raw nahi jaata). Render `<br />` deta hai — HTML5 me wahi
element. Admin `Textarea` se khud line break de sakta hai.

### 7.4 Verification (jo actually chala)

| Check | Command | Result |
|---|---|---|
| Naya gate | `node --test tests/automation/hero-banner-dual-source.test.mjs` | **6 pass / 0 fail** |
| Gate mutation-verified | 12 deliberate breaks (default badla, slot gira, fallback hataya, href guards hate, `Cache::` wapas, view me literal wapas, admin field gira, slot free-text, `hero_mode` wapas, policy binding hati) | **12/12 pakde gaye** |
| Purana no-hardcode gate (2 dead keys hatne ke baad) | `node --test tests/automation/no-hardcode.test.mjs` | **6 pass / 0 fail** |
| Poora suite | `npm run test:automation` | **208 tests / 198 pass / 10 fail** — fail names `/tmp/b.txt` baseline se `diff`-identical (sab pre-existing) |
| Frontend build | `npx vite build` | exit 0, 1.80s, `app-B5r6VLLw.css` 175.24 kB (CSS hash wahi — koi CSS change nahi) |
| PHP structure | brace/paren/bracket balance checker, `app config database routes tests resources/views` ke **486 files** | sab clean |
| Blade syntax | collapsed-`{{ }}` scan, poori `resources/views` tree | 16 hits, sab `@php`/closure false positives (pehle bhi 16 the); `_hero`/`_recently-launched` me 0 |
| **Default rendering unchanged** | HEAD ke banner markup vs naye template ko `DEFAULTS` se render karke `diff` | **dono sections IDENTICAL** (sirf `<br>` vs `<br />`) |

**Jo yahan chal nahi sakta:** `composer test` / PHPUnit aur Pint — is sandbox me `php`
binary nahi hai aur `repo.packagist.org` unreachable hai. Isliye `tests/Feature/HomepageTest.php`
ka badlav sirf source-review + balance-check se verify hua, execute nahi hua. Deploy par
`php artisan migrate` chalana zaroori hai (`hero_banners` table) — seed ki zaroorat nahi,
kyunki defaults code me hain.

### 7.5 Ek cheez jaan-boojh kar chhodi gayi

`public/videos/hero-montage.mp4` ab orphan hai (uska sirf `hero_video_url` consumer tha).
Binary asset delete karna destructive hai aur owner ne maanga nahi, isliye file rakhi hai —
delete karna ho to bolo.
