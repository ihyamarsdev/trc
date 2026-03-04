<?php

namespace App\Filament\Resources\Users\Pages;

use Filament\Actions\CreateAction;
use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions\Action;
use Filament\Actions;
use App\Imports\UserImport;
use Filament\Actions\ImportAction;
use App\Filament\Imports\UserImporter;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ExcelImportAction::make()
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
