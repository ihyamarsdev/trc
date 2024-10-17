<?php

namespace App\Filament\Resources\CounselorCoordinatorResource\Pages;

use App\Filament\Resources\CounselorCoordinatorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCounselorCoordinator extends EditRecord
{
    protected static string $resource = CounselorCoordinatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
