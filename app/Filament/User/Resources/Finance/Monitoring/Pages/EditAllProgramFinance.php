<?php

namespace App\Filament\User\Resources\Finance\Monitoring\Pages;

use App\Filament\User\Resources\Finance\Monitoring\AllProgramFinanceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditAllProgramFinance extends EditRecord
{
    protected Width|string|null $maxWidth = Width::Full;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected static string $resource = AllProgramFinanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
