# Phase 1 — Homepage + Shop Design Specification

**Status:** COMPLETE — accepted by Agent 0 on 25 August 2026
**Owners:** Agent 1 (specification), Agent 2 (feasibility), Agent 13 (accessibility/SEO)
**Final acceptance:** Agent 0 only
**Brand:** Rythme / Rhythm Exports

## 1. Design Direction

Use the XStore Electronic Mega Market references for information hierarchy, marketplace density and component composition. Retain Rythme identity, Indian currency/context and musical-instrument taxonomy. Do not reproduce reference products, imagery, copy, trademarks or theme code.

The owner confirmed **Current Logo First**, initially reviewed the measured reference teal, and then explicitly replaced it with **Rythme Red**. Retain the existing Rythme / Rhythm Exports logo treatment and use the owner-approved deep-red accent in the storefront visual system. All future visual changes must use semantic tokens rather than hardcoded view-level colors.

## 2. Responsive Verification Viewports

Implementation and visual comparison must include:

- Desktop: 1440 × 900 viewport.
- Tablet: 768 × 1024 viewport.
- Mobile: 390 × 844 viewport.
- Additional overflow check: 320px width.

Owner-supplied 1440px and 390px DPR-2 reference screenshots are accepted and measured in `tasks/PHASE_1_SCREENSHOT_MEASUREMENTS.md`. Tablet behavior may be interpolated conservatively between those anchors and verified independently.

## 3. Shared Header Contract

### Desktop

- Row 1: logo, dominant search field, support contact and account/wishlist/cart actions.
- Row 2: All Categories control, primary navigation and sale/currency area.
- Search remains a semantic GET form to Shop.
- Categories expose keyboard-operable nested navigation; hover may enhance but cannot be the only trigger.
- Sticky behavior must not cover focused content or create layout shift.

### Mobile/tablet

- Single compact row with menu, logo, search trigger and cart.
- Search opens as a clearly labelled region.
- Navigation opens as a modal drawer with focus moved inside, focus trapped, Escape support and focus restored to the opener.
- Menu/Categories tabs use complete tab semantics or are implemented as ordinary labelled buttons/regions; no incomplete ARIA pattern.

### Current feasibility decision

Retain the existing Blade/Alpine structure for Phase 3 implementation, but remediate drawer focus handling and validate the tab pattern. Do not replace it solely for visual reasons.

## 4. Homepage Contract

### Required order

1. Split marketplace hero.
2. Five trust/service benefits.
3. Popular Categories.
4. New Arrivals.
5. Two promotional banners.
6. Advantages grid.
7. Deals Of The Day.
8. Three category campaign banners.
9. Recently Launched.
10. Popular Brands.
11. Optional editorial/articles area only when real publishable content exists.
12. Offer/newsletter callout only with approved consent and delivery workflow.
13. Shared footer.

### Hero

- One primary carousel panel plus one tall and two short static campaign panels on desktop.
- Tablet stacks the primary panel above a two-column banner composition.
- Mobile uses one column and preserves text contrast/crop safety.
- First slide owns the page `h1`; later slides use `h2`.
- Controls require accessible names, visible focus and minimum 44 × 44px targets.
- Carousel must pause on pointer hover and keyboard focus, expose pause control if auto-advancing beyond five seconds, and honor reduced motion.
- Promotional imagery must have deliberate focal points for desktop/mobile crops.

### Category and product sections

- Section heading plus optional “View all” action.
- Category tiles contain image, label and product count.
- Product cards consistently expose category/brand, product name, approved rating summary, current price, original price when discounted, stock/sale state and wishlist/cart affordances.
- Product image boxes use one agreed aspect ratio per component to avoid layout shift.
- Empty sections are hidden or show an approved editorial fallback; never render broken carousels.

### Deals

- Sale price and original price remain programmatic text, not image text.
- Countdown may display only when backed by a real expiration timestamp.
- Stock/sold progress must come from valid inventory/order data; otherwise omit it.

### Articles/newsletter decision

Do not activate the existing Stories partial just to mimic reference density. It requires a real editorial route/content workflow. Newsletter UI may be added only when consent wording, submission behavior and administration are valid. Until then, the existing footer CTA remains a safe non-equivalent fallback and must not be claimed as a match.

## 5. Shop Contract

### Page hierarchy

1. Optional compact promotional banner.
2. Horizontally scrollable shortcut tiles on narrow screens; grid/carousel on desktop.
3. Results area with desktop sidebar and mobile filter drawer.
4. Results count, sort control and active-filter chips.
5. Product grid/list area.
6. Pagination.

### Filter contract

- Required now: category, brand, price, availability and sale state.
- Required after Phase 2 domain support: rating and category-aware product attributes (for example finish/color, instrument type, pickup configuration, keyboard size or connectivity).
- Facet labels include result counts where query cost is acceptable.
- Long category/brand lists include text search.
- Applying/removing filters updates the canonical query state and returns focus/status feedback appropriately.
- Mobile drawer includes clear close/apply behavior and cannot trap scroll/focus incorrectly.
- Do not add decorative “color” filters before normalized attribute data exists.

### Results toolbar

