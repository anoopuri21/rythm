# AGENT_RULES_STRICT — SOUNDSCAPE / Rythme Music Store

> **MUST be read before every task.** These rules are non-negotiable. Violations = redo.

---

## 1. Tech Stack STRICT

| Layer | Version | Notes |
|---|---|---|
| PHP | 8.4+ | `php --version` must show 8.4.x |
| Laravel | **13.24.0** | `php artisan --version` must show `Laravel Framework 13.24.0` |
| Filament | **3.3.54** | v3 ONLY — never upgrade to Filament 4/5 |
| Frontend | Blade + Tailwind 4 + Alpine.js + Vite | `npm run build` must pass before any `done` |
| Rich Text | `awcodes/filament-tiptap-editor` ^3.5 (TiptapEditor) | Filament me heading/title = **TextInput**, baki sab **TiptapEditor** |
| Media | `spatie/laravel-medialibrary` ^11.23 | |
| UI kit | `robsontenorio/mary` 2.x (existing) | |
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
