<?php

namespace App\Filament\User\Resources\Salesforce\Pages;

use App\Filament\User\Resources\Salesforce\SalesResource;
use App\Imports\SalesImport;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ListSales extends ListRecords
{
    protected static string $resource = SalesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat')
                ->after(function ($record) {
                    Notification::make()
                        ->title('Data berhasil dibuat')
                        ->success()
                        ->send();

                }),
            Action::make('import')
                ->label('Import')
                ->icon('heroicon-o-arrow-up-tray')
                ->schema([
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
                    } catch (Throwable $th) {
                        Log::error('Error saat mengimpor file: '.$th->getMessage(), [
                            'file' => $file,
                            'data' => $data,
                            'trace' => $th->getTraceAsString(),
                        ]);

                        if (file_exists($file)) {
                            unlink($file);
                        }

                        $errorMessage = $th->getMessage();
                        if ($th instanceof QueryException) {
                            $errorMessage = 'Terjadi permasalahan terhadap struktur data atau database. Pastikan pengisian format Anda sudah tepat sesuai template.';
                        }

                        Notification::make()
                            ->title('Gagal Melakukan Import')
                            ->body($errorMessage)
                            ->persistent()
                            ->danger()
                            ->send();
                    } finally {
                        if (file_exists($file)) {
                            unlink($file); // Menghapus file
                        }
                    }

                })
                ->extraModalFooterActions([
                    Action::make('downloadSample')
                        ->label('Download Sample')
                        ->color('info')
                        ->action(function () {
                            return Excel::download(new \App\Filament\Components\SampleExcel, 'sample_import_sales.xlsx');
                        }),
                ])
                ->modalHeading('Import')
                ->modalContent(function () {
                    return view('components.sample-excel-modal');
                }),
        ];
    }
}
