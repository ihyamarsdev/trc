<?php

namespace App\Filament\User\Resources\Finance\SNBT\SnbtFinanceResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Tables;
use App\Models\RegistrationData;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\User\Resources\Finance\SNBT\SnbtFinanceResource;

class ViewSnbtFinance extends ViewRecord
{
    protected static string $resource = SnbtFinanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
                Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Action::make('spk_rasyidu')
                        ->label('SPK RASYIDUU')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(fn (RegistrationData $record) => route('rasyidu.snbt.download', $record))
                        ->openUrlInNewTab(),
                    Action::make('spk_edunesia')
                        ->label('SPK EDUNESIA')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(fn (RegistrationData $record) => route('edunesia.snbt.download', $record))
                        ->openUrlInNewTab(),
                    Action::make('kwitaansi_rasyidu')
                        ->label('Kwitansi RASYIDUU')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(fn (RegistrationData $record) => route('rasyidu.kwitansi.download', $record))
                        ->openUrlInNewTab(),
                    Action::make('kwitaansi_rasyidu')
                        ->label('Kwitansi EDUNESIA')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(fn (RegistrationData $record) => route('edunesia.kwitansi.download', $record))
                        ->openUrlInNewTab(),
                    Action::make('invoice_edunesia')
                        ->label('Invoice EDUNESIA')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(fn (RegistrationData $record) => route('edunesia.invoice.download', $record))
                        ->openUrlInNewTab()
                ])
                ->label('Detail')
                ->color('primary')
                ->button(),
            ];
    }
}
