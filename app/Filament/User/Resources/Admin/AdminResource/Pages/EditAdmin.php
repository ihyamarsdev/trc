<?php

namespace App\Filament\User\Resources\Admin\AdminResource\Pages;

use App\Filament\User\Resources\Admin\AdminResource;
use App\Models\RegistrationStatus;
use App\Models\Status;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EditAdmin extends EditRecord
{
    protected static string $resource = AdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $input = strtotime($data['date_register']);
        $date = getdate($input);
        $data['monthYear'] = $date['month'].' '.$date['year'];

        $record = $this->record;

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
