<?php

namespace App\Filament\User\Resources\Finance\Tables;

use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;

class FinanceTable
{
    public static function columns(): array
    {
        return [
            Split::make([
                TextColumn::make('type')
                    ->label('Program')
                    ->description('Program', position: 'above')
                    ->extraAttributes(['class' => 'uppercase']),
                TextColumn::make('schools')
                    ->label('Sekolah')
                    ->description('Sekolah', position: 'above')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('periode')
                    ->label('Periode')
                    ->description('Periode', position: 'above')
                    ->extraAttributes(['class' => 'uppercase'])
                    ->wrap(),
                TextColumn::make('years')
                    ->label('Tahun')
                    ->description('Tahun', position: 'above'),
                TextColumn::make('latestStatusLog.status.name')
                    ->label('Status')
                    ->badge()
                    ->color(
                        fn ($record) => match ($record->latestStatusLog?->status?->color) {
                            'green' => 'success',
                            'blue' => 'blue',
                            'yellow' => 'warning',
                            'red' => 'danger',
                            default => 'gray',
                        }
                    )
                    ->placeholder('Belum Ada Status'),
            ])->from('md'),
        ];
    }
}
