<?php

namespace App\Filament\User\Resources\TimelineResource\Pages;

use Filament\Actions;
use Filament\Infolists\Infolist;
use App\Filament\Components\Academic;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\User\Resources\TimelineResource;

class ViewTimeline extends ViewRecord
{
    protected static string $resource = TimelineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }


    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $record = $this->record;
        return $infolist
            ->schema(Academic::infolist(record: $record));
    }
}
