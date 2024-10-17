<?php

namespace App\Filament\Resources\CurriculumDeputiesResource\Pages;

use App\Filament\Resources\CurriculumDeputiesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCurriculumDeputies extends ListRecords
{
    protected static string $resource = CurriculumDeputiesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
