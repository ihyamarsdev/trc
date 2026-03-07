<?php

namespace App\Providers\Filament;

use App\Filament\User\Pages\DashboardHome;
use App\Filament\User\Widgets\SalesForceStatsWidget;
use App\Http\Middleware\UpgradeToHttpsUnderNgrok;
use App\Livewire\DetailProfile;
use App\Livewire\EditProfile;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Hammadzafar05\MobileBottomNav\MobileBottomNav;
use Hammadzafar05\MobileBottomNav\MobileBottomNavItem;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;
use Joaopaulolndev\FilamentEditProfile\Pages\EditProfilePage;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;
use Yebor974\Filament\RenewPassword\RenewPasswordPlugin;

class UserPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('user')
            ->path('')
            ->login()
            ->passwordReset()
            ->maxContentWidth('Full')
            ->font('Poppins')
            ->brandLogo(fn () => view('filament.user.logo'))
            ->favicon(asset('images/logo.png'))
            ->viteTheme('resources/css/filament/user/theme.css')
            ->databaseNotifications()
            ->databaseNotificationsPolling('2s')
            ->sidebarWidth('15rem')
            ->unsavedChangesAlerts()
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
                'Service',
                'Finance',
                'Rekap Datacenter',
                'Rekap Akademik',
                'Rekap Finance',
                'Data Kuning',
                'Data Biru',
            ])
            ->discoverResources(
                in: app_path('Filament/User/Resources'),
                for: 'App\\Filament\\User\\Resources',
            )
            ->discoverPages(
                in: app_path('Filament/User/Pages'),
                for: 'App\\Filament\\User\\Pages',
            )
            ->pages([
                // Dashboard::class,
                DashboardHome::class,
            ])
            ->userMenuItems([
                'profile' => Action::make('profile')
                    ->label(fn () => Auth::user()?->name ?? 'Profile')
                    ->url(fn (): string => EditProfilePage::getUrl())
                    ->icon('heroicon-m-user-circle')
                    ->sort(1),
            ])
            ->discoverWidgets(
                in: app_path('Filament/User/Widgets'),
                for: 'App\\Filament\\User\\Widgets',
            )
            ->widgets([SalesForceStatsWidget::class])
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
            ->authMiddleware([Authenticate::class])
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
                    ->customProfileComponents([
                        EditProfile::class,
                        DetailProfile::class,
                    ]),
                RenewPasswordPlugin::make()
                    ->forceRenewPassword()
                    ->timestampColumn(),
                MobileBottomNav::make()
                    ->moreButton(false)
                    ->items([
                        MobileBottomNavItem::make('Service')
                            ->icon('heroicon-o-wrench-screwdriver')
                            ->url('/database-service')
                            ->isActive(fn () => request()->is('database-service*')),

                        MobileBottomNavItem::make('Salesforce')
                            ->icon('heroicon-o-presentation-chart-line')
                            ->url('/database-salesforce')
                            ->isActive(fn () => request()->is('database-salesforce*')),

                        MobileBottomNavItem::make('Finance')
                            ->icon('heroicon-m-credit-card')
                            ->url('/database-finance')
                            ->isActive(fn () => request()->is('database-finance*')),

                        MobileBottomNavItem::make('Activity')
                            ->icon('heroicon-o-arrow-trending-up')
                            ->url('/activity')
                            ->isActive(fn () => request()->is('activity*')),

                        MobileBottomNavItem::make('Timeline')
                            ->icon('heroicon-o-clock')
                            ->url('/timeline')
                            ->isActive(fn () => request()->is('timeline*')),
                    ]),
            ]);
    }
}
