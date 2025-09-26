<?php

namespace App\Filament\User\Resources\Activity\ActivityResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\User\Resources\Activity\ActivityResource;

class CreateActivity extends CreateRecord
{
    protected static string $resource = ActivityResource::class;
}
