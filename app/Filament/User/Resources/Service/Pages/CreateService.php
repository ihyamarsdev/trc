<?php

namespace App\Filament\User\Resources\Service\Pages;

use App\Filament\User\Resources\Service\ServiceResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateService extends CreateRecord
{
    protected Width|string|null $maxWidth = Width::Full;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected static string $resource = ServiceResource::class;
}
