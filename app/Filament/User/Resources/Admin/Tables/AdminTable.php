<?php

namespace App\Filament\User\Resources\Admin\Tables;

use App\Filament\Enum\Periode;
use App\Filament\Enum\Program;
use App\Models\RegistrationData;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Contracts\Database\Eloquent\Builder;

class AdminTable
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
                                'blue' => 'blue',
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
            SelectFilter::make('users')
                ->label('Salesforce')
                ->relationship('users', 'name')
                ->searchable()
                ->preload()
                ->indicator('Salesforce'),
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
            SelectFilter::make('status_color')
                ->label('Status Warna')
                ->options([
                    'red' => 'Merah',
                    'yellow' => 'Kuning',
                    'blue' => 'Biru',
                    'green' => 'Hijau',
                ])
                ->preload()
                ->indicator('Status Warna')
                ->query(function (Builder $query, array $data) {
                    if (empty($data['value'])) {
                        return;
                    }

                    $query->whereHas(
                        'status',
                        fn (Builder $q) => $q->where('color', $data['value'])
                    );
                }),
            SelectFilter::make('years')
                ->label('Tahun')
                ->options(function () {
                    // Mengambil daftar tahun unik yang benar-benar ada di database
                    return RegistrationData::query() // Ganti Sales dengan nama Model Anda
                        ->whereNotNull('years')
                        ->distinct()
                        ->orderBy('years', 'desc')
                        ->pluck('years', 'years')
                        ->toArray();
                })
                ->searchable()
                ->preload(),
        ];
    }
}
