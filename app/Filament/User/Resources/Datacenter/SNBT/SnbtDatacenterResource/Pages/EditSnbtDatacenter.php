<?php

namespace App\Filament\User\Resources\Datacenter\SNBT\SnbtDatacenterResource\Pages;

use App\Filament\User\Resources\Datacenter\SNBT\SnbtDatacenterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSnbtDatacenter extends EditRecord
{
    protected static string $resource = SnbtDatacenterResource::class;

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
