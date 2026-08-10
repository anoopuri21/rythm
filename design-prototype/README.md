# Design Prototype v2 — Cinematic Commerce Concept

Pure **HTML + CSS + Vanilla JS** homepage prototype (koi framework nahi, koi build step nahi).
Current live site (Laravel, v1 design) is **locked** at git tag `v1-homepage-locked` — ye
folder uski **design-only copy** hai: same content (logo, menu, products, sections), naya
design concept.

## ▶️ Open

Sirf `index.html` browser me kholo (double-click). Koi server/build zaroorat nahi.

## Design Concept

| | v1 (live, locked) | v2 (this prototype) |
|---|---|---|
| Layout | Section cards | **Cinematic story-telling**: numbered chapters 01–15, alternating light/dark acts |
| Motion | ScrollTrigger reveals | **Vanilla JS**: ken-burns hero, grain film overlay, marquee strips, parallax, staggered reveals, pulsing elements |
| Hero | Slider + overlay | Full-bleed slideshow, huge display type, "YOUR SOUND. YOUR STAGE." + slide counter + scroll indicator |
| Type | Poppins only | **Space Grotesk (display) + Poppins (body)** — professional editorial feel |
| Drama | Red accents | Red glow orbs, shine-sweep buttons, ring-pulse to-top, red progress bar, red marquee band |
| Products | Cards | Same Bajaao products — 1:1 images, object-contain, red hover glow, Add-to-cart chips |

## Latest — v2.1 (2026-08-10, phased improvements)

**P1 · Navbar mega menu:** main menu = `Shop ▾ · About · Blogs · Contact`; SHOP ka
container-width mega menu (full-width nahi): left = scrollable 10-category list (red pill
active, thin red scrollbar), right = selected category ke subcategories + thumbnail +
Explore CTA; background instrument line-art + white overlay (glassy).

**P2 · Explore by Category = pinned horizontal scroll:** section scroll pe screen pe pin
hoti hai (`position: sticky`), aage scroll karne pe cards **left→right** move karte hain
(JS scroll-driven translateX), saare cards ke baad unpin. Ek time pe **3 cards** (1:1
images, object-contain, clear). Card = glass (frosted blur): Image → Name → Count →
[Explore →] CTA; hover pe image scale NAHI. HUD: red progress bar + `03 / 10` counter +
bouncing "SCROLL →" hint. Reduced-motion pe normal grid fallback.

**P3 · Badge z-index:** `.pcard__badge` / `.pcard__wish` z-index 3 — badges hamesha images
ke upar (peeche nahi chhuphte).

## Files

- `index.html` — full homepage (nav 2-row, drawer, hero, marquee, categories, products
  carousel, bestsellers tabs, why, brands, numbers, arrivals, deals timer, video showcase,
  stories, testimonials, comparison, UGC, FAQ, footer CTA + wordmark, to-top)
- `css/style.css` — design system (red #d50808 / black / white, custom properties)
- `js/main.js` — data (same products/categories as live config) + all interactions:
  slider, carousel, tabs, counters, countdown, accordion, parallax, reveals, modal, drawer
- `images/` — poster, hero/story/ugc shots (AI-generated + reused from live site)

## Notes

- **No network needed** after first load of Google Fonts; images remote (Bajaao) with
  graceful ♪ fallback agar offline ho.
- Full cinematic effect in a normal browser tab (sandboxed preview me external images/fonts
  load nahi hote — file ko directly kholo).
