<?php

namespace App\Filament\User\Resources\ServiceProgressDatacenterResource\Pages;

use App\Filament\User\Resources\ServiceProgressDatacenterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditServiceProgressDatacenter extends EditRecord
{
    protected static string $resource = ServiceProgressDatacenterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
