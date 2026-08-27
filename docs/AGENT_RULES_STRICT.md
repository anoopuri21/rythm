# AGENT_RULES_STRICT — LEGACY / NON-AUTHORITATIVE

> **ARCHIVED:** This file contains superseded branch, Filament, palette and workflow rules and must not drive current work or automation. Current authority is `tasks/MASTER_PROJECT_TRACKER.md`, `tasks/CANONICAL_PHASE_SEQUENCE.md`, approved phase contracts, `tasks/AUTO_MODE_PROTOCOL.md`, and `tasks/AUTONOMOUS_SUPERVISOR_REQUIREMENTS.md`. It is retained only as historical context until a bounded rule migration is completed.

---

## 1. Tech Stack STRICT

| Layer | Version | Notes |
|---|---|---|
| PHP | 8.3.30+ (lock resolved for 8.3.30; sandbox runs 8.4) | `php --version` must show 8.3.30+ |
| Laravel | **13.24.0** | `php artisan --version` must show `Laravel Framework 13.24.0` |
| Filament | **3.3.54** | v3 ONLY — never upgrade to Filament 4/5 |
| Frontend | Blade + Tailwind 4 + Alpine.js + Vite | `npm run build` must pass before any `done` |
| Rich Text | `awcodes/filament-tiptap-editor` ^3.5 (TiptapEditor) | Filament me heading/title = **TextInput**, baki sab **TiptapEditor** |
| Media | `spatie/laravel-medialibrary` ^11.23 | |
| UI kit | `robsontenorio/mary` 2.x (existing) | |
| Design | **RED #d50808 · BLACK #000000 · WHITE #ffffff only** — NO gold/yellow | |
| Font | **Poppins** (sans-serif) only | |
| Other | Livewire 3.8.3, GSAP + ScrollTrigger, Lenis, Swiper, CountUp | |

**NEVER** use Next.js / React / Vue / jQuery. `next.config.js` must NOT exist — verify:
```bash
ls next.config.js   # → "No such file"
php artisan --version   # → Laravel Framework 13.24.0
```

## 2. Images — ONLY 3 allowed sources

1. **Bajaao / Amazon** — product images only (e-commerce product shots).
2. **Unsplash / Pexels** — free-license UI images (sections, cards, lifestyle). MUST add an HTML comment with the license + source.
3. **AI Generated** — hero/banner imagery only. Prompt explicitly + `alt="… — AI Generated"` + HTML comment `[AI Generated]`.

No other sources (no random web hotlinks, no copyrighted images).

## 3. Content

- **SEO friendly**: unique `<title>`, meta description, semantic headings (single `h1` per page), keyword-rich natural copy.
- **Unique copy**: NEVER verbatim-copy from Bajaao/Amazon/anywhere. Rewrite in own words.
- **AI keywords natural**: use keywords (guitars, keyboards, pro audio, free shipping, EMI, warranty…) naturally in prose, not stuffed.
- Schema markup for FAQ/Product where relevant (JSON-LD).

## 4. Filament Conventions

- Heading / Title / Section name fields → `TextInput`.
- Long-form / body / description / content → `TiptapEditor` (awcodes) — NOT Textarea.
- Images in admin → Spatie MediaLibrary (`Forms\Components\SpatieMediaLibraryFileUpload`).
- Admin sidebar: multi-group (see architecture doc).

## 5. Workflow (task-driven, God Mode)

1. Read `tasks/tasks.json` — it is the **source of truth**.
2. Pick current `pending`/`in_progress` task. Plan in chunks (logs/task-plan-*.md when task mode ON).
3. Build chunk → **test after every section**: `npm run build` AND `php artisan test` must pass.
4. Only then: mark task `done` in tasks.json + commit (git).
5. **Never repeat `done` tasks.** Only rework `pending`/`in_progress` with "improvement" notes — without breaking UI.
6. **LOCKED features — never touch**: Header, Footer, Cart, Wishlist, Checkout/Payment. (Footer redesign is queued as its own future task, not ad-hoc edits.)

## 6. Git Workflow

- Branch: `arena/019fe1bf-rythm` (this session / until task mode re-enabled).
- When task mode is ON: per-task branches `task/<id>` → merge back.
- Commit per completed chunk with clear messages; push after each task.
- Never commit to `main`.
- Git identity: `auto-agent <auto-agent@soundscape.local>`.

