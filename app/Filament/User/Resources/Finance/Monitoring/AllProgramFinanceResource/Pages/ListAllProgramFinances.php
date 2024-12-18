<?php

namespace App\Filament\User\Resources\Finance\Monitoring\AllProgramFinanceResource\Pages;

use Filament\Actions;
use Filament\Support\Enums\IconPosition;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ListRecords\Tab;
use App\Filament\User\Resources\Finance\Monitoring\AllProgramFinanceResource;

class ListAllProgramFinances extends ListRecords
{
    protected static string $resource = AllProgramFinanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    public function getTabs(): array
    {
        return [
            'anbk' => Tab::make('ANBK')
                ->icon('heroicon-m-rectangle-stack')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'anbk')),
            'apps' => Tab::make('APPS')
                ->icon('heroicon-m-rectangle-stack')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'apps')),
            'snbt' => Tab::make('SNBT')
                ->icon('heroicon-m-rectangle-stack')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'snbt')),
        ];
    }
}
