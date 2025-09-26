<?php

namespace App\Filament\User\Resources\Academic\AcademicResource\Pages;

use App\Filament\User\Resources\Academic\AcademicResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAcademic extends EditRecord
{
    protected static string $resource = AcademicResource::class;

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
