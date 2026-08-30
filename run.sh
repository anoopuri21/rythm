#!/usr/bin/env bash
set -Eeuo pipefail

# Owner-side qualification runner.
# This runner intentionally uses in-memory SQLite for destructive PHP tests.
# It never runs a destructive migration against the configured UAT database.

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT"

step() {
    printf '\n==> %s\n' "$1"
}

fail() {
    printf '\nSTOPPED: %s\n' "$1" >&2
    exit 1
}

command -v git >/dev/null 2>&1 || fail 'Git is required.'
command -v php >/dev/null 2>&1 || fail 'PHP is required. Install PHP 8.3+ and run this script again.'
command -v composer >/dev/null 2>&1 || fail 'Composer is required.'
command -v npm >/dev/null 2>&1 || fail 'Node/npm is required.'

[[ "$(git branch --show-current)" == "rhythm-uat" ]] || fail 'Checkout must be on rhythm-uat.'
[[ -z "$(git status --porcelain)" ]] || fail 'Working tree is not clean. Save or discard local edits before running qualification.'

step 'Repository and dependency preflight'
printf 'Commit: '
git rev-parse HEAD
composer validate --strict
if [[ ! -f vendor/autoload.php ]]; then
    composer install --no-interaction --prefer-dist --no-progress
fi
npm ci --no-audit --no-fund

step 'Isolated Phase 11 MySQL migration and route checks'
# The database name is deliberately fixed so this script cannot migrate the
# persistent UAT database by accident. DB host/user/password continue to come
# from the owner's local .env (or may be supplied as PHASE11_QA_DB_* variables).
QA_DB_DATABASE="${PHASE11_QA_DB_DATABASE:-rhythm_phase11_qa}"
[[ "$QA_DB_DATABASE" == "rhythm_phase11_qa" ]] || fail 'QA database must be exactly rhythm_phase11_qa.'
(
    export DB_CONNECTION="${PHASE11_QA_DB_CONNECTION:-mysql}"
    export DB_DATABASE="$QA_DB_DATABASE"
    [[ -z "${PHASE11_QA_DB_HOST:-}" ]] || export DB_HOST="$PHASE11_QA_DB_HOST"
    [[ -z "${PHASE11_QA_DB_PORT:-}" ]] || export DB_PORT="$PHASE11_QA_DB_PORT"
    [[ -z "${PHASE11_QA_DB_USERNAME:-}" ]] || export DB_USERNAME="$PHASE11_QA_DB_USERNAME"
    [[ -z "${PHASE11_QA_DB_PASSWORD:-}" ]] || export DB_PASSWORD="$PHASE11_QA_DB_PASSWORD"
    php artisan config:clear --no-ansi
    php artisan migrate --force --no-ansi
    php artisan migrate:status --no-ansi
    php artisan route:list --path=account/stock-alerts --no-ansi
)

step 'Focused Phase 11 and account tests in isolated in-memory SQLite'
env \
    APP_ENV=testing \
    DB_CONNECTION=sqlite \
    DB_DATABASE=:memory: \
    CACHE_STORE=array \
    QUEUE_CONNECTION=sync \
    SESSION_DRIVER=array \
    MAIL_MAILER=array \
    php artisan test tests/Feature/PhaseElevenCustomerExperienceTest.php tests/Feature/AccountTest.php --no-ansi

step 'Full PHP regression in isolated in-memory SQLite'
env \
    APP_ENV=testing \
    DB_CONNECTION=sqlite \
    DB_DATABASE=:memory: \
    CACHE_STORE=array \
    QUEUE_CONNECTION=sync \
    SESSION_DRIVER=array \
    MAIL_MAILER=array \
    php artisan test --no-ansi

step 'Frontend, automation and dependency checks'
npm run test:automation
npm run build
composer audit --locked --no-interaction
npm audit --omit=dev --audit-level=high
git diff --check

printf '\nQUALIFICATION COMPLETE: all scripted checks passed.\n'
printf 'Browser responsive/accessibility/conversion review remains a manual owner step.\n'
printf 'Do not paste passwords, API keys, customer data or payment credentials into the evidence.\n'
