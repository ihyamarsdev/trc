<?php

namespace App\Filament\Components\Support;

use App\Filament\Enum\Program;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Model;

trait HasProgramMetadata
{
    protected static function meta(Get $get, string $default = 'apps'): array
    {
        return Program::getMetadata($get('type'), $default);
    }

    protected static function metaInfo(Model $record, string $default = 'none'): array
    {
        return Program::getMetadata($record->type, $default);
    }
}
