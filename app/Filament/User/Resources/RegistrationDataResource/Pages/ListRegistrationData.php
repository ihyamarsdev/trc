<?php

namespace App\Filament\User\Resources\RegistrationDataResource\Pages;

use App\Filament\User\Resources\RegistrationDataResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRegistrationData extends ListRecords
{
    protected static string $resource = RegistrationDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }
}
