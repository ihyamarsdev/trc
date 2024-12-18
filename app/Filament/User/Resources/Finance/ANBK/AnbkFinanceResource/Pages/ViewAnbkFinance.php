<?php

namespace App\Filament\User\Resources\Finance\ANBK\AnbkFinanceResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables;
use App\Filament\User\Resources\Finance\ANBK\AnbkFinanceResource;
use App\Models\RegistrationData;

class ViewAnbkFinance extends ViewRecord
{
    protected static string $resource = AnbkFinanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
                Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Action::make('spk_rasyidu')
                        ->label('SPK RASYIDUU')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(fn (RegistrationData $record) => route('rasyidu.anbk.download', $record))
                        ->openUrlInNewTab(),
                    Action::make('spk_edunesia')
                        ->label('SPK EDUNESIA')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(fn (RegistrationData $record) => route('edunesia.anbk.download', $record))
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
                    Action::make('invoice_rasyidu')
                        ->label('Invoice RASYIDU')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(fn (RegistrationData $record) => route('rasyidu.invoice.download', $record))
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
