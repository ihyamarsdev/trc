<?php

namespace App\Filament\User\Resources\Service\Monitoring\Pages;

use App\Filament\User\Resources\Service\Monitoring\Infolists\RekapitulasiServiceInfolist;
use App\Filament\User\Resources\Service\Monitoring\RekapitulasiServiceResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewRekapitulasiService extends ViewRecord
{
    protected static string $resource = RekapitulasiServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return RekapitulasiServiceInfolist::configure($schema, $this->record);
    }
}
