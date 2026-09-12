<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Auth;

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

    /**
     * Authenticate as Filament staff on the isolated admin guard only.
     *
     * Laravel's actingAs($user, 'admin') also Auth::shouldUse('admin'), which
     * would make storefront auth middleware treat the staff as logged-in on web.
     * We restore the default guard to web so admin session ≠ storefront session
     * in tests (mirrors production: panel middleware switches to admin on /admin).
     */
    protected function actingAsAdmin(Authenticatable $user): static
    {
        $this->actingAs($user, 'admin');
        Auth::shouldUse('web');

        return $this;
    }
}
