#!/usr/bin/env bash
# =====================================================================
#  Rhythm Exports — Hostinger deploy helper
#  Ye script SERVER pe (Hostinger SSH me) chalti hai, aapke laptop pe nahi.
#
#  Pehli baar:   bash scripts/deploy-hostinger.sh setup
#  Har update:   bash scripts/deploy-hostinger.sh update
#  Health check: bash scripts/deploy-hostinger.sh check
#
#  Full guide: docs/DEPLOY_HOSTINGER.md
# =====================================================================
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$APP_DIR"

# Hostinger pe PHP 8.3 ka binary — agar alag ho to yahan badal do
PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer2}"

say()  { printf '\n\033[1;36m==> %s\033[0m\n' "$1"; }
ok()   { printf '\033[1;32m  ✔ %s\033[0m\n' "$1"; }
warn() { printf '\033[1;33m  ! %s\033[0m\n' "$1"; }
die()  { printf '\033[1;31m  ✘ %s\033[0m\n' "$1" >&2; exit 1; }

require_env() {
  [ -f .env ] || die ".env file nahi mila. Pehle: cp .env.staging.example .env  (phir values bharo)"
}

php_version_check() {
  say "PHP version check"
  command -v "$PHP_BIN" >/dev/null || die "PHP nahi mila. 'php -v' chala ke sahi binary ka path PHP_BIN me set karo."
  "$PHP_BIN" -r 'exit(version_compare(PHP_VERSION, "8.3.0", ">=") ? 0 : 1);' \
    || die "PHP 8.3+ chahiye. Abhi: $("$PHP_BIN" -r 'echo PHP_VERSION;'). hPanel -> Advanced -> PHP Configuration se badlo."
  ok "PHP $("$PHP_BIN" -r 'echo PHP_VERSION;')"

  local missing=""
  for ext in curl fileinfo gd intl mbstring pdo_mysql tokenizer xml zip; do
    "$PHP_BIN" -m | grep -qi "^${ext}$" || missing="$missing $ext"
  done
  [ -z "$missing" ] && ok "Saare zaruri PHP extensions maujood hain" \
    || warn "Ye extensions missing hain:$missing  (hPanel -> PHP Configuration -> PHP extensions me enable karo)"
}

install_deps() {
  say "Composer dependencies install (production mode)"
  command -v "$COMPOSER_BIN" >/dev/null || COMPOSER_BIN="composer"
  command -v "$COMPOSER_BIN" >/dev/null || die "Composer nahi mila. Hostinger SSH me 'composer2 -V' try karo."
  "$COMPOSER_BIN" install --no-dev --prefer-dist --optimize-autoloader --no-interaction
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

case "${1:-}" in
  setup)
    php_version_check; require_env; install_deps; check_assets
    app_key; storage_perms; migrate; seed; storage_link; optimize; health
    say "SETUP COMPLETE 🎉  Ab browser me apna domain kholo." ;;
  update)
    require_env
    say "Maintenance mode ON"; "$PHP_BIN" artisan down --render="errors::503" || true
    git pull --ff-only origin "$(git rev-parse --abbrev-ref HEAD)"
    install_deps; check_assets; storage_perms; migrate; optimize
    say "Maintenance mode OFF"; "$PHP_BIN" artisan up
    health
    say "UPDATE COMPLETE 🎉" ;;
  check)
    php_version_check; check_assets; health ;;
  *)
    echo "Use karo:  bash scripts/deploy-hostinger.sh [setup|update|check]"; exit 1 ;;
esac
