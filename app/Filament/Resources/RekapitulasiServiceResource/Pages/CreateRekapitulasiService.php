<?php

namespace App\Filament\Resources\RekapitulasiServiceResource\Pages;

use App\Filament\Resources\RekapitulasiServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRekapitulasiService extends CreateRecord
{
    protected static string $resource = RekapitulasiServiceResource::class;

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
