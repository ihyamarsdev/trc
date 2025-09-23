<?php

namespace App\Filament\User\Pages;

use Filament\Pages\Page;
use App\Filament\User\Widgets\CalendarWidget;

class CalenderPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-m-calendar-days';

    protected static string $view = 'filament.user.pages.calender-page';

    protected static ?string $navigationLabel = 'Timeline';

    protected static ?int $navigationSort = 2;

    protected function getHeaderWidgets(): array
    {
        return [
            CalendarWidget::class
        ];
    }
}
