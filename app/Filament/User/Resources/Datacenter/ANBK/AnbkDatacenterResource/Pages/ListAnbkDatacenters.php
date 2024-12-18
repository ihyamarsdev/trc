<?php

namespace App\Filament\User\Resources\Datacenter\ANBK\AnbkDatacenterResource\Pages;

use App\Filament\User\Resources\Datacenter\ANBK\AnbkDatacenterResource;
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
