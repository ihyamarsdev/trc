<?php

namespace App\Filament\User\Resources\TimelineResource\Pages;

use App\Filament\User\Resources\TimelineResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTimeline extends CreateRecord
{
    protected static string $resource = TimelineResource::class;

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
