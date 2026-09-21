<?php

namespace App\Filament\User\Resources\TimelineResource\Pages;

use App\Filament\Components\Academic;
use App\Filament\User\Resources\Admin\AdminResource;
use App\Filament\User\Resources\TimelineResource;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewTimeline extends ViewRecord
{
    protected static string $resource = TimelineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('edit')
                ->label('Ubah')
                ->icon('heroicon-m-pencil-square')
                ->url(fn (): string => AdminResource::getUrl('edit', ['record' => $this->record], panel: 'user'))
                ->visible(fn (): bool => auth()->user()?->hasRole('admin') ?? false),
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
            ->schema(Academic::infolist(record: $record));
    }
}
