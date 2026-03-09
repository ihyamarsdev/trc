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
            Actions\DeleteAction::make(),
        ];
    }
}
