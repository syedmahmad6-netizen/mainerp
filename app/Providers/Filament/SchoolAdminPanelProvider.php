<?php

namespace App\Providers\Filament;

use App\Filament\SchoolAdmin\Widgets\SchoolOverviewWidget;
use App\Http\Middleware\IdentifyTenant;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class SchoolAdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('school-admin')
            ->path('admin')
            ->login()
            ->brandName('Gnosis Education Systems')
            ->colors(['primary' => Color::Emerald])
            ->discoverResources(
                in: app_path('Filament/SchoolAdmin/Resources'),
                for: 'App\\Filament\\SchoolAdmin\\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/SchoolAdmin/Pages'),
                for: 'App\\Filament\\SchoolAdmin\\Pages'
            )
            ->pages([Pages\Dashboard::class])
            ->discoverWidgets(
                in: app_path('Filament/SchoolAdmin/Widgets'),
                for: 'App\\Filament\\SchoolAdmin\\Widgets'
            )
            ->widgets([
                SchoolOverviewWidget::class,
            ])
            ->navigationGroups([
                NavigationGroup::make('Academic Setup'),
                NavigationGroup::make('People')
                    ->collapsed(),
                NavigationGroup::make('Academics'),
                NavigationGroup::make('Finance')
                    ->collapsed(),
                NavigationGroup::make('Communication')
                    ->collapsed(),
                NavigationGroup::make('Reports')
                    ->collapsed(),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                IdentifyTenant::class,
            ])
            ->authMiddleware([Authenticate::class]);
    }

    public function boot(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_START,
            fn (): string => Blade::render('<x-impersonation-banner />')
        );
    }
}