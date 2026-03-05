<?php

namespace App\Filament\User\Resources\Activity\Pages;

use App\Filament\User\Resources\Activity\ActivityResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateActivity extends CreateRecord
{
    protected Width|string|null $maxWidth = Width::Full;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected static string $resource = ActivityResource::class;
}
