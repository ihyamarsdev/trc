<?php

namespace App\Providers\Filament;

use App\Filament\User\Pages\DashboardHome;
use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use App\Livewire\EditProfile;
use Filament\Pages\Dashboard;
use App\Livewire\DetailProfile;
use Filament\Navigation\MenuItem;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Auth;
use Orion\FilamentGreeter\GreeterPlugin;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use App\Http\Middleware\UpgradeToHttpsUnderNgrok;
use App\Filament\User\Widgets\SalesForceStatsWidget;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Yebor974\Filament\RenewPassword\RenewPasswordPlugin;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Joaopaulolndev\FilamentEditProfile\Pages\EditProfilePage;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;

class UserPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('user')
            ->path('')
            ->login()
            ->passwordReset()
            ->maxContentWidth(MaxWidth::Full)
            ->font('Poppins')
            ->brandLogo(asset('images/logo.png'))
            ->favicon(asset('images/logo.png'))
            ->brandLogoHeight('8rem')
            ->viteTheme('resources/css/filament/user/theme.css')
            ->databaseNotifications()
            ->topbar(true)
            ->breadcrumbs(false)
            ->colors([
                'danger' => Color::Rose,
                'gray' => Color::Gray,
                'info' => Color::Red,
                'primary' => Color::Lime,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
                'yellow' => Color::Yellow,
                'blue' => Color::Blue,
                'green' => Color::Green,
                'red' => Color::Red,
            ])
            ->navigationGroups([
                'Salesforce',
                'Akademik & Teknisi',
                'Finance',
                'Rekap Datacenter',
                'Rekap Akademik',
                'Rekap Finance',
                'Data Kuning',
                'Data Biru'
            ])
            ->discoverResources(in: app_path('Filament/User/Resources'), for: 'App\\Filament\\User\\Resources')
            ->discoverPages(in: app_path('Filament/User/Pages'), for: 'App\\Filament\\User\\Pages')
            ->pages([
                // Dashboard::class,
                DashboardHome::class,
            ])
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label(fn () => Auth::user()->name)
                    ->url(fn (): string => EditProfilePage::getUrl())
                    ->icon('heroicon-m-user-circle')
                    ->sort(1),
            ])
            ->discoverWidgets(in: app_path('Filament/User/Widgets'), for: 'App\\Filament\\User\\Widgets')
            ->widgets([
                SalesForceStatsWidget::class,
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
                UpgradeToHttpsUnderNgrok::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                 FilamentFullCalendarPlugin::make()
                    ->schedulerLicenseKey('')
                    ->selectable()
                    ->timezone(config('app.timezone'))
                    ->locale(config('app.locale'))
                    ->plugins(['dayGrid', 'timeGrid'])
                    ->config([]),
                FilamentEditProfilePlugin::make()
                    ->slug('my-profile')
                    ->setTitle('My Profile')
                    ->setNavigationLabel('My Profile')
                    ->setNavigationGroup('Group Profile')
                    ->setIcon('heroicon-o-user')
                    ->setSort(10)
                    ->shouldRegisterNavigation(false)
                    ->shouldShowDeleteAccountForm(false)
                    ->shouldShowSanctumTokens(false)
                    ->shouldShowBrowserSessionsForm(false)
                    // ->shouldShowAvatarForm(
                    //     value: true,
                    //     directory: 'avatars',
                    //     rules: 'mimes:jpg,jpeg,png|max:1024'
                    // )
                    ->customProfileComponents([
                        EditProfile::class,
                        DetailProfile::class,
                    ]),
                GreeterPlugin::make()
                    ->message('Selamat Datang,')
                    ->name(text: fn () => Auth::user()->name)
                    ->title('BIG DREAM TRC : 1 JUTA SISWA / TAHUN, 100% BISA!!!')
                    ->avatar(size: 'w-16 h-16', enabled: true)
                    ->sort(-1)
                    ->columnSpan('full'),
                (new RenewPasswordPlugin())
                    ->forceRenewPassword()
                    ->timestampColumn()
            ]);
    }
}
