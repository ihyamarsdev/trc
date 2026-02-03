<?php

namespace App\Filament\User\Resources\Academic\AcademicResource\Pages;

use App\Filament\User\Resources\Academic\AcademicResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAcademics extends ListRecords
{
    protected static string $resource = AcademicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }
}
