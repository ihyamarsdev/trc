<?php

namespace App\Filament\User\Resources\Finance\Monitoring\Pages;

use App\Filament\Components\Finance;
use App\Filament\User\Resources\Finance\Monitoring\RekapitulasiFinanceResource;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewRekapitulasiFinance extends ViewRecord
{
    protected static string $resource = RekapitulasiFinanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema(Finance::infolist($this->record));
    }
}
