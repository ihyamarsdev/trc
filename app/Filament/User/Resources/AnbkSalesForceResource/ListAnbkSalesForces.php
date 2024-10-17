<?php

namespace App\Filament\User\Resources\AnbkSalesForceResource\Pages;

use App\Filament\User\Resources\AnbkSalesForceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAnbkSalesForces extends ListRecords
{
    protected static string $resource = AnbkSalesForceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
