<?php

namespace App\Filament\User\Resources\SnbtFinanceResource\Pages;

use App\Filament\User\Resources\SnbtFinanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSnbtFinances extends ListRecords
{
    protected static string $resource = SnbtFinanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }
}
