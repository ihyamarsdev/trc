<?php

namespace App\Filament\Resources\ProctorsResource\Pages;

use App\Filament\Resources\ProctorsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProctors extends EditRecord
{
    protected static string $resource = ProctorsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
