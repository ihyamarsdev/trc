<?php

namespace App\Filament\User\Resources\AllProgramDatacenterResource\Pages;

use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Support\Enums\IconPosition;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\User\Resources\AllProgramDatacenterResource;

class ListAllProgramDatacenters extends ListRecords
{
    protected static string $resource = AllProgramDatacenterResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }

}
