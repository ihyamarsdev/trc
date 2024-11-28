<?php

namespace App\Filament\User\Resources\DifferenceAcademicResource\Pages;

use App\Filament\User\Resources\DifferenceAcademicResource;
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
