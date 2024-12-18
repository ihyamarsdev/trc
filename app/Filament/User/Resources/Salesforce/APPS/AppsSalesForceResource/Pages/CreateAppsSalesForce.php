<?php

namespace App\Filament\User\Resources\Salesforce\APPS\AppsSalesForceResource\Pages;

use Filament\Actions;
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
}
