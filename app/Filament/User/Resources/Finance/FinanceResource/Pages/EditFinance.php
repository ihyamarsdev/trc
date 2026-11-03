<?php

namespace App\Filament\User\Resources\Finance\FinanceResource\Pages;

use App\Filament\Components\Support\LogsStatusChange;
use App\Filament\User\Resources\Finance\FinanceResource;
use App\Models\Status;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFinance extends EditRecord
{
    use LogsStatusChange;

    protected static string $resource = FinanceResource::class;

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

    protected function afterSave(): void
    {
        $this->logStatusChange();
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $status = Status::find($data['status_id']);

        if ($status) {
            $data['status_color'] = $status->color;
        }

        return $data;
    }
}
