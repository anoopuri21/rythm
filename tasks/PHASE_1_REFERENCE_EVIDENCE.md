# Phase 1 — Homepage and Shop Reference Evidence

**Evidence date:** 25 August 2026
**Reference Homepage:** `https://xstore.8theme.com/elementor3/electronic-mega-market/`
**Reference Shop:** `https://xstore.8theme.com/elementor3/electronic-mega-market/shop/`
**Current application:** `resources/views/home`, `resources/views/livewire/shop-index.blade.php`, shared navbar/footer, and `resources/css/app.css`

## 1. Evidence Confidence Rules

| Grade | Meaning | Permitted use |
|---|---|---|
| A — measured | Local source contains an explicit value, or a supplied viewport capture can be measured | Implementation acceptance target |
| B — observed | Live reference semantic content/structure is directly retrievable | Section and feature specification |
| C — inferred | Visual behavior is suggested but not measured | Must not be called pixel-accurate |
| BLOCKED | A browser screenshot/computed style or owner asset is required | Cannot close Phase 1 |

Owner-supplied 1440px and 390px DPR-2 screenshots now provide Grade A viewport evidence. See `tasks/PHASE_1_SCREENSHOT_MEASUREMENTS.md`. Semantic extraction remains the source for text/control hierarchy; screenshots provide composition, density, palette and responsive evidence.

## 2. Reference Homepage — Observed Structure (Grade B)

The reachable live page exposes this hierarchy:

1. Marketplace header/navigation and search.
2. Hero promotion composition: one primary promotion plus three secondary banners.
3. Five service/value statements.
4. Popular Categories carousel/grid with image, label and product count.
5. New Arrival Products with category, title, rating and pricing.
6. Two broad promotional campaigns.
7. Our Advantages grid with eight service propositions.
8. Deals Of The Day with sale price, stock/sold status and countdown.
9. Three category/promotion banners.
10. Recently Launched composition: one feature banner, category links and products.
11. Popular Brands logo strip.
12. Additional featured product/banner content.
13. From Our Articles cards.
14. Final promotions callout and offer/newsletter capture.
15. Footer.

### Current Rythme mapping

| Reference area | Current source | Verdict |
|---|---|---|
| Two-row marketplace header/search/categories | `components/navbar.blade.php` | PRESENT; geometry unverified |
| Split hero + three secondary banners | `home/_hero.blade.php` | PRESENT; current explicit desktop grid is 2fr/1fr/1fr and 2 × 272px |
| Five value statements | `home/_usp-strip.blade.php` | PRESENT |
| Popular categories | `home/_categories.blade.php` | PRESENT |
| New arrivals | `home/_new-arrivals.blade.php` | PRESENT |
| Two promo campaigns | `home/_promo-banners.blade.php` | PRESENT |
| Eight advantages | `home/_advantages.blade.php` | PRESENT |
| Deals | `home/_deals.blade.php` | PRESENT |
| Three category banners | `home/_category-banners.blade.php` | PRESENT |
| Recently launched | `home/_recently-launched.blade.php` | PRESENT |
| Popular brands | `home/_brands.blade.php` | PRESENT |
| Articles | Partial exists as `_stories.blade.php`, but is not included by `home/index.blade.php` | GAP / scope decision in specification |
| Final promotion/newsletter | Footer CTA differs; no equivalent offer/newsletter block in active Homepage order | GAP |
| Footer | `components/footer.blade.php` | PRESENT; reference match unmeasured |

## 3. Reference Shop — Observed Structure (Grade B)

The reachable live Shop exposes:

1. Promotional hero/banner.
2. Shop shortcut tiles: All, New Arrivals, Sale, Daily Deals and categories.
3. Desktop sidebar with category search/list and counts.
4. Price filter.
5. Rating filter.
6. Product-status filters: in stock, out of stock, on sale.
7. Color/attribute filter.
8. Brand search/list and counts.
9. Sidebar promotion and social links.
10. Grid/list view controls (3/4/5 columns and list).
11. Products-per-page control.
12. Sort options: default, popularity, average rating, latest and price.
13. Product cards with category, name, rating, price/range, sale state and optional countdown.
14. Responsive behavior is visually implied but not measurable from semantic extraction.

### Current Rythme mapping

| Reference area | Current source | Verdict |
|---|---|---|
| Category, brand and price filters | `livewire/shop-index.blade.php` | PRESENT |
| In-stock and on-sale filters | same | PRESENT |
| Mobile filter drawer | same | PRESENT |
| Active filter chips and clear action | same | PRESENT; useful enhancement |
| Sorting | same | PRESENT, but “Popularity” is not backed by true popularity evidence |
| Shortcut category tiles | none in Shop template | MISSING |
| Shop promotional banner | none | MISSING |
| Rating facet | no Shop control/domain filter | MISSING; schema/query dependency |
| Color/attribute facets | no attribute domain | MISSING; Phase 2 schema dependency |
| Out-of-stock explicit facet | current control is in-stock-only | PARTIAL |
| Brand/category search within long facets | none | MISSING |
| Grid/list and products-per-page controls | none | MISSING / optional after UX validation |
| Rating on cards | requires card/data verification | PARTIAL / unverified |

## 4. Current Local Measured Baseline (Grade A — Local Only)

The following are current implementation values, not reference measurements:

- Header content maximum width: 1520px; horizontal desktop padding: 30px.
- Header desktop row 1 height: 76px; mobile row height: 64px.
- Header responsive switch: max-width 1024px.
- Hero desktop maximum width: 1520px; columns 2fr/1fr/1fr; rows 272px + 272px; gap 14px.
- Hero tablet rule begins below 1024px; single-column mobile rule begins below 640px.
- Hero tablet primary slider height: 420px; mobile primary slider height: 440px.
- Homepage palette currently uses monochrome tokens: ink `#111111`, paper `#FFFFFF`, paper-dark `#F6F6F6`, muted `#6B6B6B`.
- Current type stack maps all semantic families to Inter/system sans.
- Current Shop desktop layout uses a 270px sidebar and flexible results column inside max-width `7xl`.
- Current Shop grid is two columns by default and three columns at `xl`.

These values must not be treated as approved reference values until screenshot comparison is available.

## 5. Final Acceptance Decision

The four requested viewport captures were supplied and accepted. PNG widths are exactly 2880 and 780 physical pixels, consistent with the requested 1440px and 390px viewports at DPR 2. The mobile Homepage capture is capped at 16384 physical pixels but contains every active reference section required for the Rythme Homepage contract through Popular Brands.

The owner retained the current logo and selected the measured Reference Teal accent (`#00796B`). Screenshot/reference evidence is sufficient for Phase 1 specification acceptance. Exact font rendering and pixel accuracy will be validated during Phase 3 visual implementation rather than inferred from raster anti-aliasing.

## 6. Legal/Content Boundary

XStore is a layout/interaction reference only. Its product names, descriptions, promotional copy and imagery are not approved Rythme assets and must not be copied. Musical-instrument product content and imagery require owned, licensed or supplier-authorized sources. This evidence report makes no commercial-rights determination.
