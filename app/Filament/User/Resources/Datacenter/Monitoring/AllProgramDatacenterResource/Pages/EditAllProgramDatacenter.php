<?php

namespace App\Filament\User\Resources\Datacenter\Monitoring\AllProgramDatacenterResource\Pages;

use App\Filament\User\Resources\Datacenter\Monitoring\AllProgramDatacenterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAllProgramDatacenter extends EditRecord
{
    protected static string $resource = AllProgramDatacenterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
