<?php

namespace App\Filament\User\Resources\ServiceProgressDatacenterResource\Pages;

use App\Filament\User\Resources\ServiceProgressDatacenterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServiceProgressDatacenters extends ListRecords
{
    protected static string $resource = ServiceProgressDatacenterResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
