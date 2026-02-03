<?php

namespace App\Filament\User\Resources\Academic\Monitoring\RekapitulasiServiceResource\Pages;

use App\Filament\User\Resources\Academic\Monitoring\RekapitulasiServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRekapitulasiServices extends ListRecords
{
    protected static string $resource = RekapitulasiServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
