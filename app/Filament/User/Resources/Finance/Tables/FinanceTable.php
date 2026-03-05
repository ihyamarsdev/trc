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
                \Filament\Tables\Columns\Layout\Stack::make([
                    TextColumn::make('schools')
                        ->label('Sekolah')
                        ->weight(\Filament\Support\Enums\FontWeight::Bold)
                        ->searchable()
                        ->wrap(),
                    TextColumn::make('type')
                        ->label('Program')
                        ->icon('heroicon-m-academic-cap')
                        ->extraAttributes(['class' => 'uppercase text-gray-500']),
                ])->space(1),

                \Filament\Tables\Columns\Layout\Stack::make([
                    TextColumn::make('periode')
                        ->label('Periode')
                        ->icon('heroicon-m-calendar-days')
                        ->extraAttributes(['class' => 'uppercase']),
                    TextColumn::make('years')
                        ->label('Tahun')
                        ->icon('heroicon-m-clock'),
                ])->space(1),

                \Filament\Tables\Columns\Layout\Stack::make([
                    TextColumn::make('latestStatusLog.status.name')
                        ->label('Status')
                        ->badge()
                        ->color(
                            fn ($record) => match ($record->latestStatusLog?->status?->color) {
                                'green' => 'success',
                                'blue' => 'info',
                                'yellow' => 'warning',
                                'red' => 'danger',
                                default => 'gray',
                            }
                        )
                        ->placeholder('Belum Ada Status'),
                ])->space(1)->alignment(\Filament\Support\Enums\Alignment::End),
            ])->from('md'),
        ];
    }
}
