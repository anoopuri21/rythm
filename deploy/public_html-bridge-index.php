<?php

/**
 * =====================================================================
 *  PLAN B BRIDGE — sirf tab use karo jab Document Root badalna aur
 *  symlink dono kaam na karein (docs/DEPLOY_MILESWEB.md -> Plan B).
 *
 *  Ye file public_html/index.php ke naam se rakhni hai.
 *  Laravel app ~/app me rehti hai; ye file usko wahan se boot karti hai,
 *  taki .env aur code web se accessible na hon.
 *
 *  Copy karne ka command (script khud bhi kar deti hai):
 *    bash scripts/deploy-cpanel.sh sync-public
 * =====================================================================
 */

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Laravel project ka folder — public_html ke bahar (default: ~/app)
$base = __DIR__.'/../app';

if (! is_file($base.'/vendor/autoload.php')) {
    http_response_code(500);
    exit('Laravel app not found at '.$base.' — check the $base path in public_html/index.php');
}

// Maintenance mode
if (file_exists($maintenance = $base.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $base.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once $base.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
