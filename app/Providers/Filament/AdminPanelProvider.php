<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
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
            ->globalSearch(false)
            ->login()
            ->brandName('Dashboard Fiorini')
            ->registration()
            ->colors([
                'primary' => [
                    50 => 'oklch(0.984 0.012 95.25)',
                    100 => 'oklch(0.958 0.028 92.31)',
                    200 => 'oklch(0.91 0.055 88.12)',
                    300 => 'oklch(0.846 0.082 84.75)',
                    400 => 'oklch(0.766 0.104 79.45)',
                    500 => 'oklch(0.685 0.115 72.58)',
                    600 => 'oklch(0.584 0.098 65.21)',
                    700 => 'oklch(0.481 0.079 59.67)',
                    800 => 'oklch(0.383 0.061 57.34)',
                    900 => 'oklch(0.293 0.047 54.63)',
                    950 => 'oklch(0.192 0.034 51.82)',
                ],
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
