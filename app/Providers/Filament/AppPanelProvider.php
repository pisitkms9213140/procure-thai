<?php

namespace App\Providers\Filament;

use App\Filament\App\Widgets\WelcomeWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('app')
            ->path('app')
            ->login()
            ->brandName(function () {
                try {
                    $name = tenant('company_name');
                    return $name ? 'ProcureThai / ' . $name : 'ProcureThai';
                } catch (\Throwable) {
                    return 'ProcureThai';
                }
            })
            ->brandLogo(function () {
                try {
                    $logo = tenant('company_logo');
                    return $logo
                        ? \Illuminate\Support\Facades\Storage::disk('public')->url($logo)
                        : null;
                } catch (\Throwable) {
                    return null;
                }
            })
            ->brandLogoHeight('3.5rem')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label('โปรไฟล์ของฉัน')
                    ->icon('heroicon-o-user-circle')
                    ->url('/app/profile-page'),

                MenuItem::make()
                    ->label(fn () => session('locale', 'th') === 'th' ? '🇬🇧 Switch to English' : '🇹🇭 ภาษาไทย')
                    ->icon('heroicon-o-language')
                    ->url(fn () => route('locale.switch', [
                        'lang' => session('locale', 'th') === 'th' ? 'en' : 'th',
                    ])),
            ])
            ->discoverResources(in: app_path('Filament/App/Resources'), for: 'App\Filament\App\Resources')
            ->discoverPages(in: app_path('Filament/App/Pages'), for: 'App\Filament\App\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/App/Widgets'), for: 'App\Filament\App\Widgets')
            ->widgets([
                WelcomeWidget::class,
                \App\Filament\App\Widgets\ProcurementStatsWidget::class,
            ])
            ->middleware([
                // Resolve the tenant from the subdomain so tenant() works inside
                // panel pages. Must run before StartSession. No-op on central
                // domains via InitializeTenancyByDomain::$onFail (AppServiceProvider).
                \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
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
                \App\Http\Middleware\EnsureOnboardingComplete::class,
            ]);
    }
}
