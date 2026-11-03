<?php

namespace App\Filament\User\Resources\Service\ServiceResource\Pages;

use App\Filament\Components\Support\LogsStatusChange;
use App\Filament\User\Resources\Service\ServiceResource;
use App\Models\Status;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditService extends EditRecord
{
    use LogsStatusChange;

    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('saveHeader')
                ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->requiresConfirmation()
                ->modalDescription('Apakah status sudah sesuai? Pastikan kembali status yang Anda pilih sudah benar sebelum menyimpan.')
                ->modalIconColor('danger')
                ->action(fn () => $this->save())
                ->keyBindings(['mod+s']),
            Actions\DeleteAction::make(),
        ];
    }

    // protected function getSaveFormAction(): Actions\Action
    // {
    //     return parent::getSaveFormAction()
    //         ->requiresConfirmation() // Memunculkan modal konfirmasi
    //         ->modalHeading('Konfirmasi Perubahan')
    //         ->modalDescription('Apakah status sudah sesuai? Pastikan kembali status yang Anda pilih sudah benar sebelum menyimpan.')
    //         ->modalSubmitActionLabel('Ya, Simpan Data')
    //         ->modalCancelActionLabel('Tidak, Kembali ke Form');
    // }

    protected function getSaveFormAction(): Actions\Action
    {
        return Actions\Action::make('save')
            ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
            ->requiresConfirmation()
            ->modalDescription('Apakah status sudah sesuai? Pastikan kembali status yang Anda pilih sudah benar sebelum menyimpan.')
            ->modalIconColor('danger')
            ->action(fn () => $this->save())
            ->keyBindings(['mod+s']);
    }

    protected function afterSave(): void
    {
        $this->logStatusChange();
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->record;
        $status = Status::find($data['status_id']);

        if ($status->order == 10) {
            $recipients = User::role('service')->get();

            Notification::make()
                ->title('Data Sekolah '.$record->schools.' memasuki status '.$status->name)
                ->icon('heroicon-o-document-text')
                ->success()
                ->actions([
                    Action::make('Lihat')
                        ->url(ServiceResource::getUrl('view', ['record' => $record]))
                        ->openUrlInNewTab(),
                ])
                ->sendToDatabase($recipients);

        }

        if ($status) {
            $data['status_color'] = $status->color;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
