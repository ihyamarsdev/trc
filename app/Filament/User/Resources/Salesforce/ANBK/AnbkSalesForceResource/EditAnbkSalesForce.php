<?php

namespace App\Filament\User\Resources\Salesforce\ANBK\AnbkSalesForceResource\Pages;

use App\Filament\User\Resources\Salesforce\ANBK\AnbkSalesForceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Creasi\Nusa\Models\{Province, Regency};

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
