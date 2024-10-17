<?php

namespace App\Filament\User\Resources\AppsAcademicResource\Pages;

use App\Filament\User\Resources\AppsAcademicResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAppsAcademics extends ListRecords
{
    protected static string $resource = AppsAcademicResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
