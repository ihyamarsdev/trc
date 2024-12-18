<?php

namespace App\Filament\User\Resources\Datacenter\Monitoring\SnbtJumsisResource\Pages;

use App\Filament\User\Resources\Datacenter\Monitoring\SnbtJumsisResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSnbtJumsis extends EditRecord
{
    protected static string $resource = SnbtJumsisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
