<?php

namespace App\Filament\User\Resources\Datacenter\SNBT\SnbtDatacenterResource\Pages;

use App\Filament\User\Resources\Datacenter\SNBT\SnbtDatacenterResource;
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
