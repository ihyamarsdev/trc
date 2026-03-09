<?php

namespace App\Filament\User\Resources\Finance\Monitoring\Pages;

use App\Filament\User\Resources\Finance\Infolists\FinanceInfolist;
use App\Filament\User\Resources\Finance\Monitoring\RekapitulasiFinanceResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewRekapitulasiFinance extends ViewRecord
{
    protected static string $resource = RekapitulasiFinanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return FinanceInfolist::configure($schema, $this->record);
    }
}
