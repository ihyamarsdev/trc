<?php

namespace App\Filament\User\Resources\Service\Monitoring\Pages;

use App\Filament\User\Resources\Service\Infolists\ServiceInfolist;
use App\Filament\User\Resources\Service\Monitoring\RekapitulasiServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewRekapitulasiService extends ViewRecord
{
    protected static string $resource = RekapitulasiServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return ServiceInfolist::configure($schema, $this->record);
    }
}
