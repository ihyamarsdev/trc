<?php

namespace App\Filament\User\Widgets;

use App\Models\RegistrationData;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\AcademicResource;
use Saade\FilamentFullCalendar\Data\EventData;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use App\Filament\User\Resources\Academic\ANBK\AnbkAcademicResource;
use App\Filament\User\Resources\Academic\APPS\AppsAcademicResource;
use App\Filament\User\Resources\Academic\SNBT\SnbtAcademicResource;

class CalendarWidget extends FullCalendarWidget
{
    // protected static string $view = 'filament.user.widgets.calendar-widget';

    public function fetchEvents(array $fetchInfo): array
    {

        return RegistrationData::query()
        ->where('date_register', '>=', $fetchInfo['start'])
        ->where('implementation_estimate', '<=', $fetchInfo['end'])
        ->get()
        ->map(function (RegistrationData $event) {
            // Petakan program -> Resource class
            $resourceMap = [
                'ANBK' => AnbkAcademicResource::class,
                'APPS' => AppsAcademicResource::class,
                'SNBT' => SnbtAcademicResource::class,
            ];

            $program = strtoupper($event->type ?? ''); // ganti 'program' jika nama kolom berbeda
            $resourceClass = $resourceMap[$program] ?? null;

            $url = $resourceClass
                ? $resourceClass::getUrl(name: 'view', parameters: ['record' => $event])
                : null; // fallback: bisa diarahkan ke halaman umum bila perlu

            return EventData::make()
                ->id($event->id)
                ->title($event->schools)
                ->start($event->implementation_estimate)
                ->end($event->implementation_estimate)
                ->url(url: $url, shouldOpenUrlInNewTab: true);
        })
        ->toArray();
    }
}
