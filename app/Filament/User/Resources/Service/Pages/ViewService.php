<?php

namespace App\Filament\User\Resources\Service\Pages;

use App\Filament\User\Resources\Service\Infolists\ServiceInfolist;
use App\Filament\User\Resources\Service\ServiceResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewService extends ViewRecord
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make('edit')
                ->label('Ubah')
                ->url($this->getResource()::getUrl('edit', ['record' => $this->record])),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return ServiceInfolist::configure($schema, $this->record);
    }
}
