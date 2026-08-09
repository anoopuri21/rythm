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

## 1. Laragon install + PHP 8.4 verify

1. Laragon Full installer chalao → `C:\laragon` me install karo (SmartScreen aaye to **More info → Run anyway**).
2. Laragon kholo → **Start All** (tray me green icon).
3. **Menu → PHP → Version** → `8.4.x` select karo. Agar list me nahi hai to **Add/Download** se 8.4.x download karo (Laragon khud kar leta hai).
4. **Menu → Terminal** kholo — isi terminal me aage ke saare commands chalenge.
5. Verify:
```bash
php -v                 # PHP 8.4.x
composer --version     # 2.x
node -v && npm -v      # Node 20.19+ / npm 10+
git --version
```

> ⚠️ `php -v` me 8.2/8.3 dikhe to step 1.3 karo — warna `composer install` pe
> `requires php ^8.4` error aayega.

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

| Error / Problem | Fix |
|---|---|
| `requires php ^8.4` / `your PHP version does not satisfy` | Laragon → Menu → PHP → Version → 8.4.x |
| `ext-gd` / `ext-intl` / `ext-zip` / `ext-fileinfo` missing | Laragon → Menu → PHP → php.ini → `;extension=gd` wale lines se `;` hatao (gd, intl, zip, fileinfo, mbstring, sqlite3) → Laragon restart |
| `No application encryption key` | `php artisan key:generate` |
| `Vite manifest not found` / bina CSS ka page | `npm install && npm run build` |
| `Database file at path [database/database.sqlite] does not exist` | `type nul > database\database.sqlite` (ya `composer run setup` chalao) |
| Port 8000 busy | `php artisan serve --port=8080` (aur `APP_URL` bhi match karo) |
| `Connection refused` (MySQL wale me) | Laragon me **Start All** dabao |
| `Table 'rythme_db.users' doesn't exist` | `php artisan migrate` |
| `Base table or view not found` | `php artisan migrate:fresh --seed` |
| Blank page / HTTP 500 | `storage\logs\laravel.log` kholo — aakhri error copy karke agent ko do |
| `git clone` fail | Git for Windows installed hai? URL check karo? |
| `npm` command not found | Node install karo (Section 0) ya Laragon ka Node use karo |
| Admin login reject | `php artisan db:seed` phir se chalao (idempotent hai), ya `php artisan tinker` me `App\Models\User::create(['name'=>'Admin','email'=>'admin@rythme.test','password'=>bcrypt('admin1234')]);` |

---

## FAQ

- **MySQL ya SQLite?** SQLite = zero config, local ke liye best. MySQL = prod-like, Laragon pe dono chal sakte hain.
- **Cloudinary kyu nahi hai?** Laravel 13 ke saath incompatible tha — media pipeline Spatie MediaLibrary se aayegi (pending task).
- **Razorpay?** Installed hai, test-mode me — cart/checkout task pending hai.
- **Vendor folder symlink / APP_BASE_PATH wali cheezein?** Woh sirf agent ke sandbox workspace ke liye hain — Windows pe normal install hota hai, kuch karna nahi.
