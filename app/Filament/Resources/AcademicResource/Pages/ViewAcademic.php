<?php

namespace App\Filament\Resources\AcademicResource\Pages;

use App\Filament\Resources\AcademicResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAcademic extends ViewRecord
{
    protected static string $resource = AcademicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
