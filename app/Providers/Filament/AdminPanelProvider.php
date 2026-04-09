<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Pages;
use Filament\Panel;
use Filament\Support\Colors\Color;

class AdminPanelProvider extends BasePanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $plugins = [
            $this->makeProfilePlugin(),
        ];

        $logManagerPluginClass = '\\FilipFonal\\FilamentLogManager\\FilamentLogManager';

        if (class_exists($logManagerPluginClass)) {
            array_unshift($plugins, $logManagerPluginClass::make());
        }

        $greeterPlugin = $this->makeGreeterPlugin(
            'Satu-satunya cara untuk melakukan pekerjaan hebat yaitu dengan mencintai apa yang sedang kamu lakukan.'
        );

        if ($greeterPlugin !== null) {
            $plugins[] = $greeterPlugin;
        }

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->passwordReset()
            ->login()
            ->font('Poppins')
            ->brandLogo(asset('images/logo.png'))
            ->favicon(asset('images/logo.png'))
            ->brandLogoHeight('8rem')
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
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([

            ])
            ->userMenuItems([
                'profile' => $this->makeProfileMenuItem(),
            ])
            ->middleware($this->panelMiddleware())
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins($plugins);
    }
}
