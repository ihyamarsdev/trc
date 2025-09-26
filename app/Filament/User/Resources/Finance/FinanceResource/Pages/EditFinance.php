<?php

namespace App\Filament\User\Resources\Finance\FinanceResource\Pages;

use App\Filament\User\Resources\Finance\FinanceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFinance extends EditRecord
{
    protected static string $resource = FinanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
