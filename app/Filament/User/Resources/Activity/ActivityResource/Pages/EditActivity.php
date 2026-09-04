<?php

namespace App\Filament\User\Resources\Activity\ActivityResource\Pages;

use App\Filament\User\Resources\Activity\ActivityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditActivity extends EditRecord
{
    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('saveHeader')
                ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->action(fn () => $this->save())
                ->keyBindings(['mod+s']),
            Actions\DeleteAction::make(),
        ];
    }
}
