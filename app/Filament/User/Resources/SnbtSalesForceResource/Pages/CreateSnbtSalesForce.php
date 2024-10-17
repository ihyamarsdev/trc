<?php

namespace App\Filament\User\Resources\SnbtSalesForceResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\User\Resources\SnbtSalesForceResource;

class CreateSnbtSalesForce extends CreateRecord
{
    protected static string $resource = SnbtSalesForceResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['users_id'] = Auth::id();
        $data['type'] = 'snbt';

        $input = strtotime($data['date_register']);
        $date = getDate($input);
        $data['monthYear'] = $date['month'] . ' ' . $date['year'];
        
        return $data;
    }
}
