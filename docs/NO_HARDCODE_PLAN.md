# NO-HARDCODE RULE — audit + plan

> **Project rule (owner-mandated):** storefront content, counts, business rules,
> brand and media must never be hardcoded. Every such value lives in the
> database and is editable from the Filament admin panel.
>
> Code may still contain *technical* constants (CSS breakpoints, cache keys,
> SVG glyph paths, HTTP/CSP host allowlists, PHP enum values). Everything a
> merchant would ever want to change without a deploy must be admin-editable.
>
> **One owner-approved exception:** the homepage hero + banner sections keep their
> built-in copy/imagery in the code *and* are admin-overridable — a deliberate
> dual source, not an oversight. See `docs/HERO_BANNERS_DUAL_SOURCE_PLAN.md`
> (gate: `tests/automation/hero-banner-dual-source.test.mjs`).

Audit run against commit `4bf407c` + the shop-shortcut fixes on
`arena/01a105f2-rythm`. Every row below is a real `file:line`, not an estimate.

---

## What already works (do not redo)

| Surface | Mechanism |
|---|---|
| Shipping fee / free-shipping threshold / GST rate / state / GSTIN | `SiteSettingsService` + Admin → Settings |
| Contact phone, email, address, WhatsApp number + message | `SiteSettingsService` (empty ⇒ hidden) |
| Social links (IG / YT / FB / X / LinkedIn) | `SiteSettingsService` (empty ⇒ icon hidden) |
| Outbound mail From name/address | `MailSenderSettingsService` |
| Homepage kickers / titles / accents / bodies | `homepage_sections` + `HomepageSectionResource` |
| Hero slides, promos, testimonials, stories, UGC, comparison, numbers | `homepage_blocks` + `HomepageBlockResource` |
| FAQs, Pages, Categories, Brands, Products, Coupons, Returns | dedicated Resources |

## Findings

### A. Admin data already exists but the view ignores it — worst offenders

| # | Finding | Evidence |
|---|---|---|
| A1 | **USP strip is 100% hardcoded** even though `HomepageDataService` already loads `'usps' => HomepageBlock::section('usp')` and `HomepageDataSeeder` already seeds 6 USP rows. Admin edits do nothing. | `resources/views/home/_usp-strip.blade.php` (5 hardcoded `<div class="usp-strip__item">` blocks, 30 lines) vs `app/Services/HomepageDataService.php:58`, `database/seeders/HomepageDataSeeder.php:50-55` |
| A2 | **Footer CTA copy hardcoded** (kicker, headline, body, 2 buttons, 4 feature cards) | `resources/views/components/footer.blade.php:10-40` |

### B. Hardcoded business identifiers

| # | Finding | Evidence |
|---|---|---|
| B1 | **6 product slugs hardcoded** to pick the "Recently launched" rail | `app/Services/HomepageDataService.php:84-91` |
| B2 | Brand name / short name hardcoded in config | `config/rythme.php:10-11` (`'Rhythm Exports'`, `'RHYTHM'`) |
| B3 | Logo points at an external WordPress URL | `config/rythme.php:12` (`https://rhythmexports.com/wp-content/...`), used by `footer.blade.php:3`, `navbar.blade.php:3`, `_hero.blade.php:2` |
| B4 | Hero + showcase video URLs hardcoded | `config/rythme.php:41`, `config/rythme.php:57` |

### C. Hardcoded counts / limits (16 sites)

| Value | Site |
|---|---|
| 8 category shortcuts | `resources/views/livewire/shop-index.blade.php:15` |
| 5 footer category links | `resources/views/components/footer.blade.php:68` |
| 5 footer brand links | `resources/views/components/footer.blade.php:80` |
| 8 bestsellers | `app/Services/HomepageDataService.php:67` |
| 10 new arrivals | `app/Services/HomepageDataService.php:70` |
| 10 trending | `app/Services/HomepageDataService.php:73` |
| 8 best deals | `app/Services/HomepageDataService.php:80` |
| 16 brands | `app/Services/HomepageDataService.php:114` |
| 3 hero slides | `resources/views/home/_hero.blade.php:4` |
| 2 brands shown | `resources/views/home/_brands.blade.php:48` |
| 3 category banners | `resources/views/home/_category-banners.blade.php:5` |
| 3 testimonials | `resources/views/home/_confidence.blade.php:2` |
| 6 FAQs | `resources/views/home/_confidence.blade.php:3` |
| 2 promo banners | `resources/views/home/_promo-banners.blade.php:5` |
| 8 marquee offers | `resources/views/home/_offer-marquee.blade.php:17` |
| 12 products per page | `app/Services/ProductQueryService.php:22` (`PER_PAGE`) |

### D. Hardcoded business rules

| # | Finding | Evidence |
|---|---|---|
| D1 | Offer eligibility window `10% – 50%` duplicated in two views | `_offer-marquee.blade.php:16`, `_offer-popup.blade.php:13` |
| D2 | Currency symbol `₹` inline in **57 places across 20 view files** | e.g. `shop-card.blade.php`, `cart-page.blade.php`, `orders/invoice.blade.php`, `emails/order-confirmation.blade.php` |
| D3 | Price decimals inconsistent — `number_format($x)` in some places, `number_format($x, 2)` in others | `checkout/success.blade.php:41` vs `components/gst-lines.blade.php:20` |

