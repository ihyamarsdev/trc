<?php

namespace App\Filament\Resources\UserResource\Pages;

use Filament\Actions;
use App\Imports\UserImport;
use Filament\Actions\ImportAction;
use App\Filament\Imports\UserImporter;
use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->color("primary")
                ->use(UserImport::class),
        ];
    }
}
