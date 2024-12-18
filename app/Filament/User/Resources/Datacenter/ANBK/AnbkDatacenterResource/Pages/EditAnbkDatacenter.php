<?php

namespace App\Filament\User\Resources\Datacenter\ANBK\AnbkDatacenterResource\Pages;

use App\Filament\User\Resources\Datacenter\ANBK\AnbkDatacenterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAnbkDatacenter extends EditRecord
{
    protected static string $resource = AnbkDatacenterResource::class;

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
