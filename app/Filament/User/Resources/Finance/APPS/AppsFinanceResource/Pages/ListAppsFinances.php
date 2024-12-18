<?php

namespace App\Filament\User\Resources\Finance\APPS\AppsFinanceResource\Pages;

use App\Filament\User\Resources\Finance\APPS\AppsFinanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAppsFinances extends ListRecords
{
    protected static string $resource = AppsFinanceResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
