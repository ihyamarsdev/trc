<?php

namespace App\Filament\User\Resources\Finance\FinanceResource\Pages;

use App\Filament\User\Resources\Finance\FinanceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFinance extends CreateRecord
{
    protected static string $resource = FinanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('createHeader')
                ->label(__('filament-panels::resources/pages/create-record.form.actions.create.label'))
                ->action(fn () => $this->create())
                ->keyBindings(['mod+s']),
        ];
    }
}
