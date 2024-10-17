<?php

namespace App\Filament\User\Resources\AppsDatacenterResource\Pages;

use App\Filament\User\Resources\AppsDatacenterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppsDatacenter extends EditRecord
{
    protected static string $resource = AppsDatacenterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
