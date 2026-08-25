# Phase 1 — Reference Screenshot Measurements

**Received:** 25 August 2026
**Source:** Owner-captured current XStore Electronic Mega Market Homepage and Shop references
**Method:** PNG metadata, pixel inspection and visual component-boundary review
**Status:** ACCEPTED as visual reference evidence, subject to the capture limitations below

## 1. Evidence Identity

| Page / viewport | Uploaded file | Physical PNG | Interpreted CSS viewport | SHA-256 |
|---|---|---:|---:|---|
| Homepage desktop | `xstore.8theme.com_elementor3_electronic-mega-market_ (1).png` | 2880 × 13882 | 1440px wide at DPR 2; 6941px captured document height | `7eb55b576928092e31967c1f071ea4a6da419af33732edf0b6eb0b10f8b3ecdd` |
| Homepage mobile | `xstore.8theme.com_elementor3_electronic-mega-market_.png` | 780 × 16384 | 390px wide at DPR 2; capture capped at 8192 CSS px | `26e96698ea2e33374d4259efd8348a5e615803c843390594b99d0dfe08fe9795` |
| Shop desktop | `xstore.8theme.com_elementor3_electronic-mega-market_shop_.png` | 2880 × 7128 | 1440px wide at DPR 2; 3564px captured document height | `09f079f71a5f07bb72cf8553c33c38a7bdc1b2b73730220404f4c08f13477590` |
| Shop mobile | `xstore.8theme.com_elementor3_electronic-mega-market_shop_ (1).png` | 780 × 10840 | 390px wide at DPR 2; 5420px captured document height | `2ac48ab5e2c9b0e043a0370dc0d86cd4825b9b879b6b675647ed33fa1f49f516` |

The original PNGs remain user-supplied evidence and are not committed into the repository. Their hashes preserve identity without republishing third-party reference imagery.

## 2. Capture Limitations

- The 390px Homepage capture reaches the platform's 16384-physical-pixel cap and ends during the lower product area. It still captures the complete mobile header, hero, benefits, categories, new arrivals, promotional panel, advantages, deals, category campaigns, recently launched composition and popular brands.
- The Shop desktop capture contains a large blank region above the visible header/content, likely a full-page capture/rendering artifact. Main Shop composition and footer remain measurable.
- Screenshot evidence does not grant rights to copy XStore imagery, products, copy or source code.

## 3. Measured Visual System

### Palette sampled from screenshots

| Role | Reference sample | Use in specification |
|---|---|---|
| Primary marketplace accent | `#00796B` | Hero/promotion background, buttons, active states and rating stars in the reference |
| Main text/footer | approximately `#222222` | Headings, body emphasis and dark footer |
| Main surface | `#FFFFFF` | Page and card surfaces |
| Soft section/facet surface | approximately `#F5F5F5`–`#F7F7F8` | Alternating sections, category tiles and sidebar panels |
| Borders/dividers | very light neutral grey | Low-emphasis separation rather than heavy card outlines |

Exact anti-aliased text colors vary at edge pixels. Token implementation must use stable semantic values and pass contrast checks rather than copying every sampled raster value.

### Desktop composition

- Main content occupies nearly the full 1440px viewport with narrow outer gutters, producing a dense marketplace layout.
- Homepage hero is a four-panel composition: one dominant primary panel, one tall secondary panel and two stacked secondary panels.
- The benefits row is a single five-item strip immediately below the hero.
- Category and product sections use six visible items across at desktop reference width.
- Broad promotional content is full-row and visually shallow relative to the hero.
- Deals use a large left product/deal card plus a right campaign mosaic.
- Recently Launched uses a campaign/category panel plus a dense multi-row product grid.
- Footer uses five content columns above a compact payment/copyright strip.

### Mobile composition at 390px

- Header compresses to a single compact bar.
- Homepage hero panels stack in this order: dominant feature, tall secondary, short secondary; the fourth desktop panel is not visible in the captured initial composition and must not be forced above more important content.
- Benefits become a vertical stack of compact rows.
- Category and product rows show two items across or a horizontal subset with restrained gutters.
- Promotional and campaign mosaics become single-column cards.
- Recently Launched keeps the campaign panel first, then a two-column product grid.
- Popular Brands becomes a one-column logo list.
- Shop mobile removes the desktop sidebar and presents shortcut pills, a compact result/sort toolbar and a two-column product grid.
- Shop footer becomes stacked link groups and retains a bottom mobile navigation bar in the reference; Rythme may keep its own accessible mobile navigation rather than copying this feature automatically.

## 4. Current Rythme Comparison

| Area | Reference evidence | Current Rythme state | Phase 3 direction |
|---|---|---|---|
| Width/density | Near-full-width desktop marketplace | Homepage max 1520px; Shop max `7xl` and therefore narrower | Keep Homepage width; widen Shop to the shared marketplace container after regression checks |
| Hero proportions | Dominant panel plus three supporting campaigns | Existing 2fr/1fr/1fr composition | Retain structure; tune proportions/crops against screenshot |
| Accent | Teal `#00796B` | Provisional monochrome `#111111` | Owner decision required; logo remains current |
| Homepage section order | Matches active Rythme through Popular Brands | Strong structural match | Retain and restyle; do not activate fake editorial/newsletter content |
| Product density | Six across in full-width Homepage sections | Current sections vary by component | Target measured density while preserving readable musical-instrument names/prices |
| Shop shortcuts | Prominent circular shortcuts above results | Missing | Add in Phase 3 |
| Shop sidebar | Category, price, rating, status, color, brands, social/promo | Category, brand, price, in-stock and sale controls | Preserve safe filters; add search/shortcuts; rating/attributes wait for Phase 2 |
| Shop result density | Four products across beside sidebar | Current three at `xl` | Target four only if real content and 320px/mobile checks pass |
| Mobile Shop | Two-column product grid | Already two columns | Retain, verify 320px fallback and touch targets |
| Footer | Dense dark marketplace footer | Dark branded Rythme footer | Retain Rythme content/identity; tune density only |

## 5. Screenshot Gate Decision

The required desktop/mobile visual references are accepted. They now support proportional and responsive implementation targets, but not third-party content copying. The owner selected the measured teal accent (`#00796B`) while retaining the current Rythme / Rhythm Exports logo. The screenshot and color gates are accepted for Phase 1; implementation fidelity remains a Phase 3 verification requirement.
