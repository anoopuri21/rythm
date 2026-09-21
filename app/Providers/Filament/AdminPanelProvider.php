<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Widgets\LatestOrdersWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Http\Middleware\UseAdminAuthGuard;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            // Separate session guard from storefront "web" so /admin login
            // does not authenticate /account, checkout, wishlist, etc.
            ->authGuard('admin')
            ->login()
            ->profile()
            ->multiFactorAuthentication(
                AppAuthentication::make()
                    ->recoverable()
                    ->brandName('Rythme / Rhythm Exports'),
                isRequired: fn (): bool => ! app()->runningUnitTests(),
            )
            ->strictAuthorization()
            ->colors([
                'primary' => Color::Red,
            ])
            ->navigationGroups([
                'SHOP',
                'HOMEPAGE',
                'COMMERCE',
                'CONTENT',
                'OPERATIONS',
                'SECURITY',
                'COMMUNICATION',
                'SETTINGS',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                StatsOverviewWidget::class,
                LatestOrdersWidget::class,
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                // Resolve auth() / policies against the admin guard for the whole panel.
                UseAdminAuthGuard::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
