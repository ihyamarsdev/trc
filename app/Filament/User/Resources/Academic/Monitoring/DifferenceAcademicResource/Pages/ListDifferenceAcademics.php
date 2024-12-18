<?php

namespace App\Filament\User\Resources\Academic\Monitoring\DifferenceAcademicResource\Pages;

use App\Filament\User\Resources\Academic\Monitoring\DifferenceAcademicResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDifferenceAcademics extends ListRecords
{
    protected static string $resource = DifferenceAcademicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
