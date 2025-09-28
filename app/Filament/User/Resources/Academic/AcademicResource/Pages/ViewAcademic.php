<?php

namespace App\Filament\User\Resources\Academic\AcademicResource\Pages;

use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\Page;
use App\Filament\Components\Academic;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\User\Resources\Academic\AcademicResource;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;

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

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $record = $this->record;
        return $infolist
            ->schema(Academic::infolist(record: $record));
    }
}
