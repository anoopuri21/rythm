# 🚀 Rythm — `rhythm.vsinfosys.in` Deploy Guide (MilesWeb Business Hosting)

> **Kiske liye:** Jise coding/deployment ka zero idea hai. Har step copy-paste karne layak hai.
> **Time:** ~45-60 minute (ek hi baar). Baad me har update = 1 command.

---

## 📍 Abhi kya status hai (maine check kiya — 2026-09-01)

| Cheez | Status |
|---|---|
| Domain `rhythm.vsinfosys.in` server (103.102.234.3) pe point | ✅ Ho gaya hai |
| Website content | ⬜ Abhi khali hai (kuch load nahi hota) — deploy baaki hai |
| SSL (https ka taala 🔒) | ❌ **Abhi nahi laga** — Part 1, Step 1.3 me banenge |
| Code repo | ✅ Ready (github.com/anoopuri21/rythm — branch `main`) |

---

## 🔑 Aapki fixed values (kahin note kar lo — har step me yahi use hongi)

| Cheez | Value |
|---|---|
| Domain | `rhythm.vsinfosys.in` |
| cPanel username | `gejkosrq` |
| Server par home folder | `/home/gejkosrq` |
| Database name | `gejkosrq_rythm_demo` |
| Database user | `gejkosrq_rythm_demo` |
| Database **password** | ❓ *Sirf aapke paas hai (jate waqt cPanel me banaya tha)* |
| App folder server pe | `/home/gejkosrq/rythm` |
| Admin login (deploy ke baad) | `admin@rythme.test` / `admin1234` — *baad me badalenge* |

> 🔒 **DB password mujhe batane ki zaroorat NAHI hai.** Guide me ek jagah aap khud daaloge.

---

## ✅ Progress tracker — yahan tak aaye, tick karte jao

| Part | Kaam | Status |
|---|---|---|
| 1.1 | PHP 8.3 + extensions | ⬜ |
| 1.2 | DB user → ALL PRIVILEGES + password ready | ⬜ |
| 1.3 | SSL (AutoSSL) chalu | ⬜ |
| 2 | cPanel Terminal kholna | ⬜ |
| 3.1 | Code download (git clone) | ⬜ |
| 3.2 | Settings file (.env) banana | ⬜ |
| 3.3 | Setup script chalana | ⬜ |
| 3.4 | Domain ko app se jodna (document root) | ⬜ |
| 3.5 | Website browser me khologe 🎉 | ⬜ |
| 4.1 | Admin password badalna | ⬜ |
| 4.2 | Cron job | ⬜ |
| 4.3 | Health check | ⬜ |

---

# PART 1 — cPanel me taiyari (browser me, koi typing nahi)

> cPanel kaise khole: browser me `rhythm.vsinfosys.in/cpanel` (ya MilesWeb client area → apne hosting plan ke saamne **Manage** → **cPanel**). Login: cPanel username `gejkosrq` + password.

## Step 1.1 — PHP version 8.3 karo ⚠️ SABSE ZARURI

Ye project PHP 8.3 maangta hai. Galat version = site kabhi nahi chalegi.

1. cPanel me **Software** section dhundo → **Select PHP Version** pe click
2. Upar dropdown me **8.3** (ya 8.4) select karo → **Set as current** / **Apply**
3. Usi page pe **Extensions** tab kholo, in sab pe ✅ tick lagao (jo pehle se tick hai unhe chhedo mat):

```
curl   fileinfo   gd   intl   mbstring   pdo_mysql   mysqli   tokenizer   xml   zip   bcmath   openssl
```

4. **Save** dabao (ya changes auto-save hote hain).

## Step 1.2 — Database user ki permission + password ready karo

1. cPanel → **Databases** → **MySQL® Databases**
2. Neeche scroll karo → **Add User To Database** wale section me dekho:
   - User: `gejkosrq_rythm_demo` aur Database: `gejkosrq_rythm_demo` **juke hue hon** (aage uncheck marks na ho)
3. Agar juke nahi hain → dono select karo → **Add** → agli screen pe **ALL PRIVILEGES** checkbox pe tick → **Make Changes**
4. **Password:** agar yaad hai to note kar lo. Agar **yaad nahi** → koi tension:
   - Usi page pe **MySQL Users** section me `gejkosrq_rythm_demo` ke saamne **Set Password** / **Change Password** pe click
   - **Password Generator** dabao → **Copy the password to clipboard** → Notepad me paste kar ke **save kar lo** → **Use Password** → **Update**

> ⚠️ Step 3 (ALL PRIVILEGES) skip hua to baad me "Access denied" error aayega. Ye #1 common galti hai.

## Step 1.3 — SSL (https) chalu karo 🔒

Abhi https is domain pe kaam nahi kar raha (maine check kiya). Ye step zaroori hai:

1. cPanel → **Security** → **SSL/TLS Status**
2. `rhythm.vsinfosys.in` ke aage wale box pe ✅ tick karo
3. **Run AutoSSL** button dabao
4. 2–15 minute do — phir wahi page refresh karke dekho ki taala 🔒 **green** ho gaya
5. Green hone ke baad: cPanel → **Domains** → `rhythm.vsinfosys.in` ke saamne **Force HTTPS Redirect** toggle **ON** karo

