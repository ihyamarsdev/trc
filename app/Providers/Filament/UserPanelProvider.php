<?php

namespace App\Providers\Filament;

use App\Filament\User\Pages\DashboardHome;
use App\Filament\User\Widgets\SalesForceStatsWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Panel;
use Filament\Support\Colors\Color;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;
use Yebor974\Filament\RenewPassword\RenewPasswordPlugin;

class UserPanelProvider extends BasePanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $plugins = [];

        if (class_exists(FilamentFullCalendarPlugin::class)) {
            $plugins[] = FilamentFullCalendarPlugin::make()
                ->schedulerLicenseKey((string) config('filament.fullcalendar.scheduler_license_key', ''))
                ->selectable()
                ->timezone(config('app.timezone'))
                ->locale(config('app.locale'))
                ->plugins(['dayGrid', 'timeGrid'])
                ->config([]);
        }

        $plugins[] = $this->makeProfilePlugin();

        if (class_exists(RenewPasswordPlugin::class)) {
            $plugins[] = (new RenewPasswordPlugin)
                ->forceRenewPassword()
                ->timestampColumn();
        }

        $greeterPlugin = $this->makeGreeterPlugin('BIG DREAM TRC : 1 JUTA SISWA / TAHUN, 100% BISA!!!');

        if ($greeterPlugin !== null) {
            $plugins[] = $greeterPlugin;
        }

        return $panel
            ->id('user')
            ->path('')
            ->login()
            ->passwordReset()
            ->maxContentWidth('full')
            ->font('Poppins')
            ->brandLogo(asset('images/logo.png'))
            ->favicon(asset('images/logo.png'))
            ->brandLogoHeight('8rem')
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
            ->discoverResources(in: app_path('Filament/User/Resources'), for: 'App\\Filament\\User\\Resources')
            ->discoverPages(in: app_path('Filament/User/Pages'), for: 'App\\Filament\\User\\Pages')
            ->pages([
                // Dashboard::class,
                DashboardHome::class,
            ])
            ->userMenuItems([
                'profile' => $this->makeProfileMenuItem(1),
            ])
            ->discoverWidgets(in: app_path('Filament/User/Widgets'), for: 'App\\Filament\\User\\Widgets')
            ->widgets([
                SalesForceStatsWidget::class,
            ])
            ->middleware($this->panelMiddleware())
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins($plugins);
    }
}
