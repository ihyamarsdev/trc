<?php

namespace App\Filament\User\Resources\Admin\Tables;

use App\Filament\Enum\Periode;
use App\Filament\Enum\Program;
use App\Models\RegistrationData;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class AdminTable
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
