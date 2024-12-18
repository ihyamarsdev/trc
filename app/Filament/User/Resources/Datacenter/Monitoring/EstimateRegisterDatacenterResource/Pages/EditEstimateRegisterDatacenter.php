<?php

namespace App\Filament\User\Resources\Datacenter\Monitoring\EstimateRegisterDatacenterResource\Pages;

use App\Filament\User\Resources\Datacenter\Monitoring\EstimateRegisterDatacenterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEstimateRegisterDatacenter extends EditRecord
{
    protected static string $resource = EstimateRegisterDatacenterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
