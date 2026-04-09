<?php

namespace App\Filament\User\Resources\Academic\Monitoring\RekapitulasiServiceResource\Pages;

use App\Filament\Components\Academic;
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

    public function infolist(Schema $infolist): Schema
    {
        return $infolist->schema(Academic::infolist($this->record));
    }
}
