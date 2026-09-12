# Plan — PDP tabs (Reviews + description toggle) & Track-order placement

## Goals
1. **Customer reviews** join **Description** and **Specifications** as a third tab on the product detail page.
2. Long **Description** uses **Show more / Show less** (no permanent wall of text).
3. **Track an order** must not appear on the product page; public chrome must not advertise tracking without an order context. Tracking UI stays on **order detail** (and optional account entry only if the customer already has a confirmed order).

## Non-goals
- Do not change review eligibility, moderation, or Filament Review admin.
- Do not remove `/track-order` route (guest lookup still works if bookmarked); only **links that surface it**.
- Do not change checkout, cart, payment, or Q&A (already removed).

## Implementation

### A. PDP tabs (`resources/views/product/show.blade.php`)
- Tablist: `description` | `specs` | `reviews`.
- Default tab: `description` if body exists, else `specs`, else `reviews`.
- Deep-link: buy-box rating link stays `#customer-reviews`; Alpine `init` selects `reviews` when `location.hash === '#customer-reviews'`.
- Move `<livewire:review-section>` **into** the reviews tab panel (remove duplicate block below tabs).
- Keep FAQ / related sections below the tab card (unchanged).

### B. Description show more / less
- Alpine on description panel: measure content; if taller than ~12rem (or char/html long), clamp with `max-height` + fade; toggle button **Show more** / **Show less**.
- Short descriptions: no toggle.
- Accessible: `aria-expanded` on the control.

### C. Track an order links
| Location | Action |
|---|---|
| PDP policy nav | **Remove** “Track an order” |
| Footer Help | **Remove** “Track your order” (no unsolicited track CTA) |
| Account support card | Show **only if** customer has ≥1 order with status in confirmed+ pipeline (`confirmed`, `processing`, `shipped`, `delivered` or paid-equivalent) — else hide. Prefer link to **account orders** / first order show, not blank lookup, when possible |
| `orders/show` | Keep existing Tracking timeline / carrier link (order detail only) |
| `orders/lookup` | Route remains; not linked from PDP/footer |

### D. Tests
- `ProductPageTest`: assert tabs labels; assert **dontSee** Track an order on PDP; assert Customer reviews tab / `#customer-reviews`.
- Optional: assert description toggle markup when long description seeded product exists.

### E. Verify
- Manual: open PDP → three tabs; long desc toggles; no Track on PDP/footer.
- Order show still has Tracking section.
- Reviews submit still works inside tab.

## Risk
- Livewire inside `x-show` tab: keep panel in DOM (`x-show` not `x-if`) so Livewire roots stay mounted; use `x-cloak` on inactive panels.