> Agar 15 min baad bhi taala red hai → MilesWeb live chat/support ticket: *"Please run AutoSSL for subdomain rhythm.vsinfosys.in"*. Deploy me ye block nahi karega — hum pehle http pe deploy karenge, SSL baad me bhi lag sakta hai.

---

# PART 2 — Terminal kholna (server me command likhne ki jagah)

1. cPanel → **Advanced** section → **Terminal** pe click
2. Warning aaye to **"I understand and want to proceed"** dabao
3. Ek **black screen** khulegi — bas, ab aap server ke andar ho 🎉

> Terminal icon na dikhe to mujhe batao — SSH wale tarika se karenge.

> 📌 **Copy-paste ka rule:** Har command ko **poora select karke** copy karo → Terminal me **right-click → Paste** (ya `Ctrl+Shift+V`) → **Enter**. Ek baar me EK command. Green/normal text aana = success. Red text = error (mujhe screenshot ya text bhej dena).

---

# PART 3 — Install (Terminal me)

## Step 3.1 — Code download karo (2 command)

Command 1 — home folder me jao:
```bash
cd ~
```

Command 2 — code download:
```bash
git clone https://github.com/anoopuri21/rythm.git rythm
```

✅ Success dikhega aisa: `Cloning into 'rythm'...` phir kuch lines aur wapas prompt (`$`).

## Step 3.2 — Settings file (.env) banao ⚠️ SABSE DHYAN SE

**Pehle Notepad kholo apne computer me.** Neeche ka poora block copy karke Notepad me paste karo:

```bash
cat > ~/rythm/.env <<'EOF'
APP_NAME="Rhythm Exports"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://rhythm.vsinfosys.in

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_MAINTENANCE_DRIVER=file
BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=gejkosrq_rythm_demo
DB_USERNAME=gejkosrq_rythm_demo
DB_PASSWORD=YAHAN_APNA_DB_PASSWORD_DAALO

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public

MAIL_MAILER=log
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS="no-reply@rhythm.vsinfosys.in"
MAIL_FROM_NAME="${APP_NAME}"

RAZORPAY_KEY_ID=
RAZORPAY_KEY_SECRET=
RAZORPAY_WEBHOOK_SECRET=
RAZORPAY_ALLOW_FAKE_PAYMENTS=false

RYTHME_CONTACT_PHONE=
RYTHME_CONTACT_EMAIL=
RYTHME_SOCIAL_INSTAGRAM=
RYTHME_SOCIAL_FACEBOOK=
RYTHME_SOCIAL_YOUTUBE=

VITE_APP_NAME="${APP_NAME}"
EOF
```

Ab Notepad me **sirf 1 line** theek karo:

- `DB_PASSWORD=YAHAN_APNA_DB_PASSWORD_DAALO` — `=` ke baad apna DB password paste karo (Step 1.2 wala). **Beech me space nahi, quotes nahi.**

> 💡 Agar SSL abhi tak nahi laga (Step 1.3 pending) to `APP_URL=https://...` ki jagah `APP_URL=http://rhythm.vsinfosys.in` kar do — SSL lagne ke baad https kar denge.

Ab Notepad ka **poora block copy** karo → Terminal me **right-click → Paste** → **Enter**.

Kuch output nahi aayega (prompt wapas aa jayega) — **ye normal hai**, file ban gayi.

Verify karo — ye command chalao:
```bash
head -12 ~/rythm/.env
```
✅ Dikhna chahiye: aapki settings, aur `DB_PASSWORD=` aapka password.

## Step 3.3 — Ek command me poora setup 🎯 (5–10 minute)

```bash
cd ~/rythm && bash scripts/deploy-cpanel.sh setup
```

Ye script khud karegi: PHP 8.3 dhundhna → Composer install → APP_KEY banana → database me tables banana → demo data bharna → cache banana.

✅ **Aakhir me dikhega:** `SETUP COMPLETE 🎉  Ab browser me apna domain kholo.`

⚠️ Agar beech me **red error** aaye — wahi line mujhe bhejo (ya screenshot). Sabse common 2 errors neeche **Troubleshooting** me hain.

## Step 3.4 — Domain ko app se jodo (document root)

**Tarika 1 (sabse saaf) — cPanel se:**

1. cPanel → **Domains** → `rhythm.vsinfosys.in` ke saamne **Manage** / **Document Root** click
2. Document Root field me abhi kuch aur hoga (jaise `public_html/rhythm`) — usko badal kar likho:
   ```
   rythm/public
   ```
   (agar field poora path maange to: `/home/gejkosrq/rythm/public`)
3. **Save** / **Change**

**Tarika 2 (agar field edit nahi ho raha) — Terminal se Plan B:**

Pehle dekho abhi docroot kahan hai — cPanel → Domains me likha hoga (jaise `public_html/rhythm`). Phir terminal me (path apne hisaab se badalna):

