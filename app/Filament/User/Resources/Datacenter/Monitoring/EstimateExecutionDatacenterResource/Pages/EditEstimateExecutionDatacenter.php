<?php

namespace App\Filament\User\Resources\Datacenter\Monitoring\EstimateExecutionDatacenterResource\Pages;

use App\Filament\User\Resources\Datacenter\Monitoring\EstimateExecutionDatacenterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEstimateExecutionDatacenter extends EditRecord
{
    protected static string $resource = EstimateExecutionDatacenterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
