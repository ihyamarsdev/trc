<?php

namespace App\Filament\User\Resources\Finance\ANBK\AnbkFinanceResource\Pages;

use App\Filament\User\Resources\Finance\ANBK\AnbkFinanceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAnbkFinance extends EditRecord
{
    protected static string $resource = AnbkFinanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
