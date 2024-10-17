<?php

namespace App\Filament\User\Resources\SnbtDatacenterResource\Pages;

use App\Filament\User\Resources\SnbtDatacenterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSnbtDatacenters extends ListRecords
{
    protected static string $resource = SnbtDatacenterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }
}
