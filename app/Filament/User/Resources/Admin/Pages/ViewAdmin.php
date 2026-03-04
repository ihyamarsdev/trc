<?php

namespace App\Filament\User\Resources\Admin\Pages;

use App\Filament\User\Resources\Admin\AdminResource;
use App\Filament\User\Resources\Admin\Infolists\AdminInfolist;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewAdmin extends ViewRecord
{
    protected static string $resource = AdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function infolist(Schema $schema): Schema
    {
        return AdminInfolist::configure($schema, $this->record);
    }
}
