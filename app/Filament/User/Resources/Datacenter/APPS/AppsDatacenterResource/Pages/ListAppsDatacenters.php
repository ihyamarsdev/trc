<?php

namespace App\Filament\User\Resources\Datacenter\APPS\AppsDatacenterResource\Pages;

use App\Filament\User\Resources\Datacenter\APPS\AppsDatacenterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAppsDatacenters extends ListRecords
{
    protected static string $resource = AppsDatacenterResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
