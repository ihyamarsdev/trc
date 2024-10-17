<?php

namespace App\Filament\User\Resources\EstimateRegisterDatacenterResource\Pages;

use App\Filament\User\Resources\EstimateRegisterDatacenterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Filament\Support\Enums\IconPosition;
use Illuminate\Database\Eloquent\Builder;

class ListEstimateRegisterDatacenters extends ListRecords
{
    protected static string $resource = EstimateRegisterDatacenterResource::class;

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