## 7. Verification checklist before marking done

- [ ] `php artisan --version` → 13.24.0
- [ ] `npm run build` passes
- [ ] `php artisan test` passes (all)
- [ ] Homepage smoke test: HTTP 200 + all section IDs present
- [ ] `next.config.js` does not exist
- [ ] Image rules respected (comments + alt)
- [ ] Content unique, SEO-friendly

## 8. Workflow — Phased Execution (MANDATORY, user instruction 2026-08-10)

- **Har change ko phases me divide karo** (design → implement → test → commit per phase).
  Chhote phases = kam galtiyan. Ek phase pass hone ke baad hi agla phase.
- **Design/prototype changes me pehle PLAN banao, user approve kare, tabhi code.**
  (Plan me: exact UI architecture, files, interactions, phase breakdown.)
- **User kabhi bhi kisi rule me change karwata hai → turant khud `AGENT_RULES_STRICT.md`
  (aur related docs) edit kar lo** — dobara batane ka wait nahi.
- Prototype (`design-prototype/`) changes sirf us folder me — live Laravel site untouched
  jab tak user porting approve na kare.
- Har phase ke baad: JS `node --check` (prototype) / `npm run build` + `php artisan test`
  (Laravel) → commit → phir agla phase.

## 9. User Design Preferences (accumulated)

- Red #d50808 / Black / White only. Poppins base; prototype display font Space Grotesk.
- Elegant + cinematic + aligned; white empty backgrounds avoid karo (decor/add-ons use karo).
- Product images: real Bajaao products, 1:1, object-fit contain (kabhi cut nahi).
- Hover pe image scale avoid karo jahan cards me content ho — glass/frost effects prefer.
- Badges/CTAs hamesha images ke UPAR (z-index), peeche kabhi nahi.
- Navbar: 2-row, white, sticky in-flow; mega menu container-width (full-width nahi).
- Naye design ideas prototype me pehle, approval ke baad Laravel me port.

---

## 8. Design System STRICT

- **Homepage theme = the site-wide design system** ("Rythme Red"). Source of truth: `@theme` tokens in `resources/css/app.css` + `tailwind.config.js` + `docs/architecture/02-design-system.md`.
- Every new page/section (shop, product, cart, wishlist, checkout, auth) MUST use design-system tokens/utilities (`bg-brand`, `text-ink`, `bg-paper`, `font-sans`, `section-title`, `section-kicker`) — NEVER ad-hoc hex colors or new fonts.
- **User color/font change → agent updates the design system ITSELF**: tokens → tailwind.config.js → 02-design-system.md → sweep hardcoded hex → `npm run build` + `php artisan test` green → commit. No user follow-up needed.
- Fonts: **Poppins only**. No new families without a design-system update.
- Legacy `gold*`/`rythme*` class names are valid aliases (same values) — do not rename them (breaking change).

---

## 9. Enterprise Rules (user directive 2026-08-13 — ALL future tasks)

> Verified against installed Laravel 13.24.0 (2026-08-13). Where the framework
> does not support a rule literally, the ADJUSTED form below is binding.

### 9.1 Code style (binding, all new PHP files)
- `declare(strict_types=1);` as the FIRST line of every new PHP file.
- Strict scalar/union/intersection type hints on params + returns everywhere;
  `readonly` classes/properties for immutable data (DTOs).
- **Model attributes over legacy properties** — verified supported in 13.24:
  `#[Table('products')]`, `#[Fillable([...])]`, `#[Hidden([...])]`,
  `#[Guarded([...])]`, `#[Visible([...])]`. Do NOT declare `$fillable`,
  `$hidden`, `$table` properties on new models.
- ADJUSTED: **no `#[Casts]` attribute exists** in 13.24 → keep `$casts`
  property on models (verified: no Casts attribute in Eloquent\Attributes).
- Controllers ≤ 30 lines: business logic lives in single-responsibility
  Services/Actions; DTOs between controller ↔ service; custom `FormRequest`
  classes with `$request->validated()` — never inline validation.
- **Zero placeholders**: no `// TODO`, no incomplete stubs.

