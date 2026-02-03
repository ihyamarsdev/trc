<?php

namespace App\Filament\Resources\RekapitulasiServiceResource\Pages;

use App\Filament\Resources\RekapitulasiServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRekapitulasiServices extends ListRecords
{
    protected static string $resource = RekapitulasiServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
