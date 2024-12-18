<?php

namespace App\Filament\User\Resources\Finance\APPS\AppsFinanceResource\Pages;

use App\Filament\User\Resources\Finance\APPS\AppsFinanceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppsFinance extends EditRecord
{
    protected static string $resource = AppsFinanceResource::class;

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
