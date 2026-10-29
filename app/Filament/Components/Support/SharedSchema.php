<?php

namespace App\Filament\Components\Support;

use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
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

    /**
     * Recalculate "Total Dana Sesuai SPJ" from price and the selected count option, then the tax.
     */
    public static function syncTotal(Get $get, Set $set): void
    {
        $option = (string) $get('option_price');

        if ($option === 'real') {
            $count = (float) $get('real_count');
        } else {
            preg_match('/\d+/', $option, $matches);
            $count = (float) ($matches[0] ?? 0);
        }

        $set('total', (float) $get('price') * $count);

        self::syncTax($get, $set);
    }

    /**
     * Recalculate "Nominal Pajak" as the selected tax percentages applied to "Total Dana Sesuai SPJ".
     */
    public static function syncTax(Get $get, Set $set): void
    {
        $rate = self::taxRateTotal($get('tax_rates'), $get('tax_custom_rate'));

        $set('tax_amount', round((float) $get('total') * $rate / 100));
    }

    /**
     * Sum of the selected tax percentages; the `custom` choice uses the manually typed percentage.
     *
     * @param  array<int|string, int|string>|null  $rates
     */
    public static function taxRateTotal(?array $rates, mixed $customRate): float
    {
        $total = 0.0;

        foreach ($rates ?? [] as $rate) {
            $total += $rate === 'custom' ? (float) $customRate : (float) $rate;
        }

        return $total;
    }

    /**
     * @param  array<int|string, int|string>|null  $rates
     * @return array<int, string>
     */
    public static function taxRateLabels(?array $rates, mixed $customRate): array
    {
        $format = fn (float $percent): string => rtrim(rtrim(number_format($percent, 2, ',', ''), '0'), ',').'%';

        return collect($rates ?? [])
            ->map(fn (int|string $rate): string => $format($rate === 'custom' ? (float) $customRate : (float) $rate))
            ->values()
            ->all();
    }

    /**
     * List row shared by the Admin, Salesforce, Service and Finance tables.
     * Two layouts toggled by breakpoint: labelled columns from `md`, and a compact
     * stacked card (school, program/periode/year, estimate, status) on phones.
     *
     * @return array<int, Split|Stack>
     */
    public static function columns(): array
    {
        $upper = fn (?string $state): string => Str::upper((string) $state);

        return [
            Split::make([
                TextColumn::make('type')
                    ->label('Program')
                    ->description('Program', position: 'above')
                    ->extraAttributes(['class' => 'uppercase'])
                    ->visibleFrom('md'),
                TextColumn::make('schools')
                    ->label('Sekolah')
                    ->description('Sekolah', position: 'above')
                    ->searchable()
                    ->wrap()
                    ->visibleFrom('md'),
                TextColumn::make('periode')
                    ->label('Periode')
                    ->description('Periode', position: 'above')
                    ->extraAttributes(['class' => 'uppercase'])
                    ->wrap()
                    ->visibleFrom('md'),
                TextColumn::make('years')
                    ->label('Tahun')
                    ->description('Tahun', position: 'above')
                    ->visibleFrom('md'),
                self::statusColumn()
                    ->description('Status', position: 'above')
                    ->visibleFrom('md'),
            ])->from('md'),

            Stack::make([
                TextColumn::make('schools')
                    ->label('Sekolah')
                    ->placeholder('Sekolah belum terisi')
                    ->searchable()
                    ->weight(FontWeight::SemiBold)
                    ->wrap()
                    ->hiddenFrom('md'),
                Split::make([
                    TextColumn::make('type')
                        ->label('Program')
                        ->badge()
                        ->color('primary')
                        ->formatStateUsing($upper)
                        ->grow(false)
                        ->hiddenFrom('md'),
                    TextColumn::make('periode')
                        ->label('Periode')
                        ->color('gray')
                        ->size(TextColumnSize::Small)
                        ->formatStateUsing($upper)
                        ->grow(false)
                        ->hiddenFrom('md'),
                    TextColumn::make('years')
                        ->label('Tahun')
                        ->color('gray')
                        ->size(TextColumnSize::Small)
                        ->grow(false)
                        ->hiddenFrom('md'),
                ]),
                TextColumn::make('implementation_estimate')
                    ->label('Estimasi Pelaksanaan')
                    ->icon('heroicon-m-clock')
                    ->color('gray')
                    ->size(TextColumnSize::Small)
                    ->date('j M Y')
                    ->placeholder('Belum dijadwalkan')
                    ->hiddenFrom('md'),
                self::statusColumn()
                    ->grow(false)
                    ->hiddenFrom('md'),
            ])->space(1),
        ];
    }

    private static function statusColumn(): TextColumn
    {
        return TextColumn::make('latestStatusLog.status.name')
            ->label('Status')
            ->badge()
            ->color(fn ($record): string => match (strtolower((string) ($record?->latestStatusLog?->status?->color ?? $record?->status?->color ?? 'gray'))) {
                'green' => 'green',
                'blue' => 'blue',
                'yellow' => 'yellow',
                'red' => 'red',
                default => 'gray',
            })
            ->default(fn ($record): string => $record?->status?->name ?? '-');
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

                    return str_contains($province, 'jakarta') && filled($get('regencies'));
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
