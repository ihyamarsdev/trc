<?php

namespace App\Filament\User\Resources\Timelines\Pages;

use App\Filament\User\Resources\Timelines\TimelineResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTimeline extends CreateRecord
{
    protected static string $resource = TimelineResource::class;
}
