<?php

namespace App\Filament\Resources\CounselorCoordinatorResource\Pages;

use App\Filament\Resources\CounselorCoordinatorResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCounselorCoordinator extends CreateRecord
{
    protected static string $resource = CounselorCoordinatorResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
