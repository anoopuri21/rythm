# Windows Setup Guide — Rythme Music Store (Laravel 13)

Clone → Run karne ka complete step-by-step guide (Windows desktop). Koi error aaye to
[Troubleshooting](#-troubleshooting) dekho, phir bhi nahi bane to error ka text copy karke
agent ko do.

---

## 0. Requirements (ek baar install karo)

| Tool | Version | Kahan se |
|---|---|---|
| **Laragon Full** | latest | https://laragon.org/download |
| **Git for Windows** | latest | https://git-scm.com (Laragon me nahi hai to) |
| **Node.js** | 20.19+ / 22 LTS | https://nodejs.org (sirf agar Laragon ka Node purana ho — Vite 7 ko Node 20.19+ chahiye) |

> Laragon me PHP + Composer + Node + MySQL sab built-in aata hai — Windows ke liye
> sabse easy combo. Ek hi terminal (Laragon → Menu → Terminal) me sab chalta hai.

---

## 1. Laragon install + PHP verify

1. Laragon Full installer chalao → `C:\laragon` me install karo (SmartScreen aaye to **More info → Run anyway**).
2. Laragon kholo → **Start All** (tray me green icon).
3. **Menu → PHP → Version** → `8.3.x` (ya `8.4.x`) select karo — project ab **PHP 8.3.30+ pe chalta hai** (lock file 8.3 ke liye resolve kiya gaya hai). Agar list me nahi hai to **Add/Download** se download karo ya neeche "PHP 8.4 install karne ka manual tarika" follow karo.
4. **Menu → Terminal** kholo — isi terminal me aage ke saare commands chalenge. **Har baar naya terminal kholna (ya Laragon restart) jab PHP version switch karo** — purana terminal purani PHP use karta hai.
5. Verify:
```bash
php -v                 # PHP 8.3.30+ (8.4 bhi chalega) — 8.2 ya chhota hai to upar wala step karo
composer --version     # 2.x
node -v && npm -v      # Node 20.19+ / npm 10+  (Vite 7 ke liye zaroori)
git --version
```

> ✅ **PHP 8.3.30+ OK hai** — project ka composer.lock ab 8.3 ke liye resolve kiya gaya hai (Symfony 7.4). PHP 8.2 ya chhota ho tabhi ruko.

---

## 2. Clone

```bash
cd C:\laragon\www
git clone https://github.com/anoopuri21/rythm.git
cd rythm
git branch            # "* main" dikhna chahiye
```

---

## 3. One-command setup (recommended)

```bash
composer run setup
```

Yeh ek saath karta hai: `composer install` → `.env` banaata hai → app key → sqlite file
banata hai → migrations → seeder (admin user) → `npm install` → `npm run build`.

**Khatam hone pe seedha [Step 6 — RUN](#6-run-) pe jao.**

---

## 4. Manual setup (agar one-command skip karna ho)

### 4a. PHP dependencies
```bash
composer install
```

### 4b. `.env` banao
```bash
copy .env.example .env
```
`.env.example` ab **SQLite by default** hai — kuch badalne ki zaroorat nahi. (MySQL
chahiye ho to `.env` me commented MySQL lines uncomment karo + `DB_CONNECTION=sqlite`
comment karo, aur `mysql -u root -e "CREATE DATABASE IF NOT EXISTS rythme_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"` chalao.)

### 4c. Key + database + seed
```bash
php artisan key:generate
type nul > database\database.sqlite      # agar file exist na kare
php artisan migrate
php artisan db:seed
```

### 4d. Frontend build (COMPULSORY — bhoolna mat!)
```bash
npm install
npm run build
```
> `public/build/` gitignored hai — fresh clone pe exist nahi karta. Skip kiya to site
> bina CSS ke dikhegi + `Vite manifest not found` error aayega.

---

## 5. Admin login (optional)

Seeder ne ye users bana diye hain:

| Email | Password | Use |
|---|---|---|
| `admin@rythme.test` | `admin1234` | Filament admin panel |
| `test@example.com` | `password` | Test user |

Admin panel: **http://127.0.0.1:8000/admin**

---

## 6. RUN 🚀

```bash
php artisan serve
```
Browser: **http://127.0.0.1:8000** — homepage 15+ sections (hero slider, bestsellers,
video showcase, comparison, UGC, FAQ…) dikhni chahiye.

**Ya Laragon virtual host:** (agar Step 1 me Start All on hai)
- URL: **http://rythm.test** — is case me `.env` me `APP_URL=http://rythm.test` set karo.

**Development mode (CSS/JS live-reload):** `php artisan serve` ke saath dusre terminal me
`npm run dev` chalao (Vite hot-reload). Production-style: `npm run build` (upar).

---

## 7. Tests (verify sab theek hai)

```bash
php artisan test
# Expect: 7 passed (25 assertions)
```

---

## 🛠 Troubleshooting

| # | Error / Problem | Fix |
|---|---|---|
| 1 | **`Root composer.json requires php ^8.4 but your php version (8.3.30)`** + `Your lock file does not contain a compatible set of packages` | ✅ **FIXED in repo (2026-08-09):** composer.lock ab PHP 8.3.30 ke liye resolve kiya gaya hai (Symfony 8.1→7.4, `platform.php=8.3.30`). **Sirf `git pull` karo aur `composer install` chalao** — error chala jayega. Agar phir bhi aaye to naya Laragon Terminal kholo (`where php` check) ya `composer clear-cache && composer install`. |
| 2 | `requires php ^8.4` / `your PHP version does not satisfy` (after switching) | Naya Laragon Terminal kholo (purana terminal purani PHP pakde rehta hai). `where php` se check karo ki PATH me sahi PHP hai. |
| 3 | `ext-gd` / `ext-intl` / `ext-zip` / `ext-fileinfo` missing | Laragon → Menu → PHP → php.ini → `;extension=gd` wale lines se `;` hatao (gd, intl, zip, fileinfo, mbstring, sqlite3, openssl, curl, dom, xml, bcmath) → Laragon restart |
| 4 | `No application encryption key` | `php artisan key:generate` |
| 5 | `Vite manifest not found` / bina CSS ka page | `npm install && npm run build` |
| 6 | `Database file at path [database/database.sqlite] does not exist` | `type nul > database\database.sqlite` (ya `composer run setup` chalao) |
| 7 | Port 8000 busy | `php artisan serve --port=8080` (aur `APP_URL` bhi match karo) |
| 8 | `Connection refused` (MySQL wale me) | Laragon me **Start All** dabao |
| 9 | `Table 'rythme_db.users' doesn't exist` | `php artisan migrate` |
| 10 | `Base table or view not found` | `php artisan migrate:fresh --seed` |
| 11 | Blank page / HTTP 500 | `storage\logs\laravel.log` kholo — aakhri error copy karke agent ko do |
| 12 | `git clone` fail | Git for Windows installed hai? URL check karo? |
| 13 | `npm` command not found | Node install karo (Section 0) ya Laragon ka Node use karo |
| 14 | Admin login reject | `php artisan db:seed` phir se chalao (idempotent hai), ya `php artisan tinker` me `App\Models\User::create(['name'=>'Admin','email'=>'admin@rythme.test','password'=>bcrypt('admin1234')]);` |
| 15 | `npm install` me `engine "node" is incompatible` / `EBADENGINE` | Vite 7 ko **Node 20.19+** chahiye. `node -v` check karo — purana hai to https://nodejs.org se Node 22 LTS install karo aur **naya terminal kholo** |
| 16 | `npm run build` me `esbuild` error | Naya Node LTS install karo, `rm -rf node_modules` + `npm install` phir se |
| 17 | `composer install` me `The "php" version ... platform config` / lock issue (8.4 active hone ke baad bhi) | `composer clear-cache` phir `composer update --lock` (lock file ko current platform se re-verify) |
| 18 | `SQLSTATE[HY000]: General error: 14 unable to open database file` | `database\` folder ka path sahi hai? `.env` me `DB_CONNECTION=sqlite` + `database/database.sqlite` file exist karti hai? |
| 19 | Filament `/admin` pe `404` / blank | `php artisan route:list` me `/admin` dikhta hai? Nahi to `php artisan optimize:clear` + `composer dump-autoload` |

### PHP 8.4 install karne ka manual tarika (agar Laragon me na ho)

1. https://windows.php.net/download → **PHP 8.4 (x64, Thread Safe / VS17)** ZIP download karo
2. ZIP ko extract karo → `C:\laragon\bin\php\php-8.4.x-Win32-vs17-x64\`
3. `php.ini-development` ko copy karke `php.ini` banao (ya Laragon → Menu → PHP → php.ini se)
4. `php.ini` me ye extensions enable karo (`;extension=...` se `;` hatao): `gd`, `intl`, `zip`, `fileinfo`, `mbstring`, `sqlite3`, `pdo_sqlite`, `openssl`, `curl`, `dom`, `xml`, `bcmath`
5. Laragon restart → Menu → PHP → Version → ab 8.4 dikhega → select karo
6. Laragon → Menu → Terminal → `php -v` → **8.4.x** confirm → `composer run setup`

---

## FAQ

- **MySQL ya SQLite?** SQLite = zero config, local ke liye best. MySQL = prod-like, Laragon pe dono chal sakte hain.
- **Cloudinary kyu nahi hai?** Laravel 13 ke saath incompatible tha — media pipeline Spatie MediaLibrary se aayegi (pending task).
- **Razorpay?** Installed hai, test-mode me — cart/checkout task pending hai.
- **Vendor folder symlink / APP_BASE_PATH wali cheezein?** Woh sirf agent ke sandbox workspace ke liye hain — Windows pe normal install hota hai, kuch karna nahi.
