<?php

namespace App\Filament\User\Resources\Datacenter\Monitoring\AllJumsisResource\Pages;

use App\Filament\User\Resources\Datacenter\Monitoring\AllJumsisResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAllJumsis extends EditRecord
{
    protected static string $resource = AllJumsisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
