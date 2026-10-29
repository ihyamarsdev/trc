<?php

namespace App\Filament\User\Resources\Service\ServiceResource\Pages;

use App\Filament\User\Resources\Service\ServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;

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
