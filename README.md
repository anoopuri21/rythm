# Rythme Music Store

Rythme Music Store is a premium, cinematic e-commerce experience for musical instruments.
Laravel 13 + Blade + Tailwind 4 + Filament v3 admin — with a 15-section cinematic homepage
(hero slider, best sellers, video showcase, comparison, UGC, FAQ + JSON-LD, scroll effects).

- **Stack:** Laravel 13.24 (PHP 8.4) · Blade · Tailwind 4 · Alpine.js · GSAP/Lenis/Swiper/CountUp · Filament v3.3.54 · Livewire 3 · Mary UI 2 · TiptapEditor · Spatie MediaLibrary · Razorpay (test mode)
- **Reference:** bajaao.com (inspiration only — all copy is original)
- **Repo:** https://github.com/anoopuri21/rythm

## Quick Start — Windows (Laragon)

Full step-by-step guide: **[docs/WINDOWS_SETUP.md](docs/WINDOWS_SETUP.md)**

TL;DR:
```bash
# 1. Laragon me PHP 8.4 select karo (Menu → PHP → Version)
# 2. Terminal me:
cd C:\laragon\www
git clone https://github.com/anoopuri21/rythm.git
cd rythm
composer run setup        # deps + .env + key + sqlite + migrate + seed + npm build — sab ek saath
php artisan serve
# → http://127.0.0.1:8000  |  admin: /admin  (admin@rythme.test / admin1234)
```

## Quick Start — Linux/macOS

```bash
git clone https://github.com/anoopuri21/rythm.git && cd rythm
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite        # sqlite default; mysql ke liye .env me comments dekho
php artisan migrate --seed
npm install && npm run build
php artisan serve                     # http://127.0.0.1:8000
```

> `public/build/` is gitignored — **`npm run build` compulsory hai** fresh clone pe,
> warna page bina CSS ke dikhega ("Vite manifest not found").

## Technology requirements

- **PHP 8.4+** (composer.json: `"php": "^8.4"`)
- Composer 2
- Node.js 20.19+ / 22 LTS (Vite 7)
- Database: SQLite (zero-config, default) ya MySQL 8+
- Required PHP extensions: `curl`, `fileinfo`, `gd`, `intl`, `mbstring`, `sqlite3` (or `pdo_mysql`), `tokenizer`, `xml`, `zip`

## Project structure

```
app/
  Http/Controllers/          HomeController, NewsletterSubscriptionController
  Models/                    User, NewsletterSubscriber
  Providers/Filament/        AdminPanelProvider (panel: /admin)
resources/
  views/home/                _*.blade.php — 15 homepage sections + index
  views/components/          navbar, product-card
  views/layouts/             app.blade.php
  js/modules/                carousels.js, motion.js, ui.js
docs/                        AGENT_RULES_STRICT.md, architecture/, WINDOWS_SETUP.md
tasks/tasks.json             task source of truth
config/rythme.php            site config (s11 video URL, override via RYTHME_VIDEO_URL)
```

## Homepage sections (in order)

hero → categories → bestsellers → why-rythme → brands → numbers → new-arrivals →
deals → video-showcase → stories → testimonials → comparison → ugc → faq → footer

## Testing

```bash
php artisan test          # 7 tests (homepage sections + newsletter)
```

## Admin panel

- URL: `/admin` (Filament v3)
- Local login: `admin@rythme.test` / `admin1234` (created by `php artisan db:seed`)
- Next admin work: `admin-homepage-filament` task (homepage section manager with TiptapEditor)

## Project docs

- `docs/AGENT_RULES_STRICT.md` — non-negotiable rules (tech, images, content, workflow)
- `docs/architecture/00-project-architecture-overview.md` — architecture + roadmap
- `docs/WINDOWS_SETUP.md` — Windows clone→run guide
- `docs/NEXT_SESSION_CONTEXT.md` — agent handover context
- `tasks/tasks.json` — task board (source of truth)

## Roadmap (tasks/tasks.json)

- ✅ Laravel 13 upgrade, docs, homepage 10 sections, cinematic v2 + v3
- ⏳ admin-homepage-filament (next) · shop-list · product-detail · cart/checkout/payment · wishlist/about/contact · automation · footer 5-column
