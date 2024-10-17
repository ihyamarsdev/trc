<?php

namespace App\Filament\Resources\DevisionsResource\Pages;

use App\Filament\Resources\DevisionsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDevisions extends ListRecords
{
    protected static string $resource = DevisionsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
