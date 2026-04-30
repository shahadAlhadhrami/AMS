<?php

namespace App\Providers\Filament;

use App\Http\Middleware\EnsurePanelAccess;
use Illuminate\Support\HtmlString;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class StudentPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('student')
            ->path('student')
            ->login()
            ->passwordReset()
            ->profile()
            ->multiFactorAuthentication([
                AppAuthentication::make()->recoverable(),
            ])
            ->viteTheme('resources/css/filament/student/theme.css')
            ->brandName('AMS')
            ->brandLogo(new HtmlString('<style>@import url("https://fonts.googleapis.com/css2?family=Anta&display=swap");</style><div style="width:100%; text-align:center;"><span style="font-family: \'Anta\', sans-serif; font-weight:400; letter-spacing:0.08em; font-size:2.5rem; line-height:1; color:#d97706;">AMS</span></div>'))
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Student/Resources'), for: 'App\\Filament\\Student\\Resources')
            ->discoverPages(in: app_path('Filament/Student/Pages'), for: 'App\\Filament\\Student\\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Student/Widgets'), for: 'App\\Filament\\Student\\Widgets')
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
                EnsurePanelAccess::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
