<?php

namespace App\Filament\User\Resources\Salesforce\SalesResource\Pages;

use App\Filament\Components\Support\GuardsStatusTransition;
use App\Filament\User\Resources\Salesforce\SalesResource;
use App\Models\Status;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateSales extends CreateRecord
{
    use GuardsStatusTransition;

    protected static string $resource = SalesResource::class;

    protected static bool $canCreateAnother = false;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('createHeader')
                ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->requiresConfirmation()
                ->modalDescription('Apakah data sudah sesuai? Jika semua data wajib sudah terisi, status otomatis menjadi Kuning.')
                ->modalIconColor('danger')
                ->action(fn () => $this->create())
                ->keyBindings(['mod+s']),
        ];
    }

    protected function getCreateFormAction(): Actions\Action
    {
        return Actions\Action::make('create')
            ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
            ->requiresConfirmation()
            ->modalDescription('Apakah data sudah sesuai? Jika semua data wajib sudah terisi, status otomatis menjadi Kuning.')
            ->modalIconColor('danger')
            ->action(fn () => $this->create())
            ->keyBindings(['mod+s']);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['users_id'] = Auth::id();

        $status = Status::where('order', 1)->first();

        $data['status_id'] = $status?->id ?? 28;
        $data['status_color'] = $status?->color ?? 'red';

        // monthYear aman dibentuk (jika date_register diisi)
        if (! empty($data['date_register'])) {
            $dt = Carbon::parse($data['date_register']);
            $data['monthYear'] = $dt->translatedFormat('F Y'); // contoh: "September 2025"
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->logStatusChange();
        $this->promoteWhenComplete();
    }
}
