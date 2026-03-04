<?php

namespace App\Filament\User\Widgets;

use App\Filament\User\Resources\Timelines\TimelineResource;
use App\Models\RegistrationData;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Saade\FilamentFullCalendar\Actions\ViewAction;
use Saade\FilamentFullCalendar\Data\EventData;
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
                '',
            ],
        ];
    }

    protected function viewAction(): Action
    {
        return ViewAction::make();
    }

    public function fetchEvents(array $fetchInfo): array
    {

        return RegistrationData::query()
            ->where('implementation_estimate', '>=', $fetchInfo['start'])
            ->where('implementation_estimate', '<=', $fetchInfo['end'])
            ->unless(
                auth()->user()->hasRole('admin'), // Selama BUKAN admin...
                fn (Builder $q) => $q->when(
                    auth()->user()->hasRole('sales'), // ...dan jika dia sales
                    fn ($subQ) => $subQ->where('users_id', auth()->id())
                )
            )
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
