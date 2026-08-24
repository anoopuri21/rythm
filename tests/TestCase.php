<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Laravel infers the app base path from the Composer autoloader location.
     * When `vendor/` lives outside the project (e.g. a symlink to /tmp on
     * sandboxes, or any non-standard install), that inference points at the
     * wrong directory. Setting APP_BASE_PATH explicitly keeps tests working
     * on every machine — Windows, macOS, Linux, sandboxes.
     */
    protected function setUp(): void
    {
        $_ENV['APP_BASE_PATH'] = dirname(__DIR__);

        parent::setUp();
    }
}
