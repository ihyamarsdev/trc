<?php

namespace App\Filament\User\Resources\TimelineResource\Pages;

use App\Filament\Components\Academic;
use App\Filament\User\Resources\TimelineResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewTimeline extends ViewRecord
{
    protected static string $resource = TimelineResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function infolist(Schema $infolist): Schema
    {
        $record = $this->record;

        return $infolist
            ->schema(Academic::infolist(record: $record));
    }
}
