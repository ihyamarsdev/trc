<?php

namespace App\Filament\User\Widgets;

use App\Models\RegistrationData;
use App\Filament\Components\Academic;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Data\EventData;
use App\Filament\User\Resources\Academic\AcademicResource;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use App\Filament\User\Resources\Academic\ANBK\AnbkAcademicResource;
use App\Filament\User\Resources\Academic\APPS\AppsAcademicResource;
use App\Filament\User\Resources\Academic\SNBT\SnbtAcademicResource;
use App\Filament\User\Resources\Academic\AcademicResource as AcademicAcademicResource;

class CalendarWidget extends FullCalendarWidget
{
    // protected static string $view = 'filament.user.widgets.calendar-widget';
    // public Model | string | null $model = RegistrationData::class;


    public function config(): array
    {
        return [
            'firstDay' => 1,
            'headerToolbar' => [
                'left' => 'prev,next today',
                'center' => 'title',
                'right' => 'dayGridYear,dayGridMonth,dayGridWeek,dayGridDay',
            ],
        ];
    }

    public function fetchEvents(array $fetchInfo): array
    {

        return RegistrationData::query()
            ->where('date_register', '>=', $fetchInfo['start'])
            ->where('implementation_estimate', '<=', $fetchInfo['end'])
            ->get()
            ->map(function (RegistrationData $event) {

                return EventData::make()
                    ->id($event->id)
                    ->title($event->schools)
                    ->start($event->implementation_estimate)
                    ->end($event->implementation_estimate)
                    ->url(url: AcademicResource::getUrl(name: 'view', parameters: ['record' => $event]), shouldOpenUrlInNewTab: true);
                
                
            })
            ->toArray();
    }
}
