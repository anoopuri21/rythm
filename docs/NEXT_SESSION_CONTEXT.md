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
# ⚠️ TESTS: symlinked vendor se inferBasePath() /tmp ban jata hai → ye env var
# zaroori hai (bina repo change ke):
#   APP_BASE_PATH=/home/user/rythm php artisan test
# Server: APP_BASE_PATH=/home/user/rythm php artisan serve --host=0.0.0.0 --port=8000

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

Laravel 13 (PHP 8.4) + Blade + Filament **v3.3.54** + Tailwind 4 + Alpine.js + TiptapEditor 3.5.16 + Spatie MediaLibrary 11.23.4 + Livewire 3.8.3 + Mary 2.9.9 + GSAP/Lenis/Swiper/CountUp. **Next.js bilkul nahi.** Filament 3.3.54 = ONLY 3.3.x supporting illuminate ^13 — pinned rakhna (`^3.2` resolve hota hai usi pe).

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
   - Laravel 13 `Testing\TestCase::createApplication()` → `Application::inferBasePath()` → `ClassLoader::getRegisteredLoaders()` se derive → vendor /tmp me → `/tmp/bootstrap/app.php` fail. **Fix (COMMITTED):** `phpunit.xml` me `<env name="APP_BASE_PATH" value="/home/user/rythm"/>`
10. **Environment reset DEEP hota hai:** sirf `.git` nahi — **working files bhi purani snapshot state pe restore ho sakti hain** (blade files gayab ho gayi thi → sections silently missing). Fix: `git checkout -f -B <branch> origin/<branch>` + `php artisan view:clear`

## Resume commands

```bash
git status && cat tasks/tasks.json
# jab user task mode ON bole:
node automation/task-agent.mjs --once
# ya loop: bash -c 'while true; do node automation/task-agent.mjs --once; sleep 1800; done'
```

Bottom line: tum aur mai same ho — `admin-homepage-filament` se shuru karna hai (jab user kahe). Repo + yeh doc = sab kuch recovery ke liye kafi hai.
