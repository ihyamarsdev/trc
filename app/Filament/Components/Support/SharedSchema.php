<?php

namespace App\Filament\Components\Support;

use App\Filament\Components\Support\RegionalOptions;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Str;

class SharedSchema
{
    public static function getDifference(Get $get, Set $set): void
    {
        $accountCount = (int) $get('account_count_created');
        $implementerCount = (int) $get('implementer_count');

        if ($accountCount !== 0 || $implementerCount !== 0) {
            $set('difference', abs($accountCount - $implementerCount));
        } else {
            $set('difference', 0);
        }
    }

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

                TextColumn::make('latestStatusLog.status.color')
                    ->label('Status')
                    ->description('Status', position: 'above')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'green' => 'green',
                        'blue' => 'blue',
                        'yellow' => 'yellow',
                        'red' => 'red',
                    })
                    ->default('red'),
            ])->from('md'),
        ];
    }

    public static function locationFields(): array
    {
        return [
            Select::make('provinces')
                ->label('Provinsi')
                ->preload()
                ->searchable()
                ->reactive()
                ->dehydrateStateUsing(
                    fn (?string $state): string => Str::upper($state),
                )
                ->live(500)
                ->options(RegionalOptions::provinces()),
            Select::make('regencies')
                ->label('Kota / Kabupaten')
                ->preload()
                ->searchable()
                ->reactive()
                ->dehydrateStateUsing(
                    fn (?string $state): string => Str::upper($state),
                )
                ->live(100)
                ->options(fn (Get $get): array => RegionalOptions::regenciesByProvinceName($get('provinces'))),
            Select::make('area')
                ->label('Wilayah')
                ->dehydrateStateUsing(
                    fn (?string $state): string => Str::upper($state),
                )
                ->options(fn (Get $get): array => RegionalOptions::areasByRegencyName($get('regencies')))
                ->visible(function (Get $get) {
                    $province = strtolower($get('provinces') ?? '');
                    return $province === 'daerah khusus ibukota jakarta' || $province === 'dki jakarta';
                }),
            Select::make('district')
                ->label('Kecamatan')
                ->preload()
                ->searchable()
                ->reactive()
                ->live(100)
                ->dehydrateStateUsing(
                    fn (?string $state): string => Str::upper($state),
                )
                ->options(fn (Get $get): array => RegionalOptions::districtsByRegencyName($get('regencies'))),
        ];
    }
}
