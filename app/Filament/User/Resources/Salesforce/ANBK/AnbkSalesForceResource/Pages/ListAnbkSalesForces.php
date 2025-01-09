<?php

namespace App\Filament\User\Resources\Salesforce\ANBK\AnbkSalesForceResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\ImportAction;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\Rules\File;
use App\Imports\salesforce\ANBKImport;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Imports\SalesForceImporter;
use App\Filament\User\Resources\Salesforce\ANBK\AnbkSalesForceResource;

class ListAnbkSalesForces extends ListRecords
{
    protected static string $resource = AnbkSalesForceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Action::make('import')
                ->label('Import')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    FileUpload::make('attachment')
                ])
                ->action(function (array $data) {
                    $file = public_path('storage/' . $data['attachment']);

                    try {
                        Excel::import(new ANBKImport(), $file);

                        Notification::make()
                            ->title('Berhasil Import File')
                            ->success()
                            ->send();
                    } catch (\Throwable $th) {
                        Notification::make()
                            ->title('Terjadi Error ' . $th->getMessage())
                            ->danger()
                            ->send();
                    } finally {
                        if (file_exists($file)) {
                            unlink($file); // Menghapus file
                        }
                    }

                })
        ];
    }
}
