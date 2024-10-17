<?php

namespace App\Filament\Resources\CurriculumDeputiesResource\Pages;

use App\Filament\Resources\CurriculumDeputiesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCurriculumDeputies extends EditRecord
{
    protected static string $resource = CurriculumDeputiesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
