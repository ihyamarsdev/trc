<?php

namespace App\Filament\User\Resources\Salesforce\SalesResource\Pages;

use App\Filament\User\Resources\Salesforce\SalesResource;
use App\Models\RegistrationStatus;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateSales extends CreateRecord
{
    protected static string $resource = SalesResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }


    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['users_id'] = Auth::id();

        if (empty($data['status_id'])) {
            $data['status_id'] = 28;
        }

        $status = Status::find($data['status_id']);

        if ($status) {
            $data['status_color'] = $status->color;
        }

        // monthYear aman dibentuk (jika date_register diisi)
        if (!empty($data['date_register'])) {
            $dt = Carbon::parse($data['date_register']);
            $data['monthYear'] = $dt->translatedFormat('F Y'); // contoh: "September 2025"
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record; // RegistrationData yang baru dibuat
        if (empty($record->status_id)) {
            return;
        }

        $status = Status::find($record->status_id);

        if ($status && $status->order == 2) {
            $recipients = User::role('service')->get();

            Notification::make()
                ->title('Data Sekolah ' . $record->schools . ' memasuki status ' . $status->name)
                ->icon('heroicon-o-document-text')
                ->success()
                ->actions([
                    NotificationAction::make('Lihat')
                        ->url(SalesResource::getUrl('view', ['record' => $record]))
                        ->openUrlInNewTab(),
                ])
                ->sendToDatabase($recipients);
        }

        // Cek log terakhir (hindari duplikat)
        $last = RegistrationStatus::query()
            ->where('registration_id', $record->id)
            ->latest('id')
            ->first();

        if (!$last || (int) $last->status_id !== (int) $record->status_id) {
            RegistrationStatus::create([
                'registration_id' => $record->id,
                'status_id' => $record->status_id,
                'user_id' => Auth::id(),
            ]);
        }
    }
}
