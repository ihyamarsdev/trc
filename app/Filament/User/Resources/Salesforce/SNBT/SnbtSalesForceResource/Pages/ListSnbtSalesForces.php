<?php

namespace App\Filament\User\Resources\Salesforce\SNBT\SnbtSalesForceResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\salesforce\ANBKImport;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ListRecords;
use App\Filament\User\Resources\Salesforce\SNBT\SnbtSalesForceResource;

class ListSnbtSalesForces extends ListRecords
{
    protected static string $resource = SnbtSalesForceResource::class;

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
                        Log::error('Error saat mengimpor file: ' . $th->getMessage(), [
                            'file' => $file,
                            'data' => $data,
                            'trace' => $th->getTraceAsString(),
                        ]);

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
                ->modalHeading('Import Data')
                ->modalContent(function () {
                    return view('components.sample-excel-modal');
                })
                ->openUrlInNewTab()
        ];
    }
}