```bash
cd ~/rythm && PUBLIC_HTML=$HOME/public_html/rhythm bash scripts/deploy-cpanel.sh sync-public
```

Ye app ke assets us folder me copy kar dega aur ek bridge file rakh dega — site chalegi, docroot badle bina.

## Step 3.5 — Website kholo 🎉

Browser me ye URLs kholo (SSL laga ho to https, warna http):

| URL | Kya dikhna chahiye |
|---|---|
| `https://rhythm.vsinfosys.in` | Cinematic homepage (hero, products, sections) |
| `https://rhythm.vsinfosys.in/shop` | Products list |
| `https://rhythm.vsinfosys.in/up` | Patta green "Route /up" page |
| `https://rhythm.vsinfosys.in/admin` | Filament admin login |

Admin login: `admin@rythme.test` / `admin1234`

> Agar https kaam na kare par http chale → SSL abhi pending hai. Deploy complete hai, bas Step 1.3 poora karo.

---

# PART 4 — Live hone ke baad (jaroori security + polish)

## 4.1 — Admin password badlo (2 minute) 🔐

1. `https://rhythm.vsinfosys.in/admin` pe login karo (`admin@rythme.test` / `admin1234`)
2. Top-right **avatar/circle** pe click → **My Profile / Profile**
3. Naya strong password set karo → Save
4. (Optional) Naya admin email bhi yahin update kar do — password reset emails ispe aayenge

> Mail abhi `log` mode me hai (koi email bahar nahi jayegi). Baad me cPanel → **Email Accounts** bana ke SMTP details .env me daal sakte hain — mujhe bolna, 2 minute ka kaam hai.

## 4.2 — Cron job lagao (background kaam: emails, schedule)

1. cPanel → **Advanced** → **Cron Jobs**
2. **Add New Cron Job**: Common Settings → **Once Per Minute (***** * * * *)**
3. Command me ye paste karo → **Add New Cron Job**:
```bash
cd /home/gejkosrq/rythm && /opt/alt/php83/usr/bin/php artisan schedule:run >> /dev/null 2>&1
```
> Agar `/opt/alt/php83/...` na chale to Terminal me `ls /opt/alt/` chala ke dekho — `php84` dikhe to path me php84 kar dena. cPanel wala PHP (`/opt/cpanel/ea-php83/root/usr/bin/php`) bhi try kar sakte ho.

## 4.3 — Health check

Terminal me:
```bash
cd ~/rythm && bash scripts/deploy-cpanel.sh check
```
✅ Homepage / `/up` / `/shop` / `/admin` sab `200` ya `302` dikhna chahiye.

## 4.4 — Razorpay (payment) — ABHI SKIP, baad me

Jab demo approve ho jaye, Razorpay dashboard se **Test keys** (`rzp_test_...`) lekar .env me daalenge aur ek command se cache refresh karenge. Tab tak checkout payment ke bina try mat karna.

## 4.5 — Aage har update kaise hoga (1 command)

Code me koi bhi change `main` branch me merge hone ke baad, Terminal me bas:

```bash
cd ~/rythm && bash scripts/deploy-cpanel.sh update
```

Ye maintenance mode on karke update + migrate + cache refresh karke site wapas live kar dega (~2 min).

---

# 🚑 Troubleshooting — error aaye to pehle yahan dekho

| Problem | Matlab | Fix |
|---|---|---|
| `Access denied for user` (setup me) | DB user ko privileges nahi mili | Step 1.2 → ALL PRIVILEGES dobara check karo |
| `SQLSTATE[HY000] [1045]` | DB password galat | Step 1.2 me naya password set karo → Step 3.2 wali `.env` dobara banao |
| Setup me `Composer nahi mila` | Composer missing | Terminal me: `cd ~ && curl -sS https://getcomposer.org/installer | php -v` nahi chalega is form me — mujhe batao, exact command dunga |
| Site khole to `500 Server Error` | App error | Terminal: `tail -30 ~/rythm/storage/logs/laravel.log` → last lines mujhe bhejo |
| Page khula par **bina CSS** (sab plain text) | Assets nahi mil rahe | `ls ~/rythm/public/build/manifest.json` chalao — file honi chahiye; docroot `rythm/public` hi hai ye verify karo |
| `Vite manifest not found` | Build assets missing | `cd ~/rythm && git status` aur `ls public/build` → mujhe batao |
| https warning / SSL error | AutoSSL pending | Step 1.3 dobara / MilesWeb support |
| `/admin` pe login nahi ho raha | Password bhool gaye | Terminal: `cd ~/rythm && php artisan tinker --execute="App\Models\User::where('email','admin@rythme.test')->update(['password'=>bcrypt('NayaPassword@123')]);"` |
| Kuch aur | — | Mujhe error ka **screenshot ya exact red text** bhejo |

---

*Guide: docs/DEPLOY_RHYTHM_STEP_BY_STEP.md · Full reference: docs/DEPLOY_MILESWEB.md · Deploy script: scripts/deploy-cpanel.sh*
