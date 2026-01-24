<?php

namespace App\Filament\User\Resources\Academic\Monitoring\RekapitulasiServiceResource\Pages;

use App\Filament\Components\Academic;
use App\Filament\User\Resources\Academic\Monitoring\RekapitulasiServiceResource;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewRekapitulasiService extends ViewRecord
{
    protected static string $resource = RekapitulasiServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema(Academic::infolist($this->record));
    }
}
