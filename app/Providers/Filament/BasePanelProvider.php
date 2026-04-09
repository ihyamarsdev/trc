<?php

namespace App\Providers\Filament;

use App\Http\Middleware\UpgradeToHttpsUnderNgrok;
use App\Livewire\DetailProfile;
use App\Livewire\EditProfile;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\PanelProvider;
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

abstract class BasePanelProvider extends PanelProvider
{
    protected function panelMiddleware(): array
    {
        return [
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
        ];
    }

    protected function makeProfilePlugin(): FilamentEditProfilePlugin
    {
        return FilamentEditProfilePlugin::make()
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
            ]);
    }

    protected function makeProfileMenuItem(?int $sort = null): MenuItem
    {
        $menuItem = MenuItem::make()
            ->label(fn () => Auth::user()->name)
            ->url(fn (): string => EditProfilePage::getUrl())
            ->icon('heroicon-m-user-circle');

        if ($sort !== null) {
            $menuItem->sort($sort);
        }

        return $menuItem;
    }

    protected function makeGreeterPlugin(string $title): mixed
    {
        $greeterPluginClass = '\\Orion\\FilamentGreeter\\GreeterPlugin';

        if (! class_exists($greeterPluginClass)) {
            return null;
        }

        return $greeterPluginClass::make()
            ->message('Selamat Datang,')
            ->name(text: fn () => Auth::user()->name)
            ->title($title)
            ->avatar(size: 'w-16 h-16', enabled: true)
            ->sort(-1)
            ->columnSpan('full');
    }
}
