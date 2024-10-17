<?php

namespace App\Filament\User\Resources\AllProgramAcademicResource\Pages;

use App\Filament\User\Resources\AllProgramAcademicResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAllProgramAcademics extends ListRecords
{
    protected static string $resource = AllProgramAcademicResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
