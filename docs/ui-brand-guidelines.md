# Rythme Premium UI Brand Guidelines

**Version:** 1.0  
**Date:** 29 August 2026  
**Scope:** Visual refinement only; existing layout structure and responsive grids remain authoritative.

## Brand character

Rythme should feel precise, confident and premium rather than ornamental. Use strong musical-product imagery, disciplined spacing, deep red emphasis, quiet neutral surfaces and restrained motion. Luxury comes from consistency and clarity—not heavy gradients, oversized shadows or decorative clutter.

## Source of truth

- CSS tokens and primitives: `resources/css/theme.css`.
- Tailwind semantic aliases and existing layout rules: `resources/css/app.css`.
- Reusable Blade primitives: `resources/views/components/ui/`.
- Approved palette remains:
  - Primary red `#b20202`
  - Strong/hover red `#930303`
  - Soft accent `#e7f4f1`
  - Main ink/footer `#222222`
  - Surface `#ffffff`
  - Alternate surface `#f7f7f8`
  - Border `#e5e7eb`

New views must use semantic tokens/classes; do not add arbitrary hex colors or one-off shadows.

## Typography

Poppins is the single storefront family with system fallbacks. This avoids competing display/body personalities while retaining the current hierarchy and line wrapping.

- Hero/page H1: 700–800, tight line height, balanced wrapping.
- Section H2: 650–800, heading line height and restrained negative tracking.
- Card H3: 600–700, no more than two lines where cards require equal rhythm.
- Body: 400–500, `1.6–1.7` line height.
- Labels/metadata: 500–700; uppercase only for short badges or navigation metadata.
- Avoid body text below 12px. Interactive controls should generally use 12–14px labels and a 44px minimum target.

Legacy `font-playfair` and `font-inter` utilities intentionally resolve to Poppins so page structure is preserved during migration.

## Spacing and shape

Use the `--ry-space-*` scale. Prefer 4, 8, 12, 16, 24, 32, 40, 48 and 64px rhythms. Standard cards use 16px radius; nested media uses 12px. Pill radii are reserved for buttons, compact badges and filters—not large content containers.

## Depth and effects

- Default product surfaces use a faint border and no resting shadow.
- Interactive cards may lift up to 3px and use `--ry-shadow-md` only on hover or keyboard focus.
- Primary CTAs may use the red-to-strong-red gradient.
- Do not add continuous decorative animation.
- Respect `prefers-reduced-motion` and preserve keyboard focus.

## Buttons

Use `<x-ui.button>`:

```blade
<x-ui.button href="{{ route('shop.index') }}">Browse instruments</x-ui.button>
<x-ui.button type="submit" variant="dark" size="lg">Continue</x-ui.button>
<x-ui.button variant="secondary" :disabled="$busy">Cancel</x-ui.button>
```

Variants: `primary`, `dark`, `secondary`, `ghost`, `danger`. Sizes: `sm`, default `md`, `lg`. Use `block` for full-width form CTAs. One action group should have one clear primary action.

## Cards and product tiles

Use `.ui-card`; add `.ui-card--interactive` only when the card contains a meaningful destination/action. Product media is always square (`1:1`) with `object-fit: contain`; banners use `16:7` with `cover`. Existing `.pcard`, `.mcard` and shop tiles inherit the shared border/shadow language without changing their grids.

Product cards should preserve this order: image, truthful badge, category/brand, product name, optional verified rating, stock truth and price. Show compare-at pricing only when it is genuinely above the current price.

## Badges and alerts

Use `<x-ui.badge variant="…">` and `<x-ui.alert variant="…">`. Standard variants are `neutral`, `brand`, `success`, `warning` and `danger`. Premium catalogue variants are `new`, `best-seller` and `limited`; use them only when backed by a real product flag or curated merchandising assignment. Never infer “Limited” from low or unavailable stock. Color never replaces text. Errors use `role="alert"`; informational outcomes use polite status semantics.

## Forms

Use `<x-ui.input>` for new text-like inputs. Every field requires a visible label unless an equivalent accessible label is explicitly provided. Keep help or error text directly attached with `aria-describedby`; invalid controls set `aria-invalid`. Global form treatment now standardizes borders, radius, focus ring and type without changing layout.

## Empty/loading states

Use `<x-ui.empty-state>` with an icon slot, direct title, short explanation and one recovery action. Use `<x-ui.skeleton>` only while content is genuinely loading; match the final element dimensions to avoid layout shift. Never leave skeletons running after an error. Reduced-motion users receive a static placeholder.

## Images

- Product card: `1:1`, contain, useful alt text.
- Category tile: `1:1`, cover only when composition tolerates crop.
- Banner: `16:7`, cover, keep text outside critical crop regions.
- Always provide dimensions to reduce layout shift.
- Use local managed media; no source hotlinks.
- Do not upscale poor images or disguise missing media with misleading product art.

## CTA writing

Use short verb-led labels: “View product”, “Add to cart”, “Continue to payment”, “Save address”. Avoid multiple exclamation marks, artificial urgency or unsupported scarcity. Hover motion is subtle; actions must remain obvious without hover on touch devices.

## Migration rule

Do not restructure page grids merely to adopt these primitives. New work uses UI components immediately. Existing high-reuse surfaces migrate first: shared product cards, auth/forms, account states, cart/checkout, then long-tail CMS views. A phase is accepted only after build, responsive, accessibility and full regression evidence confirms visual preservation.
