<?php

namespace App\Filament\Resources\CurriculumDeputiesResource\Pages;

use App\Filament\Resources\CurriculumDeputiesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCurriculumDeputies extends CreateRecord
{
    protected static string $resource = CurriculumDeputiesResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
