<?php

namespace App\Filament\User\Resources\Academic\AcademicResource\Pages;

use App\Filament\Components\Academic;
use App\Filament\User\Resources\Academic\AcademicResource;
use Filament\Actions;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewAcademic extends ViewRecord
{
    use InteractsWithRecord;

    protected static string $resource = AcademicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make('edit')
                ->label('Ubah')
                ->url($this->getResource()::getUrl('edit', ['record' => $this->record])),
        ];
    }

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function infolist(Schema $infolist): Schema
    {
        $record = $this->record;

        return $infolist
            ->schema(Academic::infolist(record: $record));
    }
}
