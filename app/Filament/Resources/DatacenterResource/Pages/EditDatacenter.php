<?php

namespace App\Filament\Resources\DatacenterResource\Pages;

use App\Filament\Resources\DatacenterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDatacenter extends EditRecord
{
    protected static string $resource = DatacenterResource::class;

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
