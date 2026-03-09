<?php

namespace App\Providers\Filament;

use App\Http\Middleware\UpgradeToHttpsUnderNgrok;
use App\Livewire\DetailProfile;
use App\Livewire\EditProfile;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use FilipFonal\FilamentLogManager\FilamentLogManager;
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

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->passwordReset()
            ->login()
            ->font('Poppins')
            ->brandLogo(fn () => view('filament.admin.logo'))
            ->favicon(asset('images/logo.png'))
            ->databaseNotifications()
            ->sidebarWidth('15rem')
            ->unsavedChangesAlerts()
            ->breadcrumbs(false)
            ->colors([
                'danger' => Color::Rose,
                'gray' => Color::Gray,
                'info' => Color::Blue,
                'primary' => Color::Lime,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
                'yellow' => Color::Yellow,
                'blue' => Color::Blue,
                'green' => Color::Green,
                'red' => Color::Red,
                'indigo' => Color::Indigo,
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->navigationGroups([
                'Rekapitulation',
                'Management User',
                'Management Sekolah',
                'Settings',
            ])
            ->discoverResources(
                in: app_path('Filament/Resources'),
                for: 'App\\Filament\\Resources',
            )
            ->discoverPages(
                in: app_path('Filament/Pages'),
                for: 'App\\Filament\\Pages',
            )
            ->pages([Dashboard::class])
            ->discoverWidgets(
                in: app_path('Filament/Widgets'),
                for: 'App\\Filament\\Widgets',
            )
            ->widgets([])
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label(fn () => Auth::user()->name)
                    ->url(fn (): string => EditProfilePage::getUrl())
                    ->icon('heroicon-m-user-circle'),
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
            ->authMiddleware([Authenticate::class])
            ->plugins([
                FilamentShieldPlugin::make(),
                FilamentLogManager::make(),
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
                    //     directory: 'avatars', // image will be stored in 'storage/app/public/avatars
                    //     rules: 'mimes:jpeg,png|max:1024' //only accept jpeg and png files with a maximum size of 1MB
                    // )
                    ->customProfileComponents([
                        EditProfile::class,
                        DetailProfile::class,
                    ]),
                MobileBottomNav::make()
                    ->moreButton(false)
                    ->items([
                        MobileBottomNavItem::make('Admin Database')
                            ->icon('heroicon-o-building-library')
                            ->url('/admin/admin-database')
                            ->isActive(fn () => request()->is('admin/admin-database*')),

                        MobileBottomNavItem::make('Users')
                            ->icon('heroicon-o-user-group')
                            ->url('/admin/users')
                            ->isActive(fn () => request()->is('admin/users*')),

                        MobileBottomNavItem::make('Home')
                            ->icon('heroicon-o-home')
                            ->url('/admin')
                            ->isActive(fn () => request()->is('admin')),

                        MobileBottomNavItem::make('Roles')
                            ->icon('heroicon-o-shield-check')
                            ->url('/admin/shield/roles')
                            ->isActive(fn () => request()->is('admin/shield/roles*')),

                        MobileBottomNavItem::make('Log')
                            ->icon('heroicon-o-document-text')
                            ->url('/admin/log-manager')
                            ->isActive(fn () => request()->is('admin/log-manager*')),
                    ]),
            ]);
    }
}
