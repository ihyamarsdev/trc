<?php

namespace App\Filament\Resources\SalesForceResource\Pages;

use App\Filament\Resources\SalesForceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSalesForce extends EditRecord
{
    protected static string $resource = SalesForceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
