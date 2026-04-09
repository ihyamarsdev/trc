<?php

namespace App\Filament\User\Pages;

use App\Filament\User\Widgets\SalesForceStatsWidget;
use App\Filament\User\Widgets\SalesLeaderboard;
use Filament\Pages\Page;

class DashboardHome extends Page
{
    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-home';
    }

    protected string $view = 'filament.user.pages.dashboard-home';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Home';

    protected static ?string $title = 'Home';

    protected static ?string $slug = 'dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            SalesForceStatsWidget::class,
            SalesLeaderboard::class,

        ];
    }
}
