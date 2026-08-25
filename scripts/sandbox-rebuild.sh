#!/bin/bash
# ============================================================
# sandbox-rebuild.sh — full runtime rebuild for the Rythme app
# in the Arena/E2B sandbox (after a sandbox reset wipes
# /tmp, ~/.local, vendor, node_modules, .env, DB, build).
#
# Usage:  bash scripts/sandbox-rebuild.sh
# Logs:   /tmp/rebuild.log, marker: /tmp/REBUILD_DONE
# Requires: git repo at /home/user/rythm, node/npm, gh (authed).
# ============================================================
set -x
export PATH="$HOME/.local/bin:$PATH"
LOG=/tmp/rebuild.log
exec > >(tee -a "$LOG") 2>&1

echo "=== [1/8] PHP (gh-api → static-php.dev CDN fallback) ==="
if [ ! -x "$HOME/.local/bin/php" ]; then
  mkdir -p "$HOME/.local/bin"
  # Primary: nativephp/php-bin via GitHub API (needs gh + egress).
  if command -v gh >/dev/null 2>&1 && gh api repos/nativephp/php-bin/contents/bin/linux/x64/php-8.3.zip \
    -H "Accept: application/vnd.github.raw" > /tmp/php.zip 2>/dev/null && [ -s /tmp/php.zip ]; then
    unzip -o -j -q /tmp/php.zip -d "$HOME/.local/bin/"
    chmod +x "$HOME/.local/bin/php"
    echo "php via gh-api OK"
  else
    # FALLBACK 1: npm @libphp (PHP 8.3, Amazon Linux build) — try first as requested.
    echo "gh-api unavailable — trying npm @libphp fallback..."
    if [ ! -d /tmp/libphp ]; then
      npm install --prefix /tmp/libphp @libphp/amazon-linux-2-v83 --no-audit --no-fund >/dev/null 2>&1
    fi
    if [ -f /tmp/libphp/node_modules/@libphp/amazon-linux-2-v83/native/php/php ]; then
      cp /tmp/libphp/node_modules/@libphp/amazon-linux-2-v83/native/php/php "$HOME/.local/bin/php"
      chmod +x "$HOME/.local/bin/php"
      # Amazon Linux build may need libncurses.so.5 — if it fails to run, fall through.
      if "$HOME/.local/bin/php" -v >/dev/null 2>&1; then
        echo "php via @libphp fallback OK"
      else
        echo "@libphp binary missing libs — trying static-php.dev CDN..."
        rm -f "$HOME/.local/bin/php"
      fi
    fi
    # FALLBACK 2: static-php.dev CDN — non-GitHub, Debian-compatible static PHP 8.3.10.
    if [ ! -x "$HOME/.local/bin/php" ]; then
      if [ ! -f /tmp/php-static/php ]; then
        rm -rf /tmp/php-static && mkdir -p /tmp/php-static
        curl -sL --max-time 60 "https://dl.static-php.dev/static-php-cli/common/php-8.3.10-cli-linux-x86_64.tar.gz" -o /tmp/php-static.tar.gz
        tar -xzf /tmp/php-static.tar.gz -C /tmp/php-static
      fi
      cp /tmp/php-static/php "$HOME/.local/bin/php"
      chmod +x "$HOME/.local/bin/php"
      echo "php via static-php.dev fallback OK"
    fi
  fi
fi
php -v | head -1 || { echo "ERROR: PHP install failed (all fallbacks)." >&2; exit 1; }
# Composer may need ext-intl which static builds lack — tolerate it.
export COMPOSER_IGNORE_PLATFORM_REQ="${COMPOSER_IGNORE_PLATFORM_REQ:-}"

echo "=== [2/8] Composer 2 phar (getcomposer.org → gh-source fallback) ==="
if [ ! -x "$HOME/.local/bin/composer.phar" ] || ! "$HOME/.local/bin/php" "$HOME/.local/bin/composer.phar" --version >/dev/null 2>&1; then
  # PRIMARY: official getcomposer.org phar — non-GitHub, no gh needed.
  if curl -sL --max-time 60 https://getcomposer.org/download/latest-stable/composer.phar \
    -o "$HOME/.local/bin/composer.phar" 2>/dev/null && [ -s "$HOME/.local/bin/composer.phar" ]; then
    echo "composer via getcomposer.org OK"
  else
    echo "getcomposer.org failed — trying gh-source build..."
    rm -f "$HOME/.local/bin/composer.phar"
    if [ ! -d /tmp/composer-src ]; then
      git clone --depth 1 -q https://github.com/composer/composer.git /tmp/composer-src
    fi
    # bootstrap: old 1.x mirror phar, patched for PHP 8.3
    if [ ! -s "$HOME/.local/bin/composer-1x.phar" ]; then
      gh api "repos/snowdrogon/composer.phar/contents/composer.phar?ref=master" \
        -H "Accept: application/vnd.github.raw" > "$HOME/.local/bin/composer-1x.phar" || true
    fi
    if [ -s "$HOME/.local/bin/composer-1x.phar" ]; then
      cat > /tmp/patch1x.php <<'PHPEOF'
