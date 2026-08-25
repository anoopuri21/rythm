# RYTHME Design System v3 — RED MARKETPLACE (25 August 2026, owner approved)

> Layout reference: XStore Electronic Mega Market (structure and composition only).
> Brand treatment: retain the current Rythme / Rhythm Exports logo.
> Direction: deep Rythme red accent with neutral white, soft-grey and dark surfaces; Inter typography remains unchanged.

## Approved tokens

Single source: `@theme` in `resources/css/app.css`; compatibility mirror: `tailwind.config.js`.

| Token / role | Approved value | Usage |
|---|---|---|
| `brand` / primary accent | `#B20202` | Primary CTAs, active filters, badges, links, focus and progress |
| `brand-dark` / strong accent | `#930303` | Hover/pressed states and stronger emphasis |
| `brand-soft` / soft accent surface | `#E7F4F1` | Quiet selected-state and supporting accent surfaces |
| `ink` / main text and dark footer | `#222222` | Body text, headings and dark footer/sections |
| `paper` / main surface | `#FFFFFF` | Main page and card surfaces |
| `paper-dark` / alternate surface | `#F7F7F8` | Alternating sections and low-emphasis panels |
| `border` / neutral border | `#E5E7EB` | Inputs, cards and dividers |
| `muted` | `#6B6B6B` | Secondary text |
| Font | Inter | All semantic font families |

Legacy `gold*` and `rythme-*` class names remain as compatibility aliases; they now resolve to the approved red/neutral system. New code must use semantic token names.

## Accessibility evidence

| Pair | Contrast |
|---|---:|
| White on `#B20202` | **7.24:1** |
| White on `#930303` | **9.31:1** |
| `#222222` on white | **15.91:1** |
| `#222222` on `#F7F7F8` | **14.86:1** |

All listed pairs pass WCAG AA for normal text. Red cannot be the sole indicator of status; selected, error, sale and focus states also require text, icon, border or other non-color cues.

## Style rules

- Primary buttons: `brand` with white text; hover/pressed uses `brand-dark`.
- Cards: white surface, neutral border and restrained shadow.
- Headings/body: `ink`; secondary copy: `muted`.
- Alternate sections: `paper-dark`; soft accent surfaces: `brand-soft`.
- Dark sections/footer: `ink` with white/soft-neutral content and controlled red highlights.
- Focus indicators: visible `brand` outline with sufficient adjacent contrast.
- Selection, carousel active states, progress and active filters use semantic brand tokens.
- Preserve Inter typography, current logo treatment, approved layouts and reduced-motion behavior.

## Change protocol

1. Update `resources/css/app.css` semantic tokens.
2. Mirror compatibility values in `tailwind.config.js`.
3. Update this document and Phase design evidence.
4. Replace accent-purpose hardcoded colors with semantic tokens; do not recolor neutral text/media blindly.
5. Run targeted design tests, full PHP regression and production frontend build before commit.

---

## Historical archive — superseded v2/v1 systems

# RYTHME Design System v1 — "Rythme Red"

> **Homepage theme = the design system.** Every page (shop, product, cart, wishlist, checkout, auth, admin) follows these tokens. Single source of truth:
> - Live tokens: `resources/css/app.css` → `@theme { ... }`
> - Legacy mirror: `tailwind.config.js` (compat layer, same values)
> - Created: 2026-08-13 · Applies to: whole storefront + commerce build (Phase A–G)

---

## 1. Principles

1. **Shopify-like premium, airy, editorial** — generous whitespace, large imagery, restrained chrome. NOT Amazon/Flipkart dense marketplace UI (see commerce plan §2.1).
2. **Dark/light section rhythm** from homepage: dark hero → paper sections → dark cinematic bands → paper footer sections.
3. **One font, one accent** — Poppins everywhere, Rythme Red as the single accent color.
4. **Tokens only** — no ad-hoc hex in views/components. All colors/fonts via Tailwind utilities from `@theme`/config.
5. **Motion stays cinematic** — existing GSAP/Lenis system; new components reuse `data-reveal`, `card-hover-lift`, `img-zoom-hover`.

---

## 2. Color tokens

