<?php

namespace App\Filament\User\Resources\Timelines\Pages;

use App\Filament\User\Resources\Timelines\Infolists\TimelineInfolist;
use App\Filament\User\Resources\Timelines\TimelineResource;
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

    public function infolist(Schema $schema): Schema
    {
        return TimelineInfolist::configure($schema, $this->record);
    }
}
