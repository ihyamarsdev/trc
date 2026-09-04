<?php

namespace App\Filament\User\Resources\Activity\ActivityResource\Pages;

use App\Filament\User\Resources\Activity\ActivityResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateActivity extends CreateRecord
{
    protected static string $resource = ActivityResource::class;

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
