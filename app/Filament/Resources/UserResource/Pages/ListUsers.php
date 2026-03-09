<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Imports\UserImport;
use Filament\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Resources\Pages\ListRecords;

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
                    customiseActionUsing: fn (Action $action) => $action
                        ->color('secondary')
                        ->icon('heroicon-m-clipboard'),
                )
                ->validateUsing([
                    'name' => 'required',
                    'email' => 'required|email',
                ])
                ->color('primary')
                ->use(UserImport::class),
        ];
    }
}
