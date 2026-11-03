<?php

namespace App\Filament\User\Resources\Admin\AdminResource\Pages;

use App\Filament\Components\Support\LogsStatusChange;
use App\Filament\User\Resources\Admin\AdminResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAdmin extends EditRecord
{
    use LogsStatusChange;

    protected static string $resource = AdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('saveHeader')
                ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->action(fn () => $this->save())
                ->keyBindings(['mod+s']),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        $this->logStatusChange();
        $this->promoteWhenComplete();
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $input = strtotime($data['date_register']);
        $date = getdate($input);
        $data['monthYear'] = $date['month'].' '.$date['year'];

        return $data;
    }
}
