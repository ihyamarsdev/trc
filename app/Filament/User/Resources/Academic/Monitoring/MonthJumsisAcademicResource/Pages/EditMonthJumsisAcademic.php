<?php

namespace App\Filament\User\Resources\Academic\Monitoring\MonthJumsisAcademicResource\Pages;

use App\Filament\User\Resources\Academic\Monitoring\MonthJumsisAcademicResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMonthJumsisAcademic extends EditRecord
{
    protected static string $resource = MonthJumsisAcademicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
