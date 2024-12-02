<?php

namespace App\Filament\User\Resources\AllProgramAcademicResource\Pages;

use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Support\Enums\IconPosition;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\User\Resources\AllProgramAcademicResource;

class ListAllProgramAcademics extends ListRecords
{
    protected static string $resource = AllProgramAcademicResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
