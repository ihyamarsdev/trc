<?php

namespace App\Filament\User\Resources\Finance\Monitoring\Pages;

use App\Filament\User\Resources\Finance\Monitoring\RekapitulasiFinanceResource;
use Filament\Resources\Pages\ListRecords;

class ListRekapitulasiFinances extends ListRecords
{
    protected static string $resource = RekapitulasiFinanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
