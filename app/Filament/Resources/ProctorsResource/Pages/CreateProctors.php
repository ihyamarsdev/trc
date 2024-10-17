<?php

namespace App\Filament\Resources\ProctorsResource\Pages;

use App\Filament\Resources\ProctorsResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProctors extends CreateRecord
{
    protected static string $resource = ProctorsResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
