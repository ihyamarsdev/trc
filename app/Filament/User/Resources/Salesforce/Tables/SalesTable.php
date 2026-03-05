<?php

namespace App\Filament\User\Resources\Salesforce\Tables;

use App\Filament\Enum\Jenjang;
use App\Filament\Enum\Periode;
use App\Filament\Enum\Program;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class SalesTable
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

    public static function filters(): array
    {
        return [
            SelectFilter::make('type')
                ->label('Program')
                ->options(Program::list())
                ->preload()
                ->indicator('Program'),
            SelectFilter::make('periode')
                ->label('Periode')
                ->options(Periode::list())
                ->preload()
                ->indicator('Periode'),
            SelectFilter::make('education_level')
                ->label('Jenjang')
                ->options(Jenjang::list())
                ->preload()
                ->indicator('Jenjang'),
        ];
    }

    public static function bulkActions(): array
    {
        return [
            \Filament\Actions\BulkActionGroup::make([
                \Filament\Actions\DeleteBulkAction::make(),
            ]),
        ];
    }
}
