<?php

namespace App\Filament\User\Resources\Datacenter\Monitoring\MonthJumsisResource\Pages;

use App\Filament\User\Resources\Datacenter\Monitoring\MonthJumsisResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMonthJumsis extends EditRecord
{
    protected static string $resource = MonthJumsisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
