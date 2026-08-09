# NEXT SESSION CONTEXT — SOUNDSCAPE Project Continue

> Read this FIRST. Same format as the session-start context. Workspace was clean last time;
> everything is committed to GitHub so recovery is scriptable.

---

## God Mode ON — SOUNDSCAPE Project Continue

**Workspace:** `/home/user/rythm` (repo `anoopuri21/rythm`)
**Branch:** `arena/019fe1bf-rythm` (main pe kabhi kaam nahi karna)
**Git log:** `4f910e5` (feat: SOUNDSCAPE conversion) ← `affd8d0` (composer.lock) ← `8a5413f` ← …

## Tech Stack STRICT

Laravel 13 (PHP 8.4) + Blade + Filament **v3.3.54** + Tailwind 4 + Alpine.js + GSAP/Lenis/Swiper/CountUp + TiptapEditor 3.5.16 + Spatie MediaLibrary 11.23.4 + Livewire 3.8.3 + Mary 2.9.9. **Next.js bilkul nahi.**

Verify:
```bash
php artisan --version        # must be: Laravel Framework 13.24.0
ls next.config.js             # must NOT exist
composer show | grep -E "filament/filament|laravel/framework"
```

## Strict Rules File

**`docs/AGENT_RULES_STRICT.md` — MUST read before every task.** Summary:
- Tech: Laravel 13 + Filament v3 ONLY
- Images: ONLY Bajaao/Amazon (products) · Unsplash/Pexels free license (UI, with license comment) · AI Generated (hero/banner, alt + `[AI Generated]` comment)
- Content: SEO friendly, unique (no verbatim Bajaao), natural keywords, Filament heading/title = **TextInput**, baki sab **TiptapEditor**
- Workflow: tasks.json source of truth → chunk plan → build → `npm run build` + `php artisan test` pass → done + commit
- LOCKED (kabhi touch nahi): Header, Footer, Cart, Wishlist, Checkout/Payment

## Project Docs

- `docs/architecture/00-project-architecture-overview.md` — Amazon drawer menu + 5-column footer (future), homepage 10+5 cinematic sections, admin multi-group sidebar, flow: Home → Admin Home → Shop → Detail → Cart → Checkout → Payment → Wishlist → About/Contact
- `config/rythme.php` — s11 video URL (Pexels CC0, override via `RYTHME_VIDEO_URL`)
- `plan.md` — phases 1–14 all ✅

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
| automation-system | pending (recreate + task mode ON) |
| footer-5-column | pending (footer locked until this task) |

**Task mode was OFF this session (user instruction).** Next session: task mode ON — recreate automation system first (below), then run task loop.

## Automation System (recreate if missing — same rules)

- `automation/config.json` (interval 30; health: `php artisan --version && php artisan config:clear`)
- `automation/task-agent.mjs` (reads tasks.json → chunk design → test → commit → update tasks.json)
- `automation/task-loop`: `bash -c 'while true; do node automation/task-agent.mjs --once; sleep 1800; done'`
- `.github/workflows/auto-dev.yml` (every 2h + push)
- `logs/task-agent.log`, `logs/task-plan-*.md`, `logs/task-report-*.md`

## Local System Recreation (new session — FIRST do this)

```bash
# 1. PHP 8.4 + extensions (Debian 13: apt has 8.4; /usr/local/bin gets wiped between sessions)
sudo apt-get install -y php8.4-cli php8.4-mbstring php8.4-xml php8.4-curl php8.4-zip php8.4-gd \
  php8.4-sqlite3 php8.4-bcmath php8.4-intl php8.4-dom php8.4-tokenizer php8.4-xmlwriter php8.4-simplexml unzip

# 2. Composer 2.10.2 (if missing)
curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php \
  && sudo php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer --quiet

# 3. Repo — if workspace empty: clone + branch
git clone https://github.com/anoopuri21/rythm.git /home/user/rythm
cd /home/user/rythm
git checkout -B arena/019fe1bf-rythm origin/arena/019fe1bf-rythm

# 4. Dependencies (vendor/node_modules/public/build are snapshot-excluded → always reinstall)
composer install --no-interaction --no-progress
npm install --no-audit --no-fund && npm run build

# 5. Env (local dev uses sqlite)
cp .env.example .env   # then set: DB_CONNECTION=sqlite, DB_DATABASE=/home/user/rythm/database/database.sqlite,
                       # SESSION_DRIVER=file, CACHE_STORE=file, QUEUE_CONNECTION=sync
touch database/database.sqlite && php artisan key:generate && php artisan migrate --force

# 6. Git identity + remote (snapshot wipes .git/config → remote must be re-added)
git config user.name  "auto-agent"; git config user.email "auto-agent@soundscape.local"
git remote add origin https://github.com/anoopuri21/rythm.git   # tokenless; push with token inline if needed

# 7. Verify
php artisan --version   # 13.24.0
php artisan test        # 7 passed
```

**⚠️ Git state quirk (encountered 2026-08-09):** workspace snapshots restore `.git` to an older state —
after any session gap, HEAD may be back on `main`, local `arena/*` branch may be missing, and the remote
config may be empty. Fix: `git remote add origin …` + `git checkout -B arena/019fe1bf-rythm origin/arena/019fe1bf-rythm`
(force with `-f` if untracked files block the switch — files are identical to the branch tree).

## Session learnings (2026-08-09)

1. **Laravel 13 + Filament 3.3.54 verified working** — 3.3.54 is the ONLY 3.3.x supporting illuminate ^13; keep it pinned (`^3.2` resolves to it).
2. **Cloudinary removed** from composer.json — cloudinary-labs 3.0.2 requires illuminate ^11|^12 only. Re-add only when media pipeline is built (Spatie MediaLibrary is the plan).
3. **Blade gotcha (Laravel 13):** `@context` in raw JSON-LD is a Blade directive → use PHP arrays + `json_encode` for schema markup.
4. **s11 video:** Pexels CC0 direct URL verified working — `https://videos.pexels.com/video-files/854924/854924-hd_1920_1080_25fps.mp4` (configurable via `RYTHME_VIDEO_URL`).
5. **New homepage order:** hero → categories → bestsellers → why-rythme → brands → numbers → new-arrivals → deals → video-showcase → stories → testimonials → comparison → ugc → faq → footer.
6. **Images:** `public/images/video-showcase-poster.jpg` + `public/images/ugc/{studio-vocalist,guitar-corner,dj-desk}.jpg` — AI Generated, labeled in comments/alt.
7. **Security:** a GitHub PAT was pasted in chat and used for pushes — **user should revoke it** (Settings → Developer settings → PATs). Repo remote config is tokenless.

## Resume commands

```bash
git status && cat tasks/tasks.json && tail -f logs/task-agent.log   # (logs exist only after automation is recreated)
node automation/task-agent.mjs --once                                # task mode ON
# or loop: bash -c 'while true; do node automation/task-agent.mjs --once; sleep 1800; done'
```

Bottom line: tum aur mai same ho — aage ka kaam `admin-homepage-filament` se shuru karo (task mode ON karke).
