<?php

namespace App\Filament\User\Resources\Salesforce\ANBK\AnbkSalesForceResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\User\Resources\Salesforce\ANBK\AnbkSalesForceResource;
use Creasi\Nusa\Models\{Province, Regency};

class CreateAnbkSalesForce extends CreateRecord
{
    protected static string $resource = AnbkSalesForceResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {

        $data['users_id'] = Auth::id();
        $data['type'] = 'anbk';

        $input = strtotime($data['date_register']);
        $date = getDate($input);
        $data['monthYear'] = $date['month'] . ' ' . $date['year'];

        return $data;
    }
}
