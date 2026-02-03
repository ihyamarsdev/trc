<?php

namespace App\Filament\User\Resources\TimelineResource\Pages;

use App\Filament\User\Resources\TimelineResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTimelines extends ListRecords
{
    protected static string $resource = TimelineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
