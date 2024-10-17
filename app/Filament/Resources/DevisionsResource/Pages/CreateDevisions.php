<?php

namespace App\Filament\Resources\DevisionsResource\Pages;

use App\Filament\Resources\DevisionsResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDevisions extends CreateRecord
{
    protected static string $resource = DevisionsResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
