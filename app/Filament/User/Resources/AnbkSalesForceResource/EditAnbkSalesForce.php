<?php

namespace App\Filament\User\Resources\AnbkSalesForceResource\Pages;

use App\Filament\User\Resources\AnbkSalesForceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAnbkSalesForce extends EditRecord
{
    protected static string $resource = AnbkSalesForceResource::class;

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
