<?php

namespace App\Filament\User\Widgets;

use Filament\Actions\Action;
use App\Models\RegistrationData;
use Saade\FilamentFullCalendar\Actions;
use Saade\FilamentFullCalendar\Data\EventData;
use App\Filament\User\Resources\TimelineResource;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarWidget extends FullCalendarWidget
{
    // protected static string $view = 'filament.user.widgets.calendar-widget';
    // public Model | string | null $model = RegistrationData::class;


    public function config(): array
    {
        return [
            'firstDay' => 1,
            'headerToolbar' => [
                'left' => 'title',
                'center' => '',
                'right' => 'prevYear,nextYear',
            ],
            'footerToolbar' => [
                'left' => 'prev,next',
                'center' => 'today',
                'right' => 'dayGridMonth,dayGridWeek,dayGridDay',
            ],
            'titleFormat' => [
                ''
            ]
        ];
    }

    protected function viewAction(): Action
    {
        return Actions\ViewAction::make();
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
                    ->url(url: TimelineResource::getUrl(name: 'view', parameters: ['record' => $event]));


            })
            ->toArray();
    }
}
