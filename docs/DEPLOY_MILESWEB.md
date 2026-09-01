# 🚀 Rhythm Exports — MilesWeb (Business Hosting) pe Deploy karne ki Poori Guide

> **Kiske liye:** jise coding nahi aati. Har step copy-paste karne layak hai.
> **Target:** MilesWeb **Business Hosting (cPanel)** + aapka domain + Razorpay **TEST** mode (staging/demo).
> **Time:** pehli baar ~45-60 minute. Uske baad har update sirf **1 command** = 2 minute.

---

## 📋 Shuru karne se pehle

| # | Cheez | Status |
|---|---|---|
| 1 | MilesWeb cPanel login | aapke paas hai ✅ |
| 2 | Domain | aapke paas hai ✅ |
| 3 | SSH access | MilesWeb Business plan me **included** hai ✅ (bas ON karna hai) |
| 4 | Razorpay TEST keys | free — [dashboard.razorpay.com](https://dashboard.razorpay.com) pe signup |

> 💡 **MilesWeb ka panel cPanel hai** (Hostinger ke hPanel se alag dikhta hai). Neeche saare menu names cPanel ke hain.
> Login usually: `https://aapkadomain.com/cpanel` ya MilesWeb client area → **Manage** → **cPanel**

---

# PART 1 — cPanel me taiyari (browser me, koi command nahi)

## Step 1.1 — PHP version 8.3 karo ⚠️ SABSE ZARURI

1. cPanel me login karo
2. **Software** section → **Select PHP Version** pe click
3. Upar dropdown me **8.3** (ya 8.4) select karo → **Set as current** / **Apply**
4. Usi page pe **Extensions** tab pe jao aur in sab pe ✅ tick lagao:

```
curl   fileinfo   gd   intl   mbstring   pdo_mysql   mysqli   tokenizer   xml   zip   bcmath   json   openssl
```

5. Changes apne aap save ho jate hain (ya **Save** dabao)

> Kyun: Ye project Laravel 13 pe bana hai jo PHP 8.3 se neeche chalta hi nahi.

## Step 1.2 — MySQL database banao

1. cPanel → **Databases** → **MySQL® Databases**
2. **Create New Database** me naam daalo: `rythm` → **Create Database**
   → asli naam banega jaise `milesxyz_rythm` (aapka cPanel username prefix lagega)
3. Usi page pe neeche scroll → **MySQL Users → Add New User**
   - Username: `rythm_user` → banega `milesxyz_rythm_user`
   - Password: **Password Generator** dabao → **Use Password** → **kahin copy kar lo**
   - **Create User**
4. Aur neeche scroll → **Add User To Database**
   - User: abhi bana wala | Database: abhi bani wali → **Add**
   - Agli screen pe **ALL PRIVILEGES** wale checkbox pe tick → **Make Changes**

🔴 **Ye 3 cheezein Notepad me likh lo** (aage chahiye hongi):
```
DB_DATABASE = milesxyz_rythm
DB_USERNAME = milesxyz_rythm_user
DB_PASSWORD = <jo generate hua>
```

> ⚠️ Step 4 (ALL PRIVILEGES) skip karoge to "Access denied" error aayega. Ye sabse common galti hai.

## Step 1.3 — SSL (https) chalu karo

1. cPanel → **Security** → **SSL/TLS Status**
2. Apne domain pe tick → **Run AutoSSL** (2-15 minute lagta hai)
3. Phir cPanel → **Domains** → apne domain ke saamne **Force HTTPS Redirect** toggle **ON**

## Step 1.4 — SSH access ON karo

1. cPanel → **Security** → **SSH Access** → **Manage SSH Keys**
   (agar ye option na dikhe to MilesWeb support pe ticket: *"Please enable SSH access for my account"* — 10-15 min me ho jata hai)
2. **Notepad me note karo:**
```
Host     : aapkadomain.com  (ya server IP — cPanel ke right sidebar me "Shared IP Address")
Username : aapka cPanel username (jaise milesxyz)
Password : cPanel password
Port     : 21098   <- MilesWeb shared servers pe usually ye. 22 bhi try karna.
```

> Port confirm karne ka aasan tarika: MilesWeb support se live chat pe pucho *"What is the SSH port for my shared hosting account?"*

---

# PART 2 — Terminal (command likhne ki jagah) kholo

### Tarika A (sabse aasan — kuch install nahi karna) ✅
cPanel → **Advanced** → **Terminal** → warning aaye to **I understand and want to proceed** → ek black screen khulegi. Bas, aap server ke andar ho.

> Agar **Terminal** icon cPanel me na dikhe → Tarika B use karo.

### Tarika B — Windows ka apna terminal
1. `Windows key` dabao → `cmd` type karo → Enter
2. Ye likho (apne values daal ke) → Enter:
```
ssh -p 21098 milesxyz@aapkadomain.com
```
3. `Are you sure you want to continue connecting?` → `yes` → Enter
4. Password type karo — **screen pe kuch nahi dikhega, ye normal hai** → Enter

✅ Jab `milesxyz@server:~$` jaisa kuch dikhe — aap andar ho 🎉

---

# PART 3 — Project install (SSH me — Plan A, recommended)

> 👉 Ek-ek command copy karo, Enter dabao, output padho. Sab ek saath paste mat karna.
> 👉 `yourdomain.com` ki jagah apna asli domain likhna.

## Step 3.1 — Home folder me jao aur dekho kya hai

```bash
cd ~
ls
```
`public_html` folder dikhna chahiye ✅

## Step 3.2 — Project clone karo (public_html ke BAHAR)

```bash
cd ~
git clone https://github.com/anoopuri21/rythm.git app
cd app
git checkout arena/01a05cf1-rythm
ls
```
`app`, `config`, `public`, `artisan` dikhne chahiye ✅

> **Kyun `app` folder, `public_html` nahi?** Kyunki Laravel me sirf `public/` folder duniya ko dikhna chahiye. `.env` (jisme database password hai) kabhi web se accessible nahi hona chahiye.

## Step 3.3 — Domain ko project ke `public` folder pe point karo

Ye deploy ka sabse important step hai. **Do tarike hain — pehla try karo:**

### Tarika 1 — cPanel se Document Root badlo (sabse saaf) ⭐

1. cPanel → **Domains** → apne domain ke saamne **Manage** (ya edit icon)
2. **Document Root** field me abhi `/home/milesxyz/public_html` likha hoga
3. Usko badal ke ye karo:
```
/home/milesxyz/app/public
```
(`milesxyz` = apna cPanel username)
4. **Save** / **Update**

✅ Ho gaya. Step 3.4 pe jao.

### Tarika 2 — Symlink (agar Document Root field editable na ho)

SSH me:
```bash
cd ~
mv public_html public_html_backup
ln -s app/public public_html
ls -la
```
Output me ye line dikhni chahiye:
```
public_html -> app/public
```
✅ Ho gaya.

> ❌ Dono fail ho jaayein? Is guide ke **Plan B** pe jao.

## Step 3.4 — Settings file (.env) banao

```bash
cd ~/app
cp .env.staging.example .env
nano .env
```

Text editor khulega. Arrow keys se ghumo aur `___FILL___` ki jagah apni values (Step 1.2 wali Notepad se):

```
APP_URL=https://yourdomain.com
DB_DATABASE=milesxyz_rythm
DB_USERNAME=milesxyz_rythm_user
DB_PASSWORD=yahan-apna-password
```

Razorpay TEST keys (Razorpay dashboard → **Test Mode** ON karke → Settings → API Keys → Generate):
```
RAZORPAY_KEY_ID=rzp_test_xxxxxxxx
RAZORPAY_KEY_SECRET=xxxxxxxx
```

**Save:** `Ctrl + O` → `Enter` → `Ctrl + X`

## Step 3.5 — Ek command me poora setup 🎯

```bash
cd ~/app
bash scripts/deploy-cpanel.sh setup
```

Script apne aap ye sab karegi:
1. **Sahi PHP 8.3 binary dhoondhegi** (cPanel me `php` aksar purana 7.4 hota hai — script ye khud handle karti hai)
2. Composer se saari libraries install
3. Security key (APP_KEY) generate
4. Folder permissions set
5. Database connection test
6. Database tables banayegi (migrate)
7. Demo products + admin user daalegi (seed)
8. Image storage link banayegi
9. Cache banayegi (site fast)
10. Health check chalayegi

Aakhir me `SETUP COMPLETE 🎉` dikhna chahiye.

**Agar error aaye:**

| Error | Command jo chalao |
|---|---|
| `PHP 8.3+ chahiye` | `ls -d /opt/cpanel/ea-php8* /opt/alt/php8*` → jo path mile use: `PHP_BIN=/opt/cpanel/ea-php83/root/usr/bin/php bash scripts/deploy-cpanel.sh setup` |
| `Composer nahi mila` | `cd ~ && curl -sS https://getcomposer.org/installer \| php` → phir setup dobara |
| `Database se connect nahi` | `.env` me DB naam prefix (`milesxyz_`) check karo + Step 1.2 ka **ALL PRIVILEGES** wala step dobara karo |

## Step 3.6 — Website kholo 🎉

Browser me: **https://yourdomain.com**

Homepage laal/kaale/safed colours ke saath dikhna chahiye.

**Admin panel:** https://yourdomain.com/admin
```
Email    : admin@rythme.test
Password : admin1234
```
🔴 **Login karte hi password badal do.**

---

# PART 4 — Deploy ke baad ke 5 zaruri kaam

## 4.1 — Cron job lagao (background kaam ke liye)

cPanel → **Advanced** → **Cron Jobs** → **Add New Cron Job**
- **Common Settings:** Once Per Minute (`* * * * *`)
- **Command:**
```
cd /home/milesxyz/app && /opt/cpanel/ea-php83/root/usr/bin/php artisan schedule:run >> /dev/null 2>&1
```
(`milesxyz` apna username; PHP path wahi jo Step 3.5 me kaam aaya)

## 4.2 — Staging ko Google se chhupao

```bash
cd ~/app
printf 'User-agent: *\nDisallow: /\n' > public/robots.txt
```
> Asli launch pe isko wapas normal kar dena.

## 4.3 — Admin password badlo
Default password public repo me likha hai — turant badlo.

## 4.4 — Razorpay TEST payment check karo
Test card se ek order karke dekho:
```
Card: 4111 1111 1111 1111  |  CVV: koi bhi 3 digit  |  Expiry: koi bhi future date
```

## 4.5 — Health check
```bash
cd ~/app && bash scripts/deploy-cpanel.sh check
```
Sab `200` ya `302` = sab theek ✅

---

# 🔄 Aage har update kaise (2 minute)

```bash
cd ~/app
bash scripts/deploy-cpanel.sh update
```
Ye khud: maintenance ON → naya code pull → libraries update → DB migrate → cache rebuild → maintenance OFF.

---

# 🅱️ PLAN B — Agar Document Root bhi na badle aur symlink bhi fail ho

```bash
cd ~
rm -f public_html
mkdir public_html
cp app/deploy/public_html-fallback.htaccess public_html/.htaccess
```
Phir Step 3.4 se aage same. Browser me domain kholo — chal jana chahiye.

---

# 🆘 Common problems aur fix

| Kya dikh raha hai | Matlab | Fix |
|---|---|---|
| **500 Server Error** | kuch toota hai | `tail -50 ~/app/storage/logs/laravel.log` chalao, error mujhe bhejo |
| Page **bina design** (plain text) | CSS load nahi hui | `ls ~/app/public/build/manifest.json` — na ho to `git pull` |
| **"Vite manifest not found"** | wahi upar wali baat | same fix |
| **403 Forbidden** | permissions / symlink | `chmod 755 ~ ~/app ~/app/public` + `chmod -R 775 ~/app/storage ~/app/bootstrap/cache` |
| **SQLSTATE… Access denied** | DB details ya privileges | `.env` me prefix check + cPanel me ALL PRIVILEGES |
| **`Class not found` / composer errors** | galat PHP se install hua | `PHP_BIN=<sahi-php-path> bash scripts/deploy-cpanel.sh setup` |
| **"Please provide a valid cache path"** | folders missing | setup dobara chalao |
| Purana content dikh raha | cache | `cd ~/app && php artisan optimize:clear && php artisan optimize` |
| **`.env` browser me khul raha** 🚨 | docroot galat | Step 3.3 turant dobara karo |

---

# 🔐 Security checklist (staging)

- [ ] `APP_DEBUG=false` (template me already — badalna mat)
- [ ] Admin ka default password badla
- [ ] `https://yourdomain.com/.env` → **404 aana chahiye**
- [ ] `https://yourdomain.com/storage/logs/laravel.log` → **404**
- [ ] Razorpay sirf `rzp_test_` keys
- [ ] robots.txt me `Disallow: /`

---

# 📞 Live (asli) launch ke liye baad me chahiye hoga

`docs/release-checklist.md` + `docs/ops-runbook.md` + Razorpay **LIVE** keys + daily backup + legal pages (Terms, Privacy, Refund/Shipping policy — Razorpay live account activate karne ke liye mandatory hain).
