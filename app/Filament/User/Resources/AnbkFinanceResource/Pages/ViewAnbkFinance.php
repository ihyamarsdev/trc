<?php

namespace App\Filament\User\Resources\AnbkFinanceResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\User\Resources\AnbkFinanceResource;
use App\Models\RegistrationData;

class ViewAnbkFinance extends ViewRecord
{
    protected static string $resource = AnbkFinanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
                Actions\EditAction::make(),
                Action::make('Print Invoice')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (RegistrationData $record) => route('finance.invoice.download', $record))
                    ->openUrlInNewTab()
            ];
    }
}
