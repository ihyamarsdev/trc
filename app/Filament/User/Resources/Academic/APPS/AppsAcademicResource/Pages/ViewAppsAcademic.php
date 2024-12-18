<?php

namespace App\Filament\User\Resources\Academic\APPS\AppsAcademicResource\Pages;

use App\Filament\User\Resources\Academic\APPS\AppsAcademicResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAppsAcademic extends ViewRecord
{
    protected static string $resource = AppsAcademicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
