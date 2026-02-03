<?php

namespace App\Filament\Resources\UserResource\Pages;

use Filament\Actions;
use App\Imports\UserImport;
use Filament\Actions\ImportAction;
use App\Filament\Imports\UserImporter;
use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\Actions\Action;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->sampleExcel(
                    sampleData: [
                        ['name' => 'uya', 'email' => 'uya@gmail.com', 'roles' => 'admin'],
                        ['name' => 'ade', 'email' => 'ade@gmail.com', 'roles' => 'sales'],
                    ],
                    fileName: 'user_sample.csv',
                    sampleButtonLabel: 'Download Sample',
                    customiseActionUsing: fn (Action $action) =>
                        $action
                            ->color('secondary')
                            ->icon('heroicon-m-clipboard'),
                )
                ->validateUsing([
                    'name' => 'required',
                    'email' => 'required|email',
                ])
                ->color("primary")
                ->use(UserImport::class),
        ];
    }
}
