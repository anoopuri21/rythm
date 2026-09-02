#!/usr/bin/env bash
# =====================================================================
#  Rhythm Exports — cPanel shared hosting deploy helper (MilesWeb Business)
#  Ye script SERVER pe (cPanel SSH/Terminal me) chalti hai, aapke laptop pe nahi.
#
#  Pehli baar:   bash scripts/deploy-cpanel.sh setup
#  Har update:   bash scripts/deploy-cpanel.sh update
#  Health check: bash scripts/deploy-cpanel.sh check
#
#  Full guide: docs/DEPLOY_MILESWEB.md
# =====================================================================
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$APP_DIR"

say()  { printf '\n\033[1;36m==> %s\033[0m\n' "$1"; }
ok()   { printf '\033[1;32m  ✔ %s\033[0m\n' "$1"; }
warn() { printf '\033[1;33m  ! %s\033[0m\n' "$1"; }
die()  { printf '\033[1;31m  ✘ %s\033[0m\n' "$1" >&2; exit 1; }

# ---------------------------------------------------------------------
#  cPanel pe `php` command aksar PURANA version hota hai (7.4 etc).
#  Isliye PHP 8.3/8.4 ka binary khud dhoondte hain. Ye is script ka
#  sabse zaruri hissa hai — 90% cPanel deploy yahin fail hote hain.
# ---------------------------------------------------------------------
detect_php() {
  if [ -n "${PHP_BIN:-}" ]; then return; fi
  local c
  for c in /opt/alt/php84/usr/bin/php /opt/alt/php83/usr/bin/php \
           /opt/cpanel/ea-php84/root/usr/bin/php /opt/cpanel/ea-php83/root/usr/bin/php \
           /usr/local/bin/ea-php84 /usr/local/bin/ea-php83 \
           /usr/local/bin/php php; do
    if command -v "$c" >/dev/null 2>&1 \
       && "$c" -r 'exit(version_compare(PHP_VERSION,"8.3.0",">=")?0:1);' 2>/dev/null; then
      PHP_BIN="$c"; return
    fi
  done
  PHP_BIN="php"
}

detect_composer() {
  if [ -n "${COMPOSER_BIN:-}" ]; then return; fi
  local c
  for c in /opt/cpanel/composer/bin/composer "$HOME/composer.phar" \
           /usr/local/bin/composer composer composer2; do
    if [ -f "$c" ] || command -v "$c" >/dev/null 2>&1; then COMPOSER_BIN="$c"; return; fi
  done
  COMPOSER_BIN=""
}

detect_php
detect_composer

require_env() {
  [ -f .env ] || die ".env file nahi mila. Pehle: cp .env.staging.example .env  (phir values bharo)"
}

php_version_check() {
  say "PHP version check"
  command -v "$PHP_BIN" >/dev/null 2>&1 || [ -f "$PHP_BIN" ] \
    || die "PHP nahi mila. cPanel Terminal me 'ls /opt/cpanel/' chalao aur sahi path PHP_BIN me do."
  "$PHP_BIN" -r 'exit(version_compare(PHP_VERSION, "8.3.0", ">=") ? 0 : 1);' 2>/dev/null \
    || die "PHP 8.3+ chahiye. Abhi: $("$PHP_BIN" -r 'echo PHP_VERSION;' 2>/dev/null).
   Fix: cPanel -> Software -> 'Select PHP Version' me 8.3 karo.
   Agar wahan already 8.3 hai lekin CLI purana hai, to ye chalao:
     ls -d /opt/cpanel/ea-php8*  /opt/alt/php8*  2>/dev/null
   aur milne wale path ke saath: PHP_BIN=<path>/php bash scripts/deploy-cpanel.sh setup"
  ok "PHP $("$PHP_BIN" -r 'echo PHP_VERSION;')  ($PHP_BIN)"

  local missing=""
  for ext in curl fileinfo gd intl mbstring pdo_mysql tokenizer xml zip; do
    "$PHP_BIN" -m | grep -qi "^${ext}$" || missing="$missing $ext"
  done
  [ -z "$missing" ] && ok "Saare zaruri PHP extensions maujood hain" \
    || warn "Ye extensions missing hain:$missing  (cPanel -> Select PHP Version -> Extensions me tick karo)"
}

