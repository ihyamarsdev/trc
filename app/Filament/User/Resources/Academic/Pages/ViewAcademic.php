<?php

namespace App\Filament\User\Resources\Academic\Pages;

use App\Filament\User\Resources\Academic\AcademicResource;
use App\Filament\User\Resources\Academic\Infolists\AcademicInfolist;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewAcademic extends ViewRecord
{
    protected static string $resource = AcademicResource::class;

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
        return AcademicInfolist::configure($schema, $this->record);
    }
}
