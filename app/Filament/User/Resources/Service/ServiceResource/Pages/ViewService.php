<?php

namespace App\Filament\User\Resources\Service\ServiceResource\Pages;

use App\Filament\Components\Service;
use App\Filament\User\Resources\Service\ServiceResource;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\ViewRecord;

class ViewService extends ViewRecord
{
    use InteractsWithRecord;

    protected static string $resource = ServiceResource::class;

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

    public function infolist(Infolist $infolist): Infolist
    {
        $record = $this->record;

        return $infolist
            ->schema(Service::infolist(record: $record));
    }
}
