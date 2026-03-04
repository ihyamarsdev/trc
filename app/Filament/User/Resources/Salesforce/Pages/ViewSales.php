<?php

namespace App\Filament\User\Resources\Salesforce\Pages;

use App\Filament\User\Resources\Salesforce\Infolists\SalesInfolist;
use App\Filament\User\Resources\Salesforce\SalesResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewSales extends ViewRecord
{
    protected static string $resource = SalesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return SalesInfolist::configure($schema, $this->record);
    }
}
