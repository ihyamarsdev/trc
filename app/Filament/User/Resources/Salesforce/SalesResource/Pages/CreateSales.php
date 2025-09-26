<?php

namespace App\Filament\User\Resources\Salesforce\SalesResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use App\Models\RegistrationStatus;
use Illuminate\Support\Facades\Auth;
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
        // $data['type']     = 'anbk';

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
