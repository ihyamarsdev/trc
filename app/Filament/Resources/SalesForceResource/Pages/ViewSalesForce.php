<?php

namespace App\Filament\Resources\SalesForceResource\Pages;

use App\Filament\Resources\SalesForceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSalesForce extends ViewRecord
{
    protected static string $resource = SalesForceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
