<?php

namespace App\Filament\User\Resources\Finance\ANBK\AnbkFinanceResource\Pages;

use App\Filament\User\Resources\Finance\ANBK\AnbkFinanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAnbkFinances extends ListRecords
{
    protected static string $resource = AnbkFinanceResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
