<?php

namespace App\Filament\User\Resources\Service\Pages;

use App\Filament\User\Resources\Service\ServiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;
}
