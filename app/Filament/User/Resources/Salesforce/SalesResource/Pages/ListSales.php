<?php

namespace App\Filament\User\Resources\Salesforce\SalesResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Maatwebsite\Excel\Excel;
use App\Models\RegistrationData;
use Illuminate\Support\Facades\Log;
use App\Imports\salesforce\ANBKImport;
use App\Filament\Imports\SalesImporter;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ListRecords;
use App\Filament\User\Resources\Salesforce\SalesResource;
use HayderHatem\FilamentExcelImport\Actions\FullImportAction;
use HayderHatem\FilamentExcelImport\Actions\Concerns\CanImportExcelRecords;

class ListSales extends ListRecords
{
    use CanImportExcelRecords;

    protected static string $resource = SalesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat'),
            // Action::make('import')
            //     ->label('Import')
            //     ->icon('heroicon-o-arrow-up-tray')
            //     ->form([
            //         FileUpload::make('attachment')
            //     ])
            //     ->action(function (array $data) {
            //         $file = public_path('storage/' . $data['attachment']);

            //         try {
            //             Excel::import(new ANBKImport(), $file);

            //             Notification::make()
            //                 ->title('Berhasil Import File')
            //                 ->success()
            //                 ->send();
            //         } catch (\Throwable $th) {
            //             Log::error('Error saat mengimpor file: ' . $th->getMessage(), [
            //                 'file' => $file,
            //                 'data' => $data,
            //                 'trace' => $th->getTraceAsString(),
            //             ]);

            //             if (file_exists($file)) {
            //                 unlink($file);
            //             }

            //             Notification::make()
            //                 ->title('Terjadi Error Saat Melakukan Import  File')
            //                 ->danger()
            //                 ->send();
            //         } finally {
            //             if (file_exists($file)) {
            //                 unlink($file); // Menghapus file
            //             }
            //         }

            //     })
            //     ->modalHeading('Import Data')
            //     ->modalContent(function () {
            //         return view('components.sample-excel-modal');
            //     })
            //     ->openUrlInNewTab()
        ];
    }
}
