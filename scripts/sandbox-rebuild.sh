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

echo "=== [1/8] PHP ==="
if [ ! -x "$HOME/.local/bin/php" ]; then
  mkdir -p "$HOME/.local/bin"
  gh api repos/nativephp/php-bin/contents/bin/linux/x64/php-8.3.zip \
    -H "Accept: application/vnd.github.raw" > /tmp/php.zip
  unzip -o -j -q /tmp/php.zip -d "$HOME/.local/bin/"
  chmod +x "$HOME/.local/bin/php"
fi
php -v | head -1

echo "=== [2/8] Composer 2 phar (build from source) ==="
if [ ! -x /tmp/composer-src/composer.phar ] || [ ! -x "$HOME/.local/bin/composer.phar" ]; then
  if [ ! -d /tmp/composer-src ]; then
    git clone --depth 1 -q https://github.com/composer/composer.git /tmp/composer-src
  fi
  # bootstrap: old 1.x mirror phar, patched for PHP 8.3
  if [ ! -s "$HOME/.local/bin/composer-1x.phar" ]; then
    gh api "repos/snowdrogon/composer.phar/contents/composer.phar?ref=master" \
      -H "Accept: application/vnd.github.raw" > "$HOME/.local/bin/composer-1x.phar"
    # NOTE: heredoc-to-`php -` fails when stdin is closed (background proc);
    # write the patch to a temp file instead.
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
    echo "patch1: ErrorHandler\n";
}
$files = [];
foreach (new RecursiveIteratorIterator($phar) as $f) {
    $p = $f->getPathname();
    if (substr($p, -4) === '.php') $files[] = $p;
}
$prefix = 8 + strlen($pharPath);
$total = 0;
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
            if ($t[0] === T_SWITCH) $pending = 'S';
            elseif ($t[0] === T_FOR || $t[0] === T_FOREACH || $t[0] === T_DO) $pending = 'L';
            elseif ($t[0] === T_WHILE && $lastSig !== '}') $pending = 'L';
            elseif ($t[0] === T_CONTINUE) {
                for ($s = count($stack) - 1; $s >= 0; $s--) {
                    if ($stack[$s] === 'B') continue;
                    if ($stack[$s] === 'S') { $tokens[$i] = 'break'; $changed = true; }
                    break;
                }
            }
        } elseif ($t === '{') { $stack[] = $pending !== null ? $pending : 'B'; $pending = null; }
        elseif ($t === '}') { if (!empty($stack)) array_pop($stack); }
    }
    if ($changed) {
        $newContent = '';
        foreach ($tokens as $t) $newContent .= is_array($t) ? $t[1] : $t;
        $tmp = tempnam(sys_get_temp_dir(), 'lint'); file_put_contents($tmp, $newContent);
        $out = shell_exec(PHP_BINARY . ' -l ' . escapeshellarg($tmp) . ' 2>&1'); unlink($tmp);
        if (strpos($out, 'No syntax errors') === false) { echo "LINT FAIL $rel0\n"; continue; }
        $phar->delete($rel0);
        $tmp2 = tempnam(sys_get_temp_dir(), 'src'); file_put_contents($tmp2, $newContent);
        $phar->addFile($tmp2, $rel0); unlink($tmp2);
        echo "patch2: $rel0\n"; $total++;
    }
}
$phar->stopBuffering();
echo "patch2 total: $total\n";
PHPEOF
    php -d phar.readonly=0 /tmp/patch1x.php
  fi
  cd /tmp/composer-src
  php -d error_reporting=0 "$HOME/.local/bin/composer-1x.phar" install --ignore-platform-reqs --no-interaction -q
  php -d phar.readonly=0 bin/compile
  cp /tmp/composer-src/composer.phar "$HOME/.local/bin/composer.phar"
fi
php -d error_reporting=0 "$HOME/.local/bin/composer.phar" --version 2>/dev/null | head -1

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
