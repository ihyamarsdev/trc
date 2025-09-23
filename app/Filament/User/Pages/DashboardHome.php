<?php

namespace App\Filament\User\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Orion\FilamentGreeter\GreeterPlugin;
use App\Filament\User\Widgets\SalesForceStatsWidget;
use App\Filament\User\Widgets\SalesLeaderboard;
use App\Filament\User\Widgets\UserStatusColorsWidget;

class DashboardHome extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.user.pages.dashboard-home';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Home';

    protected static ?string $title = 'Home';

    protected function getHeaderWidgets(): array
    {
        return [
            SalesForceStatsWidget::class,
            SalesLeaderboard::class,

        ];
    }

}
