<?php

namespace App\Filament\User\Resources\AnbkAcademicResource\Pages;

use App\Filament\User\Resources\AnbkAcademicResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAnbkAcademic extends ViewRecord
{
    protected static string $resource = AnbkAcademicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
