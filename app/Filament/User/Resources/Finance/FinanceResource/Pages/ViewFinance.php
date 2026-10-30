<?php

namespace App\Filament\User\Resources\Finance\FinanceResource\Pages;

use App\Filament\Components\Finance;
use App\Filament\User\Resources\Finance\FinanceResource;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewFinance extends ViewRecord
{
    protected static string $resource = FinanceResource::class;

    /**
     * SPK, kwitansi and invoice are previewed, filled in and downloaded from the "Dokumen" tab.
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $record = $this->record;

        return $infolist
            ->schema(Finance::infolist(record: $record));
    }
}
