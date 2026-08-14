# Homepage Redesign Plan — Minimal-Tech (reference-analyzed)

> Reference: https://xstore.8theme.com/elementor3/minimal-tech/
> Status: analysis done (browser-run, scroll + animations recorded) → plan →
> implementation phases. Hero dual-mode imagery: IMPLEMENTED (this batch).

---

## 1. Reference Analysis (recorded by running in browser @1440×900)

### Behaviour / transitions / animations observed
| Feature | Observed on reference | Notes |
|---|---|---|
| Header | `position: fixed` always on top | `etheme-elementor-header-wrapper fixed top:0` |
| Hero slider | Full-width Swiper, **950px tall**, inside bottom pagination + arrows | `elementor-slider-full-width`, `swiper-container-horizontal` |
| Hero heading | **H2 74px, Inter 700**, short punch lines (2–3 words) | e.g. "Work smarter. Connect faster. Achieve more." |
| **Scroll parallax** | `elementor-motion-effects-layer` — `translateY` grows with scroll: −99 → −175 → −251 → −322 → −380 px | scroll-driven motion layer behind content (hero + banners) |
| Section flow | Hero → **3 promo image blocks** (big image + heading + Shop now) → **Trending Categories** (icon tiles) → Innovation banner → **Discover Top Picks** (product cards) → more product grids | |
| Cards | Product: image (330×330) → category → name → price → stock · thin, clean | |
| Body | **#F4F4F4** light gray, text #555, Inter | |
| Buttons | Simple `elementor-button` (flat, no glow) | |
| Animations | Subtle — no heavy reveals; motion mainly = slider + parallax layers | |

### Reference section inventory (top → bottom)
1. Hero slider (full-width, 950px, big images)
2. 3 promo image blocks (image + heading + "Shop now")
3. Trending Categories — icon tiles (11 icons)
4. "Innovation Experience" banner (H2 + image)
5. Discover Top Picks — product grid
6. Product grids (multiple)

---

## 2. Our Homepage Plan (mapped to Rythme content — music store)

Keep existing section **ids** (tests + deep-links depend on them), restyle/reorder visually per minimal-tech:

| Order | Section (id) | Redesign action |
|---|---|---|
| 1 | **hero** | ✅ **DONE (this batch)** — dual-mode imagery: desktop large banner (`image`), mobile portrait banner (`mobile_image`, <picture media≤767px>); parallax kept; inside pagination + arrows kept |
| 2 | **promos** (NEW) | Add reference-style 3 big promo image blocks (deals-banner.jpg / why-rythme.jpg / brand-feature.jpg): big image + short H2 + "Shop now" — light-gray section, no card chrome |
| 3 | **categories** | Already pinned horizontal scroll (v2) — restyle done (white cards, 1px border, black CTA). Keep. |
| 4 | **bestsellers** | "Discover Top Picks" style — white cards, 1px border, image→name→price, minimal badges (already v2-styled) |
| 5 | why-rythme · brands · numbers | Keep (minimal restyle done); numbers = monochrome counters |
| 6 | new-arrivals · deals | Keep — deals banner big image (matches promo concept) |
| 7 | video-showcase · stories · testimonials · comparison · ugc · faq | Keep (already minimal); fine-tune spacing/borders in pass 2 |
| 8 | footer | Already 5-col global, dark monochrome — keep |

### Global polish pass (pass 2, after promos)
- Section vertical rhythm: `py-16/20` consistent, `max-w-[1280px]` container
- Thin 1px borders `rgba(17,17,17,.08)`, no heavy shadows
- Body bg #F4F4F4 (paper-dark) with white content cards (reference look)
- Parallax motion layer on promos (like reference `motion-effects-layer`)

### Acceptance criteria
- [ ] Hero: desktop = large banner, mobile (≤767px) = mobile portrait images, both crisp
- [ ] Promos section renders 3 blocks with Shop now → correct shop URLs
- [ ] All existing section ids intact; homepage tests green
- [ ] Build + 200+ tests green; screenshots desktop + mobile

---

## 3. Implementation status
- [x] Reference analysis (this doc §1) + report JSON in workspace
- [x] Hero dual-mode imagery (5 desktop + 5 mobile AI-generated [AI Generated])
- [ ] Promo blocks section (next batch)
- [ ] Global polish pass (next batch)