- Results count is announced as status after Livewire updates without excessive interruption.
- Sort labels must describe actual backend behavior. Rename/remove “Popularity” until backed by a documented metric.
- Grid/list density and products-per-page controls are optional; add only if persisted, keyboard accessible and useful on realistic catalog volume.

### Product grid

- Desktop target is determined after screenshot measurement; current three-column results grid is provisional.
- Mobile uses two columns only if 320px checks preserve readable names, prices and 44px actions; otherwise use one column at the narrowest breakpoint.
- Loading skeletons reserve final card dimensions and are hidden from assistive technology.
- Empty state includes one clear reset action.

## 6. Approved Design-token Direction

The owner selected the **Rythme Red** direction while retaining the current logo. This is an intentional brand-color deviation from the measured teal reference. Phase 3 must implement these as semantic tokens and may tune only secondary shades during contrast/visual regression review:

| Role | Approved value/direction |
|---|---|
| Primary accent | `#B20202` |
| Accent hover/strong | `#930303` |
| Accent soft surface | `#E7F4F1` |
| Main text / dark footer | `#222222` |
| Main surface | `#FFFFFF` |
| Alternate section surface | `#F7F7F8` |
| Neutral border | `#E5E7EB` |
| Focus indication | high-contrast teal/dark ring with at least 3:1 adjacent contrast |

White on `#B20202` measures approximately **7.24:1**, and white on `#930303` approximately **9.31:1**, passing WCAG AA for normal text. Main `#222222` text on white measures approximately **15.91:1**.

Additional Phase 3 token work:

- Align Homepage and Shop to the shared near-full-width marketplace container evidenced at 1440px.
- Retain content-fit breakpoints around current 640/768/1024 anchors, then verify at 320/390/768/1440.
- Define typography, spacing, radii, shadows and motion as semantic tokens from screenshot proportions; do not hardcode them in Blade views.
- Preserve reduced-motion alternatives and avoid using teal as the sole indicator of state.

## 7. Accessibility Acceptance (WCAG 2.2 AA)

- One logical `h1`; heading levels follow page hierarchy.
- Every control is keyboard operable with visible focus.
- Focus is not obscured by sticky header (2.4.11).
- Minimum target size is 24 × 24px under WCAG 2.2 AA; project target is 44 × 44px for primary touch actions.
- Normal text contrast ≥ 4.5:1; large text/UI boundaries ≥ 3:1 where applicable.
- Status/sale/stock is not conveyed by color alone.
- Drawers/dialogs have correct naming, focus containment, Escape and focus restoration.
- Carousels and animated transitions honor `prefers-reduced-motion`.
- Livewire loading/results changes use restrained status announcements.
- Product images have useful alt text; decorative campaign backgrounds are hidden when adjacent text communicates the offer.
- Forms have persistent labels or equivalent accessible names; errors are associated and summarized where appropriate.

## 8. Technical SEO Acceptance

- Homepage has self-canonical and one descriptive `h1`.
- Shop base URL has self-canonical; filter/sort combinations follow an approved canonical/noindex policy.
- Internal search-result URLs are `noindex, follow` unless an SEO specialist approves an indexable landing page.
- Pagination remains crawlable and does not canonicalize every page to page 1.
- Product structured data belongs on product detail pages, not listing cards.
- Organization/WebSite SearchAction schema may be used only with valid real business/search URLs.
- Breadcrumb markup must match visible breadcrumbs.
- Images have intrinsic dimensions and responsive sources where practical.
- Promotional copy must not make unverifiable price, delivery, trade-in, EMI or service claims.

## 9. Phase 3 Implementation Handoff

### Retain/refine

- Existing shared navbar, Homepage section partials, Livewire Shop base, active filter chips, mobile filter drawer, skeleton and empty state.
- Existing local asset strategy and admin-driven hero/product sections.

### Add in Phase 3 when design metrics are approved

- Measured visual token adjustments.
- Shop shortcut tiles and approved promotion area.
- Search within large facets.
- Accessibility remediations for drawers/carousels/live updates.
- Reference-comparison fixtures/screenshots.

### Defer to Phase 2/domain work

- Rating facet.
- Normalized category-aware attribute/color facets.
- True popularity sorting metric.
- Any stock/sold progress requiring an inventory ledger.

## 10. Phase 1 Acceptance Checklist

- [x] Current Homepage/Shop component inventory completed.
- [x] Live reference semantic structure captured.
- [x] Current-to-reference gap matrix completed.
- [x] Responsive behavior contract drafted.
- [x] Accessibility and technical SEO contract drafted.
- [x] Phase 2/3 dependencies identified.
- [x] Desktop and mobile reference screenshots supplied and measured.
- [x] Current Rythme / Rhythm Exports logo treatment retained (owner: “Current Logo First”).
- [x] Final color direction confirmed: Rythme Red `#B20202`.
- [x] Design-token direction updated from evidence with WCAG contrast checks.
- [x] Agent 0 final design-spec acceptance.

Phase 1 is complete. Pixel accuracy remains an implementation claim that must be proven by Phase 3 side-by-side desktop/mobile visual regression; this specification alone does not claim the current storefront already matches.
