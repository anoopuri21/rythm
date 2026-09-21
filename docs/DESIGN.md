# DESIGN — Always Read

> **One of five always-read files.** Others: `ARCHITECTURE.md` · `RULES.md` · `PHASES.md` · `MEMORY.md`  
> **Live token source of truth:** `resources/css/app.css` → `@theme { ... }`  
> Mirror/compat: `tailwind.config.js` · Detail: `docs/architecture/02-design-system.md`, `docs/ui-brand-guidelines.md`

---

## 1. Brand character

- **Premium single-brand music store** — precise, confident, product-led.  
- Luxury = clarity, spacing, imagery — **not** clutter, heavy gradients, or marketplace chrome density.  
- Layout inspiration (structure only): XStore Electronic Mega Market; catalogue inspiration: bajaao.com.  
- Logo: current Rhythm Exports / Rythme mark — don’t replace casually.

---

## 2. Approved tokens (v3 — use these)

| Token | Hex | Usage |
|---|---|---|
| `brand` | `#B20202` | Primary CTAs, links, active filters, focus, badges |
| `brand-dark` | `#930303` | Hover / pressed / strong emphasis |
| `brand-light` | `#B20202` | Compat (same family) |
| `brand-soft` | `#E7F4F1` | Quiet selected/supporting surfaces |
| `ink` | `#222222` | Body text, headings, dark footer/sections |
| `ink-soft` | `#2D2D2D` | Alt dark |
| `ink-muted` | `#3A3A3A` | Borders/cards on dark |
| `paper` | `#FFFFFF` | Main surface |
| `paper-dark` | `#F7F7F8` | Alternate sections / panels |
| `muted` | `#6B6B6B` | Secondary text |
| `border` | `#E5E7EB` | Inputs, cards, dividers |
| `white` | `#FFFFFF` | Explicit white |

**Tailwind:** prefer semantic utilities — `bg-brand`, `text-ink`, `bg-paper`, `border-border`, `text-muted`, etc.

**Legacy aliases** (`gold*`, `rythme-*`) still resolve to the red/neutral system for old blades — **new code must use semantic names**, not gold.

---

## 3. Typography

| Rule | Value |
|---|---|
| Intended family | **Inter** in `@theme` (`--font-sans: "Inter", …`) |
| Compat note | Some docs/tailwind mirrors still mention Poppins; **follow live `app.css` @theme** and ensure font link matches tokens when touching base layout |
| Hierarchy | Weight-driven (400 body · 600–700 UI/headings) — avoid random display fonts |
| Hero / page H1 | Single H1 per page; tight line-height; balanced wrap |
| Section H2 | Strong weight; restrained tracking |
| Body | 400–500, line-height ~1.6–1.7 |
| Meta / labels | 500–700; uppercase only for short badges/nav meta |
| Min body | Avoid &lt; 12px; controls ~12–14px labels |

Legacy `font-playfair` / `font-inter` / `font-bebas` utilities are compatibility aliases — don’t introduce a second real family without a design-system change.

---

## 4. Layout & shape

| Element | Pattern |
|---|---|
| Page | Airy, editorial, large product imagery |
| Container | `max-w-7xl` style width + horizontal padding rhythm already in layouts |
| Sections | Alternate `paper` / `paper-dark`; dark bands use `ink` + light text |
| Cards | White surface, neutral border, restrained shadow, `rounded-2xl` (or existing card classes) |
| Buttons | Pill / rounded-full; primary = `bg-brand text-white` hover `brand-dark`; secondary = outline |
| Inputs | Neutral border, white fill, brand focus ring |
| Badges | Soft border/brand text; never red-only status |
| Drawers/modals | Backdrop, Esc close, focus trap (cart drawer pattern) |
| Touch | **≥ 44×44px** targets; sticky mobile commerce CTAs where already patterned |

---

## 5. Motion

- Reuse existing system: GSAP ScrollTrigger, Lenis, Swiper, CountUp, `data-reveal`, hover lifts.  
- **Respect `prefers-reduced-motion`.**  
- Don’t add a new animation framework.  
- Motion is enhancement — content and checkout must work without it.

---

## 6. Component patterns (storefront)

| UI | Expectation |
|---|---|
| Product card | Image zoom hover, manufacturer brand, name, price + compare-at strike, quick-add, wishlist heart |
| Price box | Selling price, MRP strike, % off when valid, stock truth, EMI/shipping lines only if configured/true |
| Shop | Filter sidebar/accordion + sort pills + grid + pagination + active chips |
| Cart | Drawer (quick) + full page; server totals; clear checkout CTA |
| Checkout | 2-step wizard UI; policy links; no fake trust badges |
| Navbar | Category drawer/mega; cart + wishlist badges |
| Footer | Brand + nav columns + newsletter; don’t ad-hoc redesign locked structure without a task |

Admin (Filament) follows Filament theme; don’t force storefront chrome into admin.

---

## 7. Accessibility (non-negotiable)

- Contrast: white on `#B20202` ~7.2:1; ink on white ~15.9:1 (AA OK for listed pairs).  
- **Red is never the sole status cue** — add text/icon/border.  
- Visible focus ring using brand.  
- Keyboard: drawers/menus operable; focus not lost.  
- Semantic landmarks/headings; meaningful `alt` on images.  
- Don’t rely on color-only sale/error/success.

---

## 8. SEO / content presentation

- Unique `<title>` + meta description per page type.  
- One `h1`.  
- Product/FAQ JSON-LD where already implemented — keep truthful.  
- Canonical shop/product URLs; soft-deleted products don’t stay indexable as live.

---

## 9. Do / Don’t

| Do | Don’t |
|---|---|
| Use semantic tokens | Hardcode new hex in blades/JS |
| Match existing card/button/spacing rhythm | Invent dense Amazon-style chrome |
| Keep manufacturer brand as small label | Imply multi-seller storefront |
| Lazy-load non-critical images | Ship huge unoptimized heroes without need |
| Update tokens in `app.css` first on brand change | Recolor random hex scattered in views |
| Keep reduced-motion paths | Trap scroll/focus without escape |

---

## 10. Change protocol (color/font)

1. Edit `@theme` in `resources/css/app.css`.  
2. Mirror compat values in `tailwind.config.js` if still required.  
3. Update **this file** + `02-design-system.md`.  
4. Replace accent-purpose hardcoded colors carefully (not neutral media).  
5. `npm run build` + relevant tests.  
6. Note the change in `MEMORY.md`.

---

## 11. File map

```
resources/css/app.css          ← @theme tokens + global primitives
resources/css/theme.css        ← imported theme helpers (if present)
tailwind.config.js             ← content paths + legacy color/font mirrors
resources/views/layouts/       ← shell, fonts, global chrome
resources/views/components/    ← product-card, navbar, ui/*
resources/js/modules/          ← motion, carousels, ui
public/images|videos/          ← static media
```

---

*Design questions → this file first. Only open long design-system docs for historical variants or pixel evidence tasks.*
