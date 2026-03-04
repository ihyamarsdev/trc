<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

use App\Models\RegistrationData;

class StatsOverviewWidget extends BaseStatsOverviewWidget
{
    protected static ?int $sort = 1;
    protected function getStats(): array
    {
        return [
            Stat::make('Total Sekolah', RegistrationData::count())
                ->description('Semua sekolah terdaftar')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),
            Stat::make('Total Siswa Terdaftar', RegistrationData::sum('student_count'))
                ->description('Jumlah seluruh siswa')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),
            Stat::make('Total Pemasukan', 'Rp ' . number_format(RegistrationData::sum('total_invoice'), 0, ',', '.'))
                ->description('Jumlah seluruh invoice')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('warning'),
        ];
    }
}
