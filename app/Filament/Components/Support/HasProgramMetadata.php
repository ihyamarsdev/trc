<?php

namespace App\Filament\Components\Support;

use App\Models\Option;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Model;

trait HasProgramMetadata
{
    protected static function meta(Get $get, string $default = 'apps'): array
    {
        return Option::programMeta($get('type'), $default);
    }

    protected static function metaInfo(Model $record, string $default = 'none'): array
    {
        return Option::programMeta($record->type, $default);
    }
}
