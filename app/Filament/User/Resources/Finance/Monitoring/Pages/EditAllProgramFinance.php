<?php

namespace App\Filament\User\Resources\Finance\Monitoring\Pages;

use App\Filament\User\Resources\Finance\Monitoring\AllProgramFinanceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAllProgramFinance extends EditRecord
{
    protected static string $resource = AllProgramFinanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
