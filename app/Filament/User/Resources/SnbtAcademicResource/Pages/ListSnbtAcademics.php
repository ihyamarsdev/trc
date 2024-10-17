<?php

namespace App\Filament\User\Resources\SnbtAcademicResource\Pages;

use App\Filament\User\Resources\SnbtAcademicResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSnbtAcademics extends ListRecords
{
    protected static string $resource = SnbtAcademicResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
