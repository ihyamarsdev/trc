<?php

namespace App\Filament\User\Resources\Academic\Monitoring\Pages;

use App\Filament\User\Resources\Academic\Infolists\AcademicInfolist;
use App\Filament\User\Resources\Academic\Monitoring\RekapitulasiServiceResource;
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
        return AcademicInfolist::configure($schema, $this->record);
    }
}
