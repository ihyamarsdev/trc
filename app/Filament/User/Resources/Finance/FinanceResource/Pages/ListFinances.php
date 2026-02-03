<?php

namespace App\Filament\User\Resources\Finance\FinanceResource\Pages;

use App\Filament\User\Resources\Finance\FinanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFinances extends ListRecords
{
    protected static string $resource = FinanceResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
