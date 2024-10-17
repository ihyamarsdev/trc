<?php

namespace App\Filament\User\Resources\AllProgramFinanceResource\Pages;

use App\Filament\User\Resources\AllProgramFinanceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAllProgramFinance extends EditRecord
{
    protected static string $resource = AllProgramFinanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
