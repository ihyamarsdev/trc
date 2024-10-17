<?php

namespace App\Filament\User\Resources\AnbkAcademicResource\Pages;

use App\Filament\User\Resources\AnbkAcademicResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAnbkAcademic extends EditRecord
{
    protected static string $resource = AnbkAcademicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
