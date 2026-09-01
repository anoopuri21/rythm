# 🚀 Rhythm Exports — Hostinger pe Deploy karne ki Poori Guide (Staging/Demo)

> **Kiske liye:** jise coding nahi aati. Har step copy-paste karne layak hai.
> **Target:** Hostinger shared hosting + aapka apna domain + Razorpay **TEST** mode (demo/staging).
> **Time:** pehli baar ~45-60 minute. Uske baad har update sirf **1 command** = 2 minute.

---

## 📋 Shuru karne se pehle checklist

Ye 4 cheezein aapke paas honi chahiye:

| # | Cheez | Kahan se milegi |
|---|---|---|
| 1 | Hostinger account ka login | aapke paas hai ✅ |
| 2 | Domain (jaise `rhythmexports.com`) | aapke paas hai ✅ |
| 3 | Hostinger plan me **SSH access** | Premium / Business / Cloud plans me hota hai. Single plan me nahi — us case me **Plan C** dekho |
| 4 | Razorpay account (TEST mode) | free — [dashboard.razorpay.com](https://dashboard.razorpay.com) pe signup |

> ⚠️ **Sabse pehle ye check karo:** hPanel me login karo → left menu → **Advanced** → agar **"SSH Access"** naam ka option dikh raha hai to 👍 aap **Plan A** follow karoge (sabse aasan). Agar nahi dikhta, to seedha **Plan C** pe jao.

---

# PART 1 — Hostinger pe taiyari (browser me, koi command nahi)

## Step 1.1 — Domain ko hosting se jodo

1. [hpanel.hostinger.com](https://hpanel.hostinger.com) pe login karo
2. Upar **Websites** tab → **Add Website** (ya agar domain already juda hai to skip)
3. Apna domain daalo → **Add**
4. Domain ke saamne **Manage** button dabao — ye aapka control panel hai, isko khula rakho

**Check:** browser me apna domain kholo. Hostinger ka default "Website coming soon" page dikhna chahiye. Agar "site not found" aaye to DNS propagate hone do (2-24 ghante).

## Step 1.2 — PHP version 8.3 karo (BAHUT ZARURI)

1. hPanel → left menu → **Advanced** → **PHP Configuration**
2. **PHP version** tab → **8.3** select karo (8.4 bhi chalega) → **Update**
3. Ab **PHP extensions** tab pe jao aur ye sab pe ✅ tick lagao:

```
curl   fileinfo   gd   intl   mbstring   pdo_mysql   tokenizer   xml   zip   bcmath
```

4. **Save** dabao

> Kyun: ye project Laravel 13 pe bana hai, jo PHP 8.3 se neeche chalta hi nahi. 90% deploy yahin fail hote hain.

## Step 1.3 — MySQL database banao

1. hPanel → **Databases** → **Management**
2. Form bharo:
   - **Database name:** `rythm_staging`
   - **Database username:** `rythm_user`
   - **Password:** **Generate** button dabao (strong password bana dega)
3. **Create** dabao
4. 🔴 **Ab jo 3 cheezein screen pe dikhengi unhe Notepad me copy karke rakh lo** — inki zarurat 2 minute baad padegi:

```
DB_DATABASE = u123456789_rythm_staging     <- pura naam, prefix ke saath
DB_USERNAME = u123456789_rythm_user
DB_PASSWORD = <jo generate hua>
```

> ⚠️ Hostinger naam ke aage `u123456789_` jaisa prefix apne aap lagata hai. Wahi pura naam chahiye, chhota wala nahi.

## Step 1.4 — SSL (https) chalu karo

1. hPanel → **Security** → **SSL**
2. Apne domain ke saamne **Install SSL** (agar already active hai to ✅)
3. Neeche **Force HTTPS** ka toggle **ON** kar do

## Step 1.5 — SSH access chalu karo aur details note karo

1. hPanel → **Advanced** → **SSH Access**
2. Toggle **ON** karo
3. Screen pe ye 3 cheezein dikhengi — Notepad me copy kar lo:

```
SSH IP / Host : 82.xx.xx.xx
SSH Port      : 65002
SSH Username  : u123456789
SSH Password  : (aapke hosting account ka password)
```

---

# PART 2 — Server se connect hona

Aapko ek "terminal" chahiye jisme command likh sako. **Sabse aasan tarika — browser hi kaafi hai:**

### Tarika A (recommended, kuch install nahi karna) — Hostinger ka Browser Terminal
hPanel → **Advanced** → **SSH Access** → **Browser terminal** / **Open terminal** button dabao. Bas. Ek black screen khulegi — wahi terminal hai.

### Tarika B — Windows ka apna terminal
1. Keyboard pe `Windows key` dabao → `cmd` type karo → Enter
2. Ye command likho (apne numbers daal ke) aur Enter:
```bash
ssh -p 65002 u123456789@82.xx.xx.xx
```
3. `Are you sure you want to continue connecting?` → `yes` likh ke Enter
4. Password maange to type karo — **screen pe kuch nahi dikhega, ye normal hai** — phir Enter

✅ Jab aisa kuch dikhne lage `u123456789@srv123:~$` — matlab aap server ke andar ho. 🎉

---

# PART 3 — PLAN A: Project install karo (SSH ke saath — recommended)

> 👉 Neeche har command **ek-ek karke** copy karo, paste karo, Enter dabao, aur output padho. Ek saath sab paste mat karna.
> 👉 Jahan `yourdomain.com` likha hai, wahan apna asli domain likhna.

## Step 3.1 — Sahi folder me jao

```bash
cd ~/domains/yourdomain.com
ls
```
Output me `public_html` dikhna chahiye. ✅

## Step 3.2 — Project ko server pe clone karo

```bash
git clone https://github.com/anoopuri21/rythm.git app
```

Thoda time lagega. Fir:

```bash
cd app
git checkout arena/01a05cf1-rythm
ls
```
`app`, `config`, `public`, `artisan` jaisi cheezein dikhni chahiye. ✅

> **Note:** `arena/01a05cf1-rythm` wo branch hai jisme deploy ki taiyari (built assets + scripts) hai. Jab ye branch `main` me merge ho jaye, to `main` use kar lena.

## Step 3.3 — Domain ko project ke `public` folder pe point karo

Ye deploy ka sabse important step hai. Laravel me sirf `public/` folder duniya ko dikhna chahiye, baaki sab chhupa rehna chahiye.

```bash
cd ~/domains/yourdomain.com
mv public_html public_html_backup
ln -s app/public public_html
ls -la
```

Output me aisi line dikhni chahiye:
```
public_html -> app/public
```
✅ Ho gaya. (`public_html_backup` me Hostinger ka purana default page hai — baad me delete kar dena.)

> ❌ Agar `ln` command error de ("Operation not permitted") → is guide ke **Plan B** pe jao.

## Step 3.4 — Settings file (.env) banao

```bash
cd ~/domains/yourdomain.com/app
cp .env.staging.example .env
nano .env
```

Ab ek text editor khulega. Arrow keys se ghumo aur ye 4 lines me `___FILL___` ki jagah apni values daalo (Step 1.3 wali Notepad se):

```
APP_URL=https://yourdomain.com
DB_DATABASE=u123456789_rythm_staging
DB_USERNAME=u123456789_rythm_user
DB_PASSWORD=yahan-apna-password
```

Razorpay TEST keys bhi (Razorpay dashboard → Settings → API Keys → Test mode):
```
RAZORPAY_KEY_ID=rzp_test_xxxxxxxx
RAZORPAY_KEY_SECRET=xxxxxxxx
```

**Save karne ke liye:** `Ctrl + O` → `Enter` → `Ctrl + X`

## Step 3.5 — Ek command me poora setup 🎯

```bash
cd ~/domains/yourdomain.com/app
bash scripts/deploy-hostinger.sh setup
```

Ye script apne aap ye sab karegi:
1. PHP version aur extensions check
2. Composer se saari libraries install
3. Security key generate
4. Folder permissions set
5. Database tables banayegi (migrate)
6. Demo products + admin user daalegi (seed)
7. Image storage link banayegi
8. Cache banayegi (site fast)
9. Health check chalayegi

Aakhir me `SETUP COMPLETE 🎉` dikhna chahiye.

> ⚠️ Agar `composer2: command not found` aaye → `composer` use karne ke liye:
> `COMPOSER_BIN=composer bash scripts/deploy-hostinger.sh setup`
>
> ⚠️ Agar PHP version galat bole → `php -v` chalao, sahi na ho to:
> `PHP_BIN=/usr/bin/php8.3 bash scripts/deploy-hostinger.sh setup`

## Step 3.6 — Website kholo 🎉

Browser me: **https://yourdomain.com**

Homepage colours ke saath (laal/kaala/safed) dikhna chahiye.

**Admin panel:** https://yourdomain.com/admin
```
Email    : admin@rythme.test
Password : admin1234
```
🔴 **Login karte hi password badal do** (Admin panel → apna profile → password change).

---

# PART 4 — Deploy ke baad ke 5 zaruri kaam

## 4.1 — Cron job lagao (background kaam ke liye — emails, queue)

hPanel → **Advanced** → **Cron Jobs** → **Create New Cron Job**
- **Type:** Custom
- **Interval:** Every Minute (`* * * * *`)
- **Command:**
```
cd /home/u123456789/domains/yourdomain.com/app && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```
(`u123456789` aur domain apna daalo)

## 4.2 — Staging ko Google se chhupao (demo hai, index nahi hona chahiye)

```bash
cd ~/domains/yourdomain.com/app
printf 'User-agent: *\nDisallow: /\n' > public/robots.txt
```

> Jab asli launch karo, to isko wapas normal kar dena.

## 4.3 — Admin password badlo + demo admin hatao
Login karke password change karo. Ye seeded default password public repo me likha hai — isliye zaruri hai.

## 4.4 — Razorpay TEST mode confirm karo
`.env` me keys `rzp_test_` se shuru honi chahiye. Test card se order karke check karo:
```
Card: 4111 1111 1111 1111  |  CVV: koi bhi 3 digit  |  Expiry: koi bhi future date
```

## 4.5 — Health check chalao
```bash
bash scripts/deploy-hostinger.sh check
```
Sab `200` ya `302` aaye to sab theek. ✅

---

# 🔄 Aage har baar update kaise karein (2 minute)

Jab bhi code me naya change aaye:

```bash
cd ~/domains/yourdomain.com/app
bash scripts/deploy-hostinger.sh update
```

Ye khud se: maintenance mode ON → naya code pull → libraries update → DB migrate → cache rebuild → maintenance OFF.

---

# 🅱️ PLAN B — Agar `ln -s` (symlink) kaam na kare

1. Symlink hatao aur normal folder wapas lao:
```bash
cd ~/domains/yourdomain.com
rm -f public_html
mkdir public_html
```
2. Repo se fallback file copy karo:
```bash
cp app/deploy/public_html-fallback.htaccess public_html/.htaccess
```
3. Browser me domain kholo — chal jana chahiye.
4. Baaki sab steps (3.4 se aage) same hain.

---

# 🅲 PLAN C — Agar aapke plan me SSH hi nahi hai

Do options hain — pehla behtar hai:

**Option 1 (recommended): Hostinger plan Premium pe upgrade karo.**
Kyunki bina SSH ke Laravel deploy karna bahut manual aur error-prone hai (vendor folder me 10,000+ files FTP se upload karni padengi, 30+ minute lagenge, aur har update pe dobara).

**Option 2: FTP se manual upload** — agar upgrade nahi karna:
1. Mujhe batao, main aapke liye ek **ready-to-upload ZIP** bana dunga jisme `vendor/` + built assets sab pehle se ho
2. Us ZIP ko hPanel → **File Manager** → `domains/yourdomain.com/` me upload karke **Extract** karna hoga
3. Database tables banane ke liye main ek **token-protected temporary URL** bana dunga (`/deploy/<secret>`) jise ek baar kholne se migrate chal jayega, phir wo route hata denge

---

# 🆘 Common problems aur fix

| Kya dikh raha hai | Matlab | Fix |
|---|---|---|
| **500 Server Error** | kuch toota hai | `cat storage/logs/laravel.log \| tail -50` chala ke aakhri error padho / mujhe bhejo |
| Page bilkul **bina design** (plain text) | CSS load nahi hui | `ls public/build/manifest.json` — na ho to `git pull` karo |
| **"Vite manifest not found"** | wahi upar wali baat | same fix |
| **403 Forbidden** | permissions | `chmod -R 755 ~/domains/yourdomain.com/app && chmod -R 775 storage bootstrap/cache` |
| **SQLSTATE… Access denied** | DB details galat | `.env` me DB naam/user prefix (`u123456789_`) check karo |
| **"Please provide a valid cache path"** | folders missing | `bash scripts/deploy-hostinger.sh setup` dobara chalao |
| Site pe **purana content** dikh raha | cache | `php artisan optimize:clear && php artisan optimize` |
| **`.env` browser me khul raha hai** 🚨 | docroot galat hai | Plan A step 3.3 dobara karo — turant |

---

# 🔐 Security — staging pe minimum

- [ ] `APP_DEBUG=false` (template me already hai — badalna mat)
- [ ] Admin ka default password badla
- [ ] `https://yourdomain.com/.env` browser me kholo → **404 aana chahiye**, content nahi
- [ ] `https://yourdomain.com/storage/logs/laravel.log` → **404 aana chahiye**
- [ ] Razorpay sirf TEST keys
- [ ] robots.txt me `Disallow: /`

---

# 📞 Agla kadam

Jab tak ye staging chal na jaye, live production pe mat jaana. Live jaate waqt extra chahiye hoga:
`docs/release-checklist.md` + `docs/ops-runbook.md` + Razorpay LIVE keys + backup schedule + legal pages (Terms, Privacy, Refund policy — Razorpay live activation ke liye mandatory hain).
