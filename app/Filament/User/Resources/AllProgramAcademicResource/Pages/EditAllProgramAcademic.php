<?php

namespace App\Filament\User\Resources\AllProgramAcademicResource\Pages;

use App\Filament\User\Resources\AllProgramAcademicResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAllProgramAcademic extends EditRecord
{
    protected static string $resource = AllProgramAcademicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
