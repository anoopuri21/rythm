# NEXT SESSION CONTEXT — SOUNDSCAPE Project Continue (FINAL — 2026-08-09)

> Read this FIRST. God Mode ON. Isi point se continue karna hai — bilkul zero-to-hero setup guide.

---

## God Mode ON — SOUNDSCAPE Project Continue

**Workspace:** `/home/user/rythm` (repo `anoopuri21/rythm` — **PUBLIC**)
**Current base:** **`main`** (PR #1 MERGED — full Laravel 13 project main me hai) · HEAD main = `843731b`
**Legacy:** `arena/019fe1bf-rythm` (merged; sirf PR #2 doc-refresh uspe hai — merge/close kar dena)
**Naya kaam:** main se `task/<id>` branch bana ke (automation task mode ON hote hi system khud karega)

## ✅ Current state (verified 2026-08-09)

- **PR #1 MERGED** (`843731b`): full project main me — Laravel 13.24.0, homepage 10+5 sections, docs, tests
- **PR #2 OPEN** (optional, 1 file): `docs/NEXT_SESSION_CONTEXT.md` refresh → main. Merge kar do to doc main me bhi aa jayega.
- **Automation: OFF confirmed** — `task_mode: false`, koi process/crontab nahi, `automation/`+`logs/`+`.github/workflows/` exist nahi karte
- **Tests:** 7/7 passing (25 assertions) · `php artisan --version` = 13.24.0
- **Next task: `admin-homepage-filament`** (pending)

## ⚠️ CRITICAL — Local System (GitHub repo ke ALAAVA) har naye session me dobara setup karna hoga!

Workspace snapshot sirf `/home/user` persist karta hai. **System packages + tools (repo ke bahar) WIPE ho jate hain:**

| Tool | Kahan tha | New session me |
|---|---|---|
| PHP 8.4 + extensions (intl, mbstring, xml, curl, zip, gd, sqlite3, bcmath, dom, tokenizer, xmlwriter, simplexml) | `/usr/bin` (apt) | ❌ wipe → **reinstall (step 1)** |
| Composer 2.10.2 | `/usr/local/bin/composer` | ❌ wipe → **reinstall (step 2)** |
| Node 20 + npm | system | ✅ present (only Node survives) |
| `vendor/` | **`/tmp/vendor`** (workspace me sirf symlink) | ❌ `/tmp` bhi wipe → `mkdir -p /tmp/vendor` + symlink (step 4) |
| `node_modules/` + `public/build/` | project | ❌ excluded → `npm install && npm run build` |
| `.env` (secrets) | project | ❌ gitignored → recreate |
| `database/database.sqlite` | project | ❌ gitignored → recreate |
| `.git/config` (remote URL) | project | ❌ reset → `git remote add origin` |
| git identity (auto-agent) | global config | ❌ reset → set again |

**Ye steps har naye session me FIRST run karne hain (poora block):**

```bash
# ── STEP 1: PHP 8.4 + extensions (Debian 13 — apt me 8.4 native) ──
sudo apt-get update -qq
sudo DEBIAN_FRONTEND=noninteractive apt-get install -y -qq \
  php8.4-cli php8.4-mbstring php8.4-xml php8.4-curl php8.4-zip php8.4-gd \
  php8.4-sqlite3 php8.4-bcmath php8.4-intl php8.4-dom php8.4-tokenizer \
  php8.4-xmlwriter php8.4-simplexml unzip
php -m | grep -iE "intl|mbstring|xml|curl|zip|gd|sqlite|bcmath"   # verify

# ── STEP 2: Composer 2.10.2 (if missing) ──
curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
sudo php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer --quiet
composer --version

# ── STEP 3: Repo (PUBLIC — bina token) ──
git clone https://github.com/anoopuri21/rythm.git /home/user/rythm   # agar workspace empty
cd /home/user/rythm
git fetch origin --quiet
git checkout -f main origin/main       # PR #2 merged ho to; warna arena branch se doc le lena
git checkout -f -B arena/019fe1bf-rythm origin/arena/019fe1bf-rythm   # PR #2 unmerged → doc yahan se

# ── STEP 4: Dependencies (VENDOR = /tmp + symlink — workspace fix 2026-08-09) ──
# Har naye session me /tmp bhi khali hota hai:
mkdir -p /tmp/vendor
ln -sfn /tmp/vendor vendor                    # agar vendor symlink missing ho (cwd = /home/user/rythm)
composer install --no-interaction --no-progress   # ⚠️ HAMESHA /home/user/rythm se — composer $baseDir ABSOLUTE embed karta hai
npm install --no-audit --no-fund
npm run build
# ⚠️ TESTS: symlinked vendor se inferBasePath() /tmp ban jata hai — FIX COMMITTED
# (tests/TestCase.php me $_ENV['APP_BASE_PATH'] = dirname(__DIR__); portable — Windows/Linux/sandbox sab pe)
# Sirf `php artisan test` chalao — koi extra env var nahi chahiye.

# ── STEP 5: .env (local sqlite) ──
cp .env.example .env
# Edit: DB_CONNECTION=sqlite | DB_DATABASE=/home/user/rythm/database/database.sqlite
#        SESSION_DRIVER=file | CACHE_STORE=file | QUEUE_CONNECTION=sync
touch database/database.sqlite
php artisan key:generate
php artisan migrate --force

# ── STEP 6: Git identity + remote (config wipe ho jata hai) ──
git config --global user.name  "auto-agent"
git config --global user.email "auto-agent@soundscape.local"
git remote add origin https://github.com/anoopuri21/rythm.git

# ── STEP 7: Verify everything ──
php artisan --version        # → Laravel Framework 13.24.0
php artisan test              # → 7 passed (25 assertions)
ls next.config.js             # → must NOT exist
```

## ⚠️ Git quirk (is session me 4× confirm — HAR new session me expect karo)

Snapshot `.git` ko purani state pe restore karta hai → HEAD `main` pe purana commit, remote empty, sab kuch "uncommitted" lagega. **Code GitHub pe safe hai — blind re-commit mat karna.**
Fix: `git remote add origin …` → `git fetch origin --quiet` → `git checkout -f -B <branch> origin/<branch>` → `git status -sb` (clean dikhna chahiye).

**Push auth:** repo public (clone free). Push ke liye PAT/SSH — purana PAT (`ghp_…2Eic`) last check pe valid tha; revoke ho chuka ho to user se naya mangna.

## Tech Stack STRICT

Laravel 13 (PHP 8.3.30+ — lock resolved for 8.3.30; sandbox runs 8.4) + Blade + Filament **v3.3.54** + Tailwind 4 + Alpine.js + TiptapEditor 3.5.16 + Spatie MediaLibrary 11.23.4 + Livewire 3.8.3 + Mary 2.9.9 + GSAP/Lenis/Swiper/CountUp. **Next.js bilkul nahi.** Filament 3.3.54 = ONLY 3.3.x supporting illuminate ^13 — pinned rakhna (`^3.2` resolve hota hai usi pe).

## Strict Rules — `docs/AGENT_RULES_STRICT.md` (MUST read before every task)

- Tech: Laravel 13 + Filament v3 ONLY
- Images: ONLY Bajaao/Amazon (products) · Unsplash/Pexels free license (UI, license comment ke saath) · AI Generated (hero/banner, alt + `[AI Generated]` comment)
- Content: SEO friendly, unique (no verbatim Bajaao), natural keywords · Filament heading/title = **TextInput**, baki sab **TiptapEditor**
- Workflow: tasks.json source of truth → chunk plan → build → `npm run build` + `php artisan test` pass → done + commit
- LOCKED (kabhi touch nahi): Header, Footer, Cart, Wishlist, Checkout/Payment

## Project Docs

- `docs/architecture/00-project-architecture-overview.md` — Amazon drawer menu + 5-column footer target, homepage 10+5 cinematic sections, admin multi-group sidebar, flow: Home → Admin Home → Shop → Detail → Cart → Checkout → Payment → Wishlist → About/Contact
- `config/rythme.php` — s11 video URL (Pexels CC0, override `RYTHME_VIDEO_URL`)
- `plan.md` — phases 1–14 ✅

## Task System (`tasks/tasks.json` — source of truth)

| Task | Status |
|---|---|
| laravel-13-upgrade | ✅ done |
| soundscape-docs | ✅ done |
| page-home-10-sections (hero…footer) | ✅ done (10/10) |
| homepage-cinematic-v2 (s11 video, s12 testimonials, s13 comparison, s14 UGC, s15 FAQ, s16 scroll FX) | ✅ done (6/6) |
| **admin-homepage-filament** (5 sections, TiptapEditor, multi-group sidebar) | ⏳ **pending — NEXT** |
| shop-list (+ Amazon drawer menu) | pending |
| product-detail | pending |
| cart-checkout-payment (Razorpay test) | pending |
| wishlist-about-contact | pending |
| automation-system | pending (recreate + task mode ON, user approval pe) |
| footer-5-column | pending (footer locked until this task) |

## Automation System (recreate ONLY jab user task mode ON bole — same rules)

- `automation/config.json` (interval 30; health: `php artisan --version && php artisan config:clear`)
- `automation/task-agent.mjs` (reads tasks.json → chunk design → test → commit → update tasks.json)
- task-loop: `bash -c 'while true; do node automation/task-agent.mjs --once; sleep 1800; done'` (30m)
- `.github/workflows/auto-dev.yml` (every 2h + push)
- `logs/task-agent.log`, `logs/task-plan-*.md`, `logs/task-report-*.md`

## Learnings (2026-08-09)

1. **Filament 3.3.54** = only 3.3.x with illuminate ^13 — pinned rakhna
2. **Cloudinary removed** — cloudinary-labs 3.0.2 needs illuminate ^11|^12 only; media pipeline = Spatie MediaLibrary
3. **Laravel 13 Blade gotcha:** `@context` in JSON-LD is a Blade directive → PHP array + `json_encode`
4. **s11 video:** Pexels CC0 — `https://videos.pexels.com/video-files/854924/854924-hd_1920_1080_25fps.mp4` (`RYTHME_VIDEO_URL` override)
5. **Homepage order:** hero → categories → bestsellers → why-rythme → brands → numbers → new-arrivals → deals → video-showcase → stories → testimonials → comparison → ugc → faq → footer
6. **Images:** `public/images/video-showcase-poster.jpg` + `public/images/ugc/{studio-vocalist,guitar-corner,dj-desk}.jpg` — AI Generated, labeled
7. **PR #1 MERGED** — main = base; workflow: `git checkout -b task/<id> origin/main`
8. **Automation OFF** — task_mode: false; user explicitly off rakhna chahta hai abhi ke liye
9. **Workspace fix (vendor → /tmp + symlink):** vendor (52–136MB) snapshot se bahar; workspace me sirf 11-byte symlink `vendor -> /tmp/vendor`. Do quirks resolve kiye:
   - Composer 2.10 `autoload_classmap.php` me `$baseDir = '/home/user/rythm'` **ABSOLUTE embed** karta hai → install HAMESHA `/home/user/rythm` cwd se chalana
   - Laravel 13 `Testing\TestCase::createApplication()` → `Application::inferBasePath()` → `ClassLoader::getRegisteredLoaders()` se derive → vendor /tmp me → `/tmp/bootstrap/app.php` fail. **Fix (COMMITTED):** `tests/TestCase.php` me `setUp()` me `$_ENV['APP_BASE_PATH'] = dirname(__DIR__);` — portable (har platform pe chalta hai; phpunit.xml me kuch hardcoded nahi). Windows/local pe bhi safe.
10. **PHP 8.3 compat (2026-08-09):** User ke Windows Laragon pe PHP 8.3.30 hai — purana lock (Symfony 8.1, >=8.4.1) fail karta tha. **Fix:** composer.json `"php": "^8.3"` + `config.platform.php = 8.3.30` + `composer update -W` → Symfony 8.1→7.4 (sab PHP 8.2+). Laravel 13.24.0 intact, tests 7/7. User ko sirf `git pull` + `composer install` karna hai.
11. **Environment reset DEEP hota hai:** sirf `.git` nahi — **working files bhi purani snapshot state pe restore ho sakti hain** (blade files gayab ho gayi thi → sections silently missing). Fix: `git checkout -f -B <branch> origin/<branch>` + `php artisan view:clear`

## Resume commands

```bash
git status && cat tasks/tasks.json
# jab user task mode ON bole:
node automation/task-agent.mjs --once
# ya loop: bash -c 'while true; do node automation/task-agent.mjs --once; sleep 1800; done'
```

Bottom line: tum aur mai same ho — `admin-homepage-filament` se shuru karna hai (jab user kahe). Repo + yeh doc = sab kuch recovery ke liye kafi hai.

---

## SESSION REPORT — 2026-08-13 (user offline, PM-mode autonomous run)

### Completed (all pushed to `feature/dev` → PR #22 auto-updates)

1. **codebase-quality-audit** — 116 PHP files syntax-checked; `declare(strict_types=1)` added to 75 files; dead `welcome.blade.php` removed; verified no debug/TODO/raw-SQL-injection/N+1/XSS; models consistent.
2. **dynamic-website-cms** — admin-managed **Pages** (URL slug field → dynamic catch-all routing, reserved slugs protected, TiptapEditor content, templates generic/about/contact) + **polymorphic `seo_entries`** (meta title/description/keywords, OG tags, canonical, robots, schema JSON-LD, head scripts) wired into layout `<head>` with Blade fallbacks. **Products, homepage, shop** all have SEO tabs in Filament (Details/Content + SEO tabs). PageSeeder anchors: home/about/contact/shop. **task_mode OFF** (audit done — per user directive).
3. **website-completion** — global **5-column footer** component on ALL pages (DB-driven Shop categories + Top brands + dynamic-page links), support pages seeded (shipping/returns/warranty/faqs/terms/privacy with SEO), single footer on homepage, trust badges.
4. **optimize-performance** — `Model::preventLazyLoading()` (dev/tests) caught + fixed a real N+1 (`ProductVariant::effectivePrice()` lazy relation → explicit product param); BrandService counts cached 1h + `BrandObserver` flush; existing caches verified (category tree, homepage sections/SEO).
5. **security-review** — `SecurityHeaders` middleware (CSP, X-Content-Type-Options, X-Frame-Options SAMEORIGIN, Referrer-Policy, Permissions-Policy, HSTS over HTTPS); `.env.example` → APP_DEBUG=false; session cookies auto-secure; `composer audit` 0 + `npm audit` 0 vulns; webhook/callback crypto signature tests; security header tests; CSRF default + honeypot + throttle + mass-assignment verified.

### Final gates
- **128 tests / 531 assertions — all green**
- `npm run build` passes
- Live verified: / , /shop, /product, /about, /contact, /shipping, /terms (all 200 + SEO titles), guest /checkout → 302 login
- Screenshots: `rythme-design-snapshots/` 33–39 (admin pages list, content tab, SEO tab, product details, product SEO, footer 5-col, support page)

### For next session
- Everything on `feature/dev`; single PR **#22** open (feature/dev → main) — review & merge once.
- `task_mode: false` — re-enable on user command.
- Next candidate tasks (user-directed when they return): real product images via admin media library, blog/journal, reviews, coupons, Postgres/pgvector semantic search, WebAuthn passkeys (Fortify) — all gated on user approval.

---

## PRODUCTION OPERATIONS (Phase 6 — 2026-08-13)

### Queue worker (emails, order notifications)
```bash
# .env
QUEUE_CONNECTION=database
# then run worker (one per app server; retry failed jobs):
php artisan queue:work --tries=3 --timeout=90
# supervisor/systemd recommended for long-running; failed jobs: php artisan queue:retry all
```

### Caching decisions (senior review)
- Cached: category tree (forever+observer), brand counts (1h+observer), homepage sections (1h+observer), homepage SEO (1h), site settings (forever+flush on save)
- Shop product queries: NOT response-cached (dynamic filters + pagination) — queries are eager-loaded, indexed, paginated; revisit only if metrics demand

### GST & shipping (admin Settings page)
- shipping_flat_fee · shipping_free_above · tax_rate (%) — applied server-side in checkout totals (grand total = subtotal − coupon + shipping + GST)

### Sitemap
- /sitemap.xml (home, shop, pages, categories, products) · /robots.txt (admin/cart/checkout/account disallowed)

### Error pages
- Custom 404 / 500 (design system) — resources/views/errors/
