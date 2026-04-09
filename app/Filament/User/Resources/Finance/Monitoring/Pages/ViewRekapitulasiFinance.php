<?php

namespace App\Filament\User\Resources\Finance\Monitoring\Pages;

use App\Filament\Components\Finance;
use App\Filament\User\Resources\Finance\Monitoring\RekapitulasiFinanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewRekapitulasiFinance extends ViewRecord
{
    protected static string $resource = RekapitulasiFinanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(),
        ];
    }

    public function infolist(Schema $infolist): Schema
    {
        return $infolist->schema(Finance::infolist($this->record));
    }
}
