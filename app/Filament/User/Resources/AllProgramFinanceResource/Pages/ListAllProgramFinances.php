<?php

namespace App\Filament\User\Resources\AllProgramFinanceResource\Pages;

use App\Filament\User\Resources\AllProgramFinanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAllProgramFinances extends ListRecords
{
    protected static string $resource = AllProgramFinanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
