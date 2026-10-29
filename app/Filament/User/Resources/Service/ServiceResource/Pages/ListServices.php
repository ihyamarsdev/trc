<?php

namespace App\Filament\User\Resources\Service\ServiceResource\Pages;

use App\Filament\User\Resources\Service\ServiceResource;
use Filament\Resources\Pages\ListRecords;

class ListServices extends ListRecords
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
