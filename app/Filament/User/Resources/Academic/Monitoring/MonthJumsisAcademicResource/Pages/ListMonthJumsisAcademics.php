<?php

namespace App\Filament\User\Resources\Academic\Monitoring\MonthJumsisAcademicResource\Pages;

use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Support\Enums\IconPosition;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\User\Resources\Academic\Monitoring\MonthJumsisAcademicResource;

class ListMonthJumsisAcademics extends ListRecords
{
    protected static string $resource = MonthJumsisAcademicResource::class;

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
