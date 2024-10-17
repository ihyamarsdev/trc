<?php

namespace App\Filament\User\Resources\AnbkDatacenterResource\Pages;

use App\Filament\User\Resources\AnbkDatacenterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAnbkDatacenters extends ListRecords
{
    protected static string $resource = AnbkDatacenterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }
}
