<?php

namespace App\Filament\User\Resources\Salesforce\SalesResource\Pages;

use App\Filament\User\Resources\Salesforce\SalesResource;
use App\Imports\SalesImport;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use HayderHatem\FilamentExcelImport\Actions\Concerns\CanImportExcelRecords;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ListSales extends ListRecords
{
    use CanImportExcelRecords;

    protected static string $resource = SalesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat')
                ->after(function ($record) {
                    Notification::make()
                        ->title('Data berhasil dibuat')
                        ->success()
                        ->actions([
                            Action::make('Lihat')
                                ->url(SalesResource::getUrl('view', ['record' => $record]))
                                ->openUrlInNewTab(),
                        ])
                        ->send();
                }),
            Action::make('import')
                ->label('Import')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    FileUpload::make('attachment'),
                ])
                ->action(function (array $data) {
                    $file = public_path('storage/'.$data['attachment']);

                    try {
                        Excel::import(new SalesImport, $file);

                        Notification::make()
                            ->title('Berhasil Import File')
                            ->success()
                            ->send();
                    } catch (\Throwable $th) {
                        Log::error('Error saat mengimpor file: '.$th->getMessage(), [
                            'file' => $file,
                            'data' => $data,
                            'trace' => $th->getTraceAsString(),
                        ]);

                        if (file_exists($file)) {
                            unlink($file);
                        }

                        Notification::make()
                            ->title('Terjadi Error Saat Melakukan Import File. Error: '.$th->getMessage())
                            ->danger()
                            ->send();
                    } finally {
                        if (file_exists($file)) {
                            unlink($file); // Menghapus file
                        }
                    }

                })
                ->modalHeading('Import')
                ->modalContent(function () {
                    return view('components.sample-excel-modal');
                }),
        ];
    }
}
