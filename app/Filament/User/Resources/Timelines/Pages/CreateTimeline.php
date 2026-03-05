<?php

namespace App\Filament\User\Resources\Timelines\Pages;

use App\Filament\User\Resources\Timelines\TimelineResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateTimeline extends CreateRecord
{
    protected Width|string|null $maxWidth = Width::Full;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected static string $resource = TimelineResource::class;
}