| Token | Hex | Usage | Legacy class (existing pages) |
|---|---|---|---|
| `brand` | `#D50808` | Primary CTA, active states, links, accents | `bg-gold`, `text-gold` |
| `brand-dark` | `#A30404` | Hover, `em` accents in titles, gradient end | `bg-gold-dark` |
| `brand-light` | `#FF5252` | Kickers, secondary accents, gradient start | `bg-gold-light` |
| `brand-soft` | `#FF6B6B` | Tertiary accents, badges, borders | — |
| `ink` | `#0A0A0A` | Body text on light; dark section bg | `bg-rythme-black` |
| `ink-soft` | `#1A1A1A` | Alt dark section bg | `bg-rythme-black-soft` |
| `ink-muted` | `#2D2D2D` | Borders/cards on dark | `bg-rythme-black-muted` |
| `paper` | `#FFFDF7` | Page background (light) | `bg-rythme-cream` |
| `paper-dark` | `#F5F5F5` | Alt light section bg, table stripes | `bg-rythme-cream-dark` |
| `white` | `#FFFFFF` | Cards on dark sections | `bg-rythme-warm-white` |
| `muted` | `#6B6B6B` | Secondary text | `text-rythme-warm-gray` |

**Gradients (canonical):**
- Brand gradient: `linear-gradient(135deg, #FF5252, #D50808, #A30404)` (text-gold-gradient equivalent)
- Scroll progress: `linear-gradient(90deg, #A30404, #FF5252)`

---

## 3. Typography

- **Family: Poppins only** (400/500/600/700). No serif, no display font.
- Weights: body 400 · nav/buttons 600–700 · headings 700 (weight-driven hierarchy, no size gimmicks).

| Role | Spec |
|---|---|
| Display / section title | `clamp(2.5rem, 5vw, 4.8rem)`, line-height 1.04, `-0.025em`, weight 700; `em` = `brand-dark` |
| Section kicker | 0.7rem, uppercase, tracking 0.24em, `brand-dark`, line + text (`.section-kicker`) |
| Page h1 | Hero-scale, single h1 per page |
| h2 / card titles | 1.25–2rem, weight 600–700 |
| Body | 0.95–1.05rem, line-height 1.6–1.75, `ink` on `paper` / white on dark |
| Small / meta | 0.8rem, `muted` |

---

## 4. Spacing · Radius · Shadow

- Section padding: `py-24 sm:py-32` (light), `py-28 sm:py-36` (dark bands)
- Container: `max-w-7xl mx-auto px-5 sm:px-8 lg:px-12`
- Cards: `rounded-2xl` (light) / `rounded-3xl` (hero media), soft shadow `0 24px 60px rgba(10,10,10,.12)`
- Buttons: `rounded-full` pill; interactive target ≥ 44×44px
- Focus: `outline 2px brand` offset 3 (already global)

---

## 5. Canonical component patterns

| Component | Pattern |
|---|---|
| Section header | `section-kicker` + `section-title` (+ `data-reveal="up"`) |
| Primary button | pill `bg-brand text-white` (existing `.btn-gold-glow` = red now), hover `brand-dark` |
| Secondary button | pill outline `border-ink/20` → hover `border-brand text-brand` |
| Product card | image `img-zoom-hover`, brand small, name, price + `compare_at` strikethrough, quick-add + wishlist heart |
| Inputs | `rounded-lg border-ink/15` bg-white, focus ring brand |
| Badges | `rounded-full border brand-soft text-brand` |
| Drawer/modal | right slide-over, `backdrop-blur`, esc close, focus trap (cart drawer, video modal) |
| Dark section | `bg-ink text-white` + `data-reveal` content + parallax media |

---

## 6. Change protocol (color / font changes)

When user requests a color or font change, the agent **updates the design system itself**, in this order:

1. Edit `@theme` tokens in `resources/css/app.css` (single place).
2. Mirror same values in `tailwind.config.js`.
3. Update this doc (`02-design-system.md`) — palette/type tables.
4. Sweep: replace any hardcoded hex in `app.css`/views with tokens/utilities of the new value.
5. Verify: `npm run build` + `php artisan test` green → commit.

**Rules:** never introduce a new font family without updating the system (Poppins only today); never hardcode hex in new views; never import another brand's palette.

---

## 7. Do / Don't

| ✅ Do | ❌ Don't |
|---|---|
| Use semantic tokens (`brand`, `ink`, `paper`, `muted`) | Don't hardcode hex in views |
| Keep legacy `gold*`/`rythme*` classes on old sections working | Don't rename legacy classes (breaking change) |
| One accent color, one font | Don't add second accent/font |
| Reuse motion system (`data-reveal`, hover lifts) | Don't build heavy new animation frameworks |
