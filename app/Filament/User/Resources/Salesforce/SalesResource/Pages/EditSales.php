<?php

namespace App\Filament\User\Resources\Salesforce\SalesResource\Pages;

use App\Filament\Components\Support\GuardsStatusTransition;
use App\Filament\User\Resources\Salesforce\SalesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSales extends EditRecord
{
    use GuardsStatusTransition;

    protected static string $resource = SalesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('saveHeader')
                ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->requiresConfirmation()
                ->modalDescription('Apakah data sudah sesuai? Jika semua data wajib sudah terisi, status otomatis menjadi Kuning.')
                ->modalIconColor('danger')
                ->action(fn () => $this->save())
                ->keyBindings(['mod+s']),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getSaveFormAction(): Actions\Action
    {
        return Actions\Action::make('save')
            ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
            ->requiresConfirmation()
            ->modalDescription('Apakah data sudah sesuai? Jika semua data wajib sudah terisi, status otomatis menjadi Kuning.')
            ->modalIconColor('danger')
            ->action(fn () => $this->save())
            ->keyBindings(['mod+s']);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        $this->promoteWhenComplete();
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $dateRegister = $data['date_register'] ?? null;

        if ($dateRegister) {
            $input = strtotime((string) $dateRegister);

            if ($input !== false) {
                $date = getdate($input);
                $data['monthYear'] = $date['month'].' '.$date['year'];
            }
        }

        return $data;
    }
}
