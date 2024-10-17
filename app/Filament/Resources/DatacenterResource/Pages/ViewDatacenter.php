<?php

namespace App\Filament\Resources\DatacenterResource\Pages;

use App\Filament\Resources\DatacenterResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDatacenter extends ViewRecord
{
    protected static string $resource = DatacenterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
