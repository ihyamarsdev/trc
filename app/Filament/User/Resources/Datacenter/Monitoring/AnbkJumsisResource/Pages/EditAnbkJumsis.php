<?php

namespace App\Filament\User\Resources\Datacenter\Monitoring\AnbkJumsisResource\Pages;

use App\Filament\User\Resources\Datacenter\Monitoring\AnbkJumsisResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAnbkJumsis extends EditRecord
{
    protected static string $resource = AnbkJumsisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
