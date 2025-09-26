<?php

namespace App\Filament\User\Resources\Salesforce\SalesResource\Pages;

use App\Filament\User\Resources\Salesforce\SalesResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSales extends ViewRecord
{
    protected static string $resource = SalesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