install_deps() {
  say "Composer dependencies install (production mode)"
  [ -n "$COMPOSER_BIN" ] || die "Composer nahi mila. Ek baar ye chalao:
     cd ~ && curl -sS https://getcomposer.org/installer | $PHP_BIN
   phir dobara: bash scripts/deploy-cpanel.sh setup"
  ok "Composer: $COMPOSER_BIN"
  # Composer ko HAMESHA sahi PHP se chalao (cPanel ka default purana ho sakta hai)
  "$PHP_BIN" "$COMPOSER_BIN" install --no-dev --prefer-dist --optimize-autoloader --no-interaction \
    || "$COMPOSER_BIN" install --no-dev --prefer-dist --optimize-autoloader --no-interaction
  ok "vendor/ ready"
}

check_assets() {
  say "Frontend assets check"
  [ -f public/build/manifest.json ] \
    && ok "public/build/manifest.json mil gaya (CSS/JS ready)" \
    || die "public/build/manifest.json missing! Repo se 'git pull' karo — build assets repo me committed hain."
}

app_key() {
  require_env
  if grep -qE '^APP_KEY=.+' .env; then
    ok "APP_KEY already set"
  else
    say "APP_KEY generate"
    "$PHP_BIN" artisan key:generate --force
    ok "APP_KEY set ho gayi"
  fi
}

storage_perms() {
  say "storage/ aur bootstrap/cache/ folders + permissions"
  mkdir -p storage/framework/{cache/data,sessions,testing,views} storage/logs bootstrap/cache
  chmod -R 775 storage bootstrap/cache 2>/dev/null || true
  ok "Folders ready aur writable"
}

db_check() {
  say "Database connection test"
  "$PHP_BIN" artisan db:show --json >/dev/null 2>&1 \
    && ok "Database se connection ban gaya" \
    || die "Database se connect nahi ho pa raha.
   Fix: .env me DB_DATABASE / DB_USERNAME / DB_PASSWORD check karo.
   cPanel me naam ke aage username prefix lagta hai (jaise milesxyz_rythm).
   Aur cPanel -> MySQL Databases -> 'Add User To Database' me user ko
   ALL PRIVILEGES dena mat bhoolna."
}

migrate() {
  say "Database migrate (tables banana)"
  "$PHP_BIN" artisan migrate --force
  ok "Migrations complete"
}

seed() {
  say "Demo data seed (products, categories, admin user)"
  "$PHP_BIN" artisan db:seed --force
  ok "Seed complete — admin: admin@rythme.test / admin1234 (LOGIN KE BAAD PASSWORD BADLO)"
}

storage_link() {
  say "Storage symlink (uploaded images public karne ke liye)"
  "$PHP_BIN" artisan storage:link || warn "storage:link fail hua — shayad pehle se bana hai. Chalega."
}

optimize() {
  say "Cache rebuild (site fast karne ke liye)"
  "$PHP_BIN" artisan optimize:clear
  "$PHP_BIN" artisan config:cache
  "$PHP_BIN" artisan route:cache
  "$PHP_BIN" artisan view:cache
  ok "Config / route / view cache ban gaya"
}

