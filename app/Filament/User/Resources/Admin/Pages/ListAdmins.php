<?php

namespace App\Filament\User\Resources\Admin\Pages;

use App\Filament\User\Resources\Admin\AdminResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAdmins extends ListRecords
{
    protected static string $resource = AdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat'),
        ];
    }
}
