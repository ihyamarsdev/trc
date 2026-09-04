<?php

namespace App\Filament\Resources\RekapitulasiServiceResource\Pages;

use App\Filament\Resources\RekapitulasiServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRekapitulasiService extends EditRecord
{
    protected static string $resource = RekapitulasiServiceResource::class;

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