### 9.2 Security (binding)
- CSRF: Laravel 13's origin-aware `PreventRequestForgery` is the default
  middleware (verified) — do not downgrade to legacy token-only checks;
  use `PreventRequestForgery::except()` only for verified webhook routes.
- Payment webhooks: cryptographic signature verification ALWAYS before
  acting on payloads (Razorpay HMAC plan — commerce arch §5).
- Queries: Eloquent builders only; raw SQL only with bound parameters.
- Passkeys/WebAuthn (Fortify): NOT installed — future task, requires user
  decision before adding the dependency. Do not add silently.

### 9.3 Performance (binding)
- Eager loading (`with()`) or lazy-eager (`load()`) on every relational
  query; prevent N+1.
- `Cache::touch()` (verified) to extend TTL of cached product state —
  never rewrite whole cache blocks.
- Every migration FK/slug/SKU/status column gets an index.
- Non-blocking workflows (order emails, invoices, shipping webhooks) →
  queued jobs routed via `Queue::route()` (verified).

### 9.4 UI/UX (binding)
- Mobile-first responsive Tailwind; dark sections follow design system.
- Zero-refresh interactions (mini-cart, filters, qty) via Livewire 3.
- Network feedback: `wire:loading` states, skeleton UI, disabled submit
  during payment.

### 9.5 E-commerce intelligence (conditional — needs infra/user decision)
- Laravel AI SDK (`laravel/ai`): NOT installed; add when recommendations/
  support/search task begins (user approval).
- Semantic search `whereVectorSimilarTo()`: verified present in 13.24
  Builder, but requires **PostgreSQL + pgvector** — project DB is SQLite
  (dev) / MySQL (prod plan). Decision needed: Postgres migration or
  keyword search fallback. Do not implement on SQLite.

### 9.6 Testing (binding)
- PHPUnit suite for every Action/endpoint/component (project uses PHPUnit —
  keep PHPUnit, not Pest) covering success + exception boundaries.
- Existing gates stay: `npm run build` + `php artisan test` green before
  marking any task done.

---

## 10. Git Workflow STRICT — Single Branch, Single PR (user directive 2026-08-13)

- **ONE long-lived branch: `feature/dev`.** All development, every task, every commit lands here.
- **ONE open PR: #22 (`feature/dev` → `main`)** — it auto-updates on every push. User reviews & merges ONCE.
- **NEVER** create a new branch (`task/<id>`, `feature/<x>`, etc.) and NEVER open a new PR.
- Never commit to `main` directly. Push only to `feature/dev`.
- Stale/merged branches are deleted immediately when noticed.
- Task agent (`automation/task-agent.mjs`) enforces: branch check before any commit.

## 11. Security Mandate — TOP-NOTCH (user directive 2026-08-13)

> This is an e-commerce platform. **Security is a first-class, non-negotiable concern** in EVERY line of code and architecture decision — never an afterthought.

1. **OWASP Top 10 aware** — every feature considered against: broken access control, injection, XSS, insecure design, misconfig, vulnerable components, auth failures, crypto failures, SSRF, logging issues.
2. **Input**: every input via FormRequest validation; never trust client data (prices, totals, ids, ownership).
3. **Output**: Blade auto-escaping; no raw `{!! !!}` unless trusted Tiptap content sanitized; CSP headers where feasible.
4. **AuthZ**: ownership checks (Policies or inline `user_id` guards) on every resource — orders, wishlist, addresses. Signed URLs for sensitive pages.
5. **Payments**: Razorpay callback + webhook ALWAYS cryptographically verified (signature/HMAC) before state change; amount-match checks; CSRF-excepted routes verified server-side only.
6. **SQLi**: Eloquent/Query-Builder only; raw SQL only with bound params and zero user input.
7. **Mass assignment**: model attributes (`#[Fillable]`) strictly; no `*` guard.
8. **Secrets**: env-only; never commit keys; `.env` gitignored.
9. **Rate limiting** on all auth + form POST routes (login, register, contact, newsletter, cart, checkout).
10. **Dependencies**: `composer audit` + `npm audit` checked in CI; no known-vulnerable packages.
11. **Session/cookies**: secure cookie flags in production; session regeneration on login.
12. **Security review task is QUEUED** (`security-review` in tasks.json) — full audit after site completion; until then every new code passes the checklist above.
