<?php

namespace App\Filament\Resources\RekapitulasiServiceResource\Pages;

use App\Filament\Resources\RekapitulasiServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRekapitulasiService extends EditRecord
{
    protected static string $resource = RekapitulasiServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