<?php
$pharPath = getenv("HOME") . "/.local/bin/composer-1x.phar";
$phar = new Phar($pharPath);
$phar->startBuffering();
$path = "src/Composer/Util/ErrorHandler.php";
$c = $phar[$path]->getContent();
$needle = "public static function handle(\$level, \$message, \$file, \$line)\n{";
$rep = $needle . "\n if (\$level === E_DEPRECATED || \$level === E_USER_DEPRECATED) { return true; }";
if (strpos($c, "E_USER_DEPRECATED) { return true; }") === false && strpos($c, $needle) !== false) {
    $c = str_replace($needle, $rep, $c);
    $tmp = tempnam(sys_get_temp_dir(), "eh"); file_put_contents($tmp, $c);
    $phar->delete($path); $phar->addFile($tmp, $path); unlink($tmp);
}
$files = [];
foreach (new RecursiveIteratorIterator($phar) as $f) {
    $p = $f->getPathname();
    if (substr($p, -4) === '.php') $files[] = $p;
}
$prefix = 8 + strlen($pharPath);
foreach ($files as $p) {
    $rel0 = substr($p, $prefix);
    $content = $phar[$rel0]->getContent();
    $tokens = @token_get_all($content);
    if ($tokens === false) continue;
    $stack = []; $pending = null; $lastSig = null; $changed = false;
    for ($i = 0, $n = count($tokens); $i < $n; $i++) {
        $t = $tokens[$i];
        if (is_array($t)) {
            if (!in_array($t[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) $lastSig = $t[0];
            if ($t[0] === T_STRING && ($t[1] === 'self' || $t[1] === 'static')) {
                $pending = [$i, $t[1]];
                continue;
            }
            if ($pending !== null && $t[0] === T_OBJECT_OPERATOR) {
                $pending = null;
            } elseif ($pending !== null && $t[0] === T_DOUBLE_COLON && $lastSig !== T_NEW) {
                $newContent = $content;
                $newContent = substr_replace($newContent, $tokens[$pending[0]][1], strpos($newContent, $tokens[$pending[0]][1], 0), strlen($tokens[$pending[0]][1]));
                $changed = true;
                $pending = null;
            } else {
                $pending = null;
            }
        }
    }
    if ($changed) {
        $tmp = tempnam(sys_get_temp_dir(), 'lint'); file_put_contents($tmp, $newContent);
        if (@$phar[$rel0]->getContent() !== $newContent) {
            $tmp2 = tempnam(sys_get_temp_dir(), 'src'); file_put_contents($tmp2, $newContent);
            $phar->delete($rel0); $phar->addFile($tmp2, $rel0); unlink($tmp2);
        }
        unlink($tmp);
    }
}
$phar->stopBuffering();
echo "patch done\n";
PHPEOF
      "$HOME/.local/bin/php" /tmp/patch1x.php || true
    fi
    # build composer 2 from source using the (patched) 1.x bootstrap
    (cd /tmp/composer-src && "$HOME/.local/bin/php" -d error_reporting=0 "$HOME/.local/bin/composer-1x.phar" install --ignore-platform-reqs --no-interaction -q || true)
    if [ -f /tmp/composer-src/composer.phar ]; then
      cp /tmp/composer-src/composer.phar "$HOME/.local/bin/composer.phar"
      echo "composer via gh-source build OK"
    else
      echo "ERROR: composer unavailable (getcomposer.org + source both failed)." >&2
      exit 1
    fi
  fi
fi
"$HOME/.local/bin/php" -d error_reporting=0 "$HOME/.local/bin/composer.phar" --version 2>/dev/null | head -1

echo "=== [3/8] Vendor ==="
cd /home/user/rythm
if [ ! -d vendor ] || [ ! -f vendor/autoload.php ]; then
  php -d error_reporting=0 "$HOME/.local/bin/composer.phar" install --ignore-platform-reqs --no-interaction
fi

echo "=== [4/8] Env + DB ==="
if [ ! -f .env ]; then
  cp .env.example .env
  sed -i 's|^APP_URL=.*|APP_URL=|' .env
  sed -i 's|^APP_DEBUG=.*|APP_DEBUG=true|' .env
  printf '\nRYTHME_LOGO_URL=https://www.rhythmexports.com/wp-content/uploads/2023/10/Rhythm.png\n' >> .env
fi
php artisan key:generate --force >/dev/null 2>&1
touch database/database.sqlite
php artisan migrate:fresh --seed
php artisan storage:link >/dev/null 2>&1

echo "=== [5/8] Frontend build ==="
if [ ! -f public/build/manifest.json ]; then
  npm install --no-audit --no-fund
  npm run build
fi

echo "=== [6/8] Product images (committed fallback = reset-proof) ==="
# Committed product photos live in public/images/products/ — no Spatie
# attach needed. Attaching from /home/user/prodimg is optional (fresh uploads).
ls public/images/products/ 2>/dev/null | wc -l

echo "=== [7/8] Server ==="
pkill -f "php [-]S" 2>/dev/null; pkill -f "artisan [s]erve" 2>/dev/null; sleep 1
nohup php artisan serve --host=0.0.0.0 --port=8000 > /tmp/serve.log 2>&1 &
sleep 3
curl -s -o /dev/null -w "home:%{http_code}\n" http://127.0.0.1:8000/

echo "=== [8/8] DONE ==="
echo ok > /tmp/REBUILD_DONE