### E. Deliberately left in code (technical constants — not merchant-editable)

`resources/css/app.css` breakpoints/spacing, `SecurityHeaders` CSP host list,
`SkuGenerator::RANDOM_LENGTH`, `NotificationRetryService::MAX_ATTEMPTS`,
`PaymentRetryService::MAX_PAYMENT_ATTEMPTS`, `ProductQueryService::MAX_*_FILTERS`
(abuse caps), `HomepageDataService::MAX_CATEGORY_ROWS` / `MAX_DISCOVERY_CATEGORIES`
(layout invariants), SVG glyph paths, `IndiaStates`.

---

## Plan

### Batch 1 — DONE on `arena/01a105f2-rythm`

| Step | Change | Admin surface |
|---|---|---|
| 1.1 | `SiteSettingsService::DEFAULTS` gains a **Storefront** key group + `getInt()` accessor. Every default equals today's hardcoded value, so behaviour is unchanged until an admin edits it. | Admin → Settings → *Storefront* |
| 1.2 | All 16 counts in section C read from settings. | Admin → Settings → *Storefront* |
| 1.3 | Offer window 10–50 % (D1) read from settings in both views. | Admin → Settings → *Storefront* |
| 1.4 | `recently_launched_slugs` setting (B1) replaces the hardcoded slug array. | Admin → Settings → *Storefront* |
| 1.5 | Brand name / short name / logo (B2–B4) move to settings; `config/rythme.php` keeps its values as the fallback of last resort. *(The `hero_video_url` / `video_showcase_url` fields added here were later deleted — they had no consumer; see `docs/HERO_BANNERS_DUAL_SOURCE_PLAN.md` §7.)* | Admin → Settings → *Brand & media* |
| 1.6 | USP strip (A1) renders `homepage_blocks` (`usp`); section hides when no active rows. New nullable `icon` column on `homepage_blocks` + `<x-icon>` glyph library, so the icon is admin-chosen too. | Admin → Homepage blocks → *Why Rythme (USPs)* |
| 1.7 | Currency symbol (D2) becomes one `@currency` Blade directive backed by a setting. All 55 storefront `₹` literals removed (the 2 left in `navbar.blade.php` are inside a `{{-- --}}` comment). Also swept out of `app/`: 9 Filament field prefixes, coupon/checkout messages, the admin revenue stat. The only literal left in the codebase is `SiteSettingsService::DEFAULTS['currency_symbol']`. | Admin → Settings → *Storefront* |
| 1.8 | New `tests/automation/no-hardcode.test.mjs` (6 tests) gates the rule — including a generic check that **every key in `DEFAULTS` has a matching admin form field**. Mutation-tested: reintroducing `->limit(8)` or a hardcoded USP item fails the suite. | CI |
| 1.9 | Settings save now flushes `HomepageDataObserver` and `CategoryService`, so a changed count/currency/brand is live immediately instead of after the 1 h cache. | — |

### Batch 2 — next

| Step | Change | Why deferred |
|---|---|---|
| 2.1 | Footer CTA copy (A2) → `homepage_sections` row `footer-cta` + seeder | Needs a new section key + copy review with the owner |
| 2.2 | Price decimals policy (D3) → single `money()` helper with a `currency_decimals` setting | Touches invoices/emails; needs owner sign-off on 0 vs 2 decimals per surface |
| 2.3 | Products-per-page (C, `PER_PAGE`) → setting | Changes pagination + canonical/`rel=next` behaviour and SEO; must be tested with a live catalogue |
| 2.4 | Design tokens (section E CSS) → admin theme editor | Large surface, no way to visually regression-test it in this environment |

---

## Batch 1 verification

| Check | Result |
|---|---|
| `npm run test:automation` | 202 tests, **192 pass / 10 fail** — the same 10 failures exist on the pre-change baseline (`diff` of failure names identical) |
| `node --test tests/automation/no-hardcode.test.mjs` | **6/6 pass**; fails when `->limit(8)` or a hardcoded USP item is reintroduced |
| Two existing tests updated | `homepage-minor-ui.test.mjs` asserted the literal `discount >= 10 / <= 50`; it now asserts the setting-backed `$minDiscount`/`$maxDiscount` |
| PHP syntax | every modified PHP file brace/paren/bracket balanced (checked after stripping strings and comments). One real syntax error was caught and fixed this way in `CouponResource.php` |
| `composer test` / Pint | **not run** — no PHP binary in this environment and `deb.debian.org` + `repo.packagist.org` are unreachable, so `composer install` is impossible |

Migration to run on deploy: `2026_10_04_000001_add_icon_to_homepage_blocks_table`.
Re-seed after deploy (`php artisan db:seed --class=HomepageDataSeeder`) so the USP
strip picks up its 5 admin-editable rows; the 6 superseded rows are deactivated,
not deleted.

## Rule for future work

1. New storefront copy ⇒ `homepage_sections` or `homepage_blocks`, never Blade.
2. New count / threshold ⇒ `SiteSettingsService::DEFAULTS` + a field in
   `app/Filament/Pages/Settings.php`.
3. New brand/media value ⇒ same, never `config/`.
4. `tests/automation/no-hardcode.test.mjs` must stay green.
