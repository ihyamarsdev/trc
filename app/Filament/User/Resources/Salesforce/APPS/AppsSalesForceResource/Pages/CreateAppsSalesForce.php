<?php

namespace App\Filament\User\Resources\Salesforce\APPS\AppsSalesForceResource\Pages;

use Filament\Actions;
use App\Models\RegistrationStatus;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\User\Resources\Salesforce\APPS\AppsSalesForceResource;

class CreateAppsSalesForce extends CreateRecord
{
    protected static string $resource = AppsSalesForceResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['users_id'] = Auth::id();
        $data['type'] = 'apps';

        $input = strtotime($data['date_register']);
        $date = getDate($input);
        $data['monthYear'] = $date['month'] . ' ' . $date['year'];

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
