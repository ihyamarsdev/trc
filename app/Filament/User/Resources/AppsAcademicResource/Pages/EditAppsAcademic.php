<?php

namespace App\Filament\User\Resources\AppsAcademicResource\Pages;

use App\Filament\User\Resources\AppsAcademicResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppsAcademic extends EditRecord
{
    protected static string $resource = AppsAcademicResource::class;

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
