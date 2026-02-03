<?php

namespace App\Filament\User\Resources\Academic\AcademicResource\Pages;

use App\Models\User;
use Filament\Actions;
use App\Models\Status;
use App\Models\RegistrationStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use App\Filament\User\Resources\Academic\AcademicResource;

class EditAcademic extends EditRecord
{
    protected static string $resource = AcademicResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
            ->modalDescription("Apakah status sudah sesuai? Pastikan kembali status yang Anda pilih sudah benar sebelum menyimpan.")
            ->modalIconColor('danger')
            ->action(fn() => $this->save())
            ->keyBindings(['mod+s']);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->record;
        $status = Status::find($data["status_id"]);

        if ($status->order == 10) {
            $recipients = User::role('service')->get();

            Notification::make()
                ->title('Data Sekolah ' . $record->schools . ' memasuki status ' . $status->name)
                ->icon('heroicon-o-document-text')
                ->success()
                ->actions([
                    Action::make('Lihat')
                        ->url(AcademicResource::getUrl('view', ['record' => $record]))
                        ->openUrlInNewTab(),
                ])
                ->sendToDatabase($recipients);

        }

        $record = $this->record;

        DB::transaction(function () use ($record) {
            if (empty($record->status_id)) {
                return;
            }

            $currentStatusId = (int) $record->status_id;

            $last = RegistrationStatus::query()
                ->where('registration_id', $record->id)
                ->latest('id')
                ->first();

            // --- SIMPLE: bandingkan berdasar angka status_id (sesuai permintaanmu) ---
            if (!$last) {
                RegistrationStatus::create([
                    'registration_id' => $record->id,
                    'status_id' => $currentStatusId,
                    'user_id' => Auth::id(),
                ]);
                return;
            }

            if ((int) $last->status_id === $currentStatusId) {
                // sama -> tidak perlu apa-apa
                return;
            }

            if ((int) $last->status_id < $currentStatusId) {
                // naik -> catat log baru
                RegistrationStatus::create([
                    'registration_id' => $record->id,
                    'status_id' => $currentStatusId,
                    'user_id' => Auth::id(),
                ]);
                return;
            }

            // turun -> hapus log sampai posisi terakhir <= current
            while ($last && (int) $last->status_id > $currentStatusId) {
                $last->delete();

                $last = RegistrationStatus::query()
                    ->where('registration_id', $record->id)
                    ->latest('id')
                    ->first();
            }

            // setelah rollback, jika belum persis sama dan ingin set posisinya ke current, tambahkan log current:
            if (!$last || (int) $last->status_id < $currentStatusId) {
                RegistrationStatus::create([
                    'registration_id' => $record->id,
                    'status_id' => $currentStatusId,
                    'user_id' => Auth::id(),
                ]);
            }

            /*
            // --- ALTERNATIF LEBIH AKURAT: bandingkan berdasar urutan (statuses.order) ---
            // $currentOrder = (int) Status::whereKey($currentStatusId)->value('order');
            // $lastOrder    = (int) Status::whereKey($last->status_id)->value('order');

            // if ($lastOrder === $currentOrder) { return; }

            // if ($lastOrder < $currentOrder) {
            //     RegistrationStatus::create([...]); return;
            // }

            // while ($last && (int) Status::whereKey($last->status_id)->value('order') > $currentOrder) {
            //     $last->delete();
            //     $last = RegistrationStatus::where('registration_id', $record->id)->latest('id')->first();
            // }
            // if (! $last || (int) Status::whereKey($last->status_id)->value('order') < $currentOrder) {
            //     RegistrationStatus::create([...]);
            // }
            */
        });


        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
