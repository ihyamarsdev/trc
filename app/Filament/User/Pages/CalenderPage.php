<?php

namespace App\Filament\User\Pages;

use App\Filament\User\Widgets\CalendarWidget;
use Filament\Pages\Page;

class CalenderPage extends Page
{
    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-m-calendar-days';
    }

    protected static string $view = 'filament.user.pages.calender-page';

    protected static ?string $navigationLabel = 'Timeline';

    protected static ?string $title = 'Timeline Sekolah';

    protected static ?string $slug = 'timeline';

    protected static ?int $navigationSort = 2;

    protected function getHeaderWidgets(): array
    {
        return [
            CalendarWidget::class,
        ];
    }
}
