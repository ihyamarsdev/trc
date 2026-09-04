<?php

namespace App\Filament\User\Resources\Academic\AcademicResource\Pages;

use App\Filament\User\Resources\Academic\AcademicResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAcademic extends CreateRecord
{
    protected static string $resource = AcademicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('createHeader')
                ->label(__('filament-panels::resources/pages/create-record.form.actions.create.label'))
                ->action(fn () => $this->create())
                ->keyBindings(['mod+s']),
        ];
    }
}
