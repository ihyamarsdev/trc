<?php

namespace App\Filament\User\Resources\SnbtAcademicResource\Pages;

use App\Filament\User\Resources\SnbtAcademicResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSnbtAcademic extends EditRecord
{
    protected static string $resource = SnbtAcademicResource::class;

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
