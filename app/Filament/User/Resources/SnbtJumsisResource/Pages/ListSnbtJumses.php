<?php

namespace App\Filament\User\Resources\SnbtJumsisResource\Pages;

use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Support\Enums\IconPosition;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\User\Resources\SnbtJumsisResource;

class ListSnbtJumses extends ListRecords
{
    protected static string $resource = SnbtJumsisResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }

    public function getTabs(): array
    {
        return [
            'sd' => Tab::make('SD')
                ->icon('heroicon-m-rectangle-stack')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('education_level', 'SD')),
            'mi' => Tab::make('MI')
                ->icon('heroicon-m-rectangle-stack')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('education_level', 'MI')),
            'smp' => Tab::make('SMP')
                ->icon('heroicon-m-rectangle-stack')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('education_level', 'SMP')),
            'mts' => Tab::make('MTS')
                ->icon('heroicon-m-rectangle-stack')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('education_level', 'MTS')),
            'sma' => Tab::make('SMA')
                ->icon('heroicon-m-rectangle-stack')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('education_level', 'SMA')),
            'ma' => Tab::make('MA')
                ->icon('heroicon-m-rectangle-stack')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('education_level', 'MA')),
            'smk' => Tab::make('SMK')
                ->icon('heroicon-m-rectangle-stack')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('education_level', 'SMK')),
        ];
    }
}
