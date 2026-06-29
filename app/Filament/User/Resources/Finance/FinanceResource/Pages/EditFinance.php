<?php

namespace App\Filament\User\Resources\Finance\FinanceResource\Pages;

use App\Filament\User\Resources\Finance\FinanceResource;
use App\Models\RegistrationStatus;
use App\Models\Status;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EditFinance extends EditRecord
{
    protected static string $resource = FinanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->record;
        $status = Status::find($data['status_id']);

        if ($status) {
            $data['status_color'] = $status->color;
        }

        DB::transaction(function () use ($record) {
            if (empty($record->status_id)) {
                return;
            }

            $currentStatusId = (int) $record->status_id;
            $currentOrder = (int) Status::whereKey($currentStatusId)->value('order');

            $last = RegistrationStatus::query()
                ->where('registration_id', $record->id)
                ->latest('id')
                ->first();

            if (! $last) {
                RegistrationStatus::create([
                    'registration_id' => $record->id,
                    'status_id' => $currentStatusId,
                    'user_id' => Auth::id(),
                ]);

                return;
            }

            $lastOrder = (int) Status::whereKey($last->status_id)->value('order');

            if ($lastOrder === $currentOrder) {
                return;
            }

            if ($lastOrder < $currentOrder) {
                RegistrationStatus::create([
                    'registration_id' => $record->id,
                    'status_id' => $currentStatusId,
                    'user_id' => Auth::id(),
                ]);

                return;
            }

            // turun -> hapus log sampai posisi terakhir <= current
            while ($last && (int) Status::whereKey($last->status_id)->value('order') > $currentOrder) {
                $last->delete();

                $last = RegistrationStatus::query()
                    ->where('registration_id', $record->id)
                    ->latest('id')
                    ->first();
            }

            // setelah rollback, jika belum persis sama dan ingin set posisinya ke current, tambahkan log current:
            $lastOrderAfterRollback = $last ? (int) Status::whereKey($last->status_id)->value('order') : 0;
            if (! $last || $lastOrderAfterRollback < $currentOrder) {
                RegistrationStatus::create([
                    'registration_id' => $record->id,
                    'status_id' => $currentStatusId,
                    'user_id' => Auth::id(),
                ]);
            }
        });

        return $data;
    }
}
