<?php

namespace App\Filament\User\Pages;

use App\Filament\User\Widgets\CalendarWidget;
use Filament\Pages\Page;

class CalenderPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-m-calendar-days';

    protected string $view = 'filament.user.pages.calender-page';

    protected static ?string $navigationLabel = 'Timeline';

    protected static ?string $title = 'Timeline Sekolah';

    protected static ?string $slug = 'timeline';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('ViewAny:TimelineResource') ?? false;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CalendarWidget::class,
        ];
    }
}
