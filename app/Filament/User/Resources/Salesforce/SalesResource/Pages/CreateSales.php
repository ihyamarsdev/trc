<?php

namespace App\Filament\User\Resources\Salesforce\SalesResource\Pages;

use Carbon\Carbon;
use App\Models\User;
use Filament\Actions;
use App\Models\Status;
use App\Models\RegistrationStatus;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\User\Resources\Salesforce\SalesResource;

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
            $data['status_id'] = 1;
        }

        $status = Status::find($data["status_id"]);

        if ($status->order == 2) {
            $recipients = User::role('service')->get();

            Notification::make()
                ->title('Data Sekolah ' . $data["schools"] . ' memasuki status ' . $status->name)
                ->icon('heroicon-o-document-text')
                ->success()
                ->sendToDatabase($recipients);

        }

        $data['status_color'] = $status->color;

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

        // Cek log terakhir (hindari duplikat)
        $last = RegistrationStatus::query()
            ->where('registration_id', $record->id)
            ->latest('id')
            ->first();

        if (! $last || (int) $last->status_id !== (int) $record->status_id) {
            RegistrationStatus::create([
                'registration_id' => $record->id,
                'status_id'       => $record->status_id,
                'user_id'         => Auth::id(),
                ''
            ]);
        }
    }
}
