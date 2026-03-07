<?php

namespace App\Filament\User\Resources\Service\Monitoring\Infolists;

use App\Filament\User\Resources\Service\Infolists\ServiceInfolist;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class RekapitulasiServiceInfolist
{
    public static function configure(Schema $schema, Model $record): Schema
    {
        return ServiceInfolist::configure($schema, $record);
    }
}
