<?php

namespace App\Filament\User\Resources\Activity\Pages;

use App\Filament\User\Resources\Activity\ActivityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditActivity extends EditRecord
{
    protected Width|string|null $maxWidth = Width::Full;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
