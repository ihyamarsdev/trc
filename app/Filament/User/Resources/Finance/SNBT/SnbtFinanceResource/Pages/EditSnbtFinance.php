<?php

namespace App\Filament\User\Resources\Finance\SNBT\SnbtFinanceResource\Pages;

use App\Filament\User\Resources\Finance\SNBT\SnbtFinanceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSnbtFinance extends EditRecord
{
    protected static string $resource = SnbtFinanceResource::class;

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