health() {
  say "Health check"
  "$PHP_BIN" artisan about --only=environment || true
  "$PHP_BIN" artisan migrate:status | tail -n 5 || true
  local url
  url="$(grep -E '^APP_URL=' .env | cut -d= -f2- | tr -d '"')"
  if command -v curl >/dev/null && [ -n "$url" ]; then
    printf '  homepage  -> '; curl -s -o /dev/null -w '%{http_code}\n' "$url/" || true
    printf '  /up       -> '; curl -s -o /dev/null -w '%{http_code}\n' "$url/up" || true
    printf '  /shop     -> '; curl -s -o /dev/null -w '%{http_code}\n' "$url/shop" || true
    printf '  /admin    -> '; curl -s -o /dev/null -w '%{http_code}\n' "$url/admin" || true
    echo "  (200 ya 302 = theek hai; 500 = storage/logs/laravel.log dekho)"
  fi
}

# ---------------------------------------------------------------------
#  PLAN B support: jab public_html ek asli folder ho (symlink/docroot
#  change possible na ho), to app/public ke assets wahan copy karne padte
#  hain aur index.php ki jagah bridge file rakhni padti hai.
# ---------------------------------------------------------------------
PUBLIC_HTML="${PUBLIC_HTML:-$HOME/public_html}"

is_plan_b() {
  [ -d "$PUBLIC_HTML" ] && [ ! -L "$PUBLIC_HTML" ] \
    && [ "$(cd "$PUBLIC_HTML" 2>/dev/null && pwd -P)" != "$(cd "$APP_DIR/public" && pwd -P)" ]
}

sync_public() {
  say "Plan B: public/ assets ko public_html me copy kar rahe hain"
  [ -d "$PUBLIC_HTML" ] || die "public_html folder nahi mila: $PUBLIC_HTML  (PUBLIC_HTML=<path> se batao)"
  if command -v rsync >/dev/null 2>&1; then
    rsync -a --delete --exclude 'index.php' "$APP_DIR/public/" "$PUBLIC_HTML/"
  else
    cp -a "$APP_DIR/public/." "$PUBLIC_HTML/"
  fi
  cp "$APP_DIR/deploy/public_html-bridge-index.php" "$PUBLIC_HTML/index.php"
  # bridge file me app ka sahi path likh do
  local rel_base
  rel_base="$APP_DIR"
  "$PHP_BIN" -r '
    $f = $argv[1]; $base = $argv[2];
    $s = file_get_contents($f);
    $s = str_replace("__DIR__.\x27/../app\x27", var_export($base, true), $s);
    file_put_contents($f, $s);
  ' "$PUBLIC_HTML/index.php" "$rel_base"
  ok "public_html sync ho gaya (bridge index.php -> $APP_DIR)"
}

maybe_sync_public() {
  if is_plan_b; then sync_public; else ok "Docroot seedha app/public pe hai — sync ki zarurat nahi"; fi
}

case "${1:-}" in
  setup)
    php_version_check; require_env; install_deps; check_assets
    app_key; storage_perms; db_check; migrate; seed; storage_link; optimize
    maybe_sync_public; health
    say "SETUP COMPLETE 🎉  Ab browser me apna domain kholo." ;;
  update)
    require_env
    say "Maintenance mode ON"; "$PHP_BIN" artisan down --retry=60 || true
    # Safety net: agar beech me koi bhi step fail ho jaye (jaise route:cache),
    # to site ko 503 maintenance mode me phansa mat chhodo — wapas ON karo.
    trap 'say "Update fail hua — site wapas live kar rahe hain"; "$PHP_BIN" artisan up 2>/dev/null || true' ERR
    git pull --ff-only origin "$(git rev-parse --abbrev-ref HEAD)"
    install_deps; check_assets; storage_perms; db_check; migrate; optimize
    maybe_sync_public
    trap - ERR
    say "Maintenance mode OFF"; "$PHP_BIN" artisan up
    health
    say "UPDATE COMPLETE 🎉" ;;
  check)
    php_version_check; check_assets; require_env; db_check; health ;;
  sync-public)
    sync_public ;;
  *)
    echo "Use karo:  bash scripts/deploy-cpanel.sh [setup|update|check|sync-public]"; exit 1 ;;
esac
