<?php

namespace App\Filament\User\Resources\Academic\SNBT\SnbtAcademicResource\Pages;

use App\Filament\User\Resources\Academic\SNBT\SnbtAcademicResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSnbtAcademic extends ViewRecord
{
    protected static string $resource = SnbtAcademicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
