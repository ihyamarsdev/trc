<?php

namespace App\Filament\User\Resources\Salesforce\SNBT\SnbtSalesForceResource\Pages;

use App\Filament\User\Resources\Salesforce\SNBT\SnbtSalesForceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSnbtSalesForces extends ListRecords
{
    protected static string $resource = SnbtSalesForceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
