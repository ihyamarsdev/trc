<?php

namespace App\Filament\User\Resources\AppsSalesForceResource\Pages;

use App\Filament\User\Resources\AppsSalesForceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppsSalesForce extends EditRecord
{
    protected static string $resource = AppsSalesForceResource::class;

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
        $date = getDate($input);
        $data['monthYear'] = $date['month'] . ' ' . $date['year'];
        return $data;
    }
}
