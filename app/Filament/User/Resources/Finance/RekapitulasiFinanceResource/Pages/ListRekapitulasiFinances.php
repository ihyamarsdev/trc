<?php

namespace App\Filament\User\Resources\Finance\RekapitulasiFinanceResource\Pages;

use App\Filament\User\Resources\Finance\RekapitulasiFinanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRekapitulasiFinances extends ListRecords
{
    protected static string $resource = RekapitulasiFinanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
