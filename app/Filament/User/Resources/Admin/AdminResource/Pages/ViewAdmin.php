<?php

namespace App\Filament\User\Resources\Admin\AdminResource\Pages;

use App\Filament\Components\Admin;
use App\Filament\User\Resources\Admin\AdminResource;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewAdmin extends ViewRecord
{
    protected static string $resource = AdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
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
            ->schema(Admin::infolist(record: $record));
    }
}
