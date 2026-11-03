<?php

namespace App\Filament\User\Resources\Admin\AdminResource\Pages;

use App\Filament\Components\Support\LogsStatusChange;
use App\Filament\User\Resources\Admin\AdminResource;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateAdmin extends CreateRecord
{
    use LogsStatusChange;

    protected static string $resource = AdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('createHeader')
                ->label(__('filament-panels::resources/pages/create-record.form.actions.create.label'))
                ->action(fn () => $this->create())
                ->keyBindings(['mod+s']),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['users_id'] = Auth::id();
        // $data['type']     = 'anbk';

        // monthYear aman dibentuk (jika date_register diisi)
        if (! empty($data['date_register'])) {
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

        $this->logStatusChange();
        $this->promoteWhenComplete();
    }
}
