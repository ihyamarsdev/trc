<?php

namespace App\Filament\User\Resources\AppsSalesForceResource\Pages;

use App\Filament\User\Resources\AppsSalesForceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAppsSalesForces extends ListRecords
{
    protected static string $resource = AppsSalesForceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
