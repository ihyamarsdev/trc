<?php

namespace App\Filament\User\Resources\Datacenter\Monitoring\AppsJumsisResource\Pages;

use App\Filament\User\Resources\Datacenter\Monitoring\AppsJumsisResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppsJumsis extends EditRecord
{
    protected static string $resource = AppsJumsisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
