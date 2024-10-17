<?php

namespace App\Filament\Resources\CounselorCoordinatorResource\Pages;

use App\Filament\Resources\CounselorCoordinatorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCounselorCoordinators extends ListRecords
{
    protected static string $resource = CounselorCoordinatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
