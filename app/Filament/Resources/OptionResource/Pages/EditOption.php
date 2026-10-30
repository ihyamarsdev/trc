<?php

namespace App\Filament\Resources\OptionResource\Pages;

use App\Filament\Resources\OptionResource;
use App\Models\Option;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOption extends EditRecord
{
    protected static string $resource = OptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(fn (Actions\DeleteAction $action, Option $record) => OptionResource::cancelIfInUse($action, $record)),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return OptionResource::getUrl('index', ['activeTab' => $this->record->group->value]);
    }
}
