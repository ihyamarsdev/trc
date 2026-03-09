<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RegistrationChartWidget extends ChartWidget
{
    protected ?string $heading = 'Grafik Pendaftaran Sekolah';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $data = \App\Models\RegistrationData::selectRaw('strftime("%Y-%m", date_register) as month, count(*) as count')
                ->groupBy('month')
                ->orderBy('month')
                ->get();
        } else {
            $data = \App\Models\RegistrationData::selectRaw('DATE_FORMAT(date_register, "%Y-%m") as month, count(*) as count')
                ->groupBy('month')
                ->orderBy('month')
                ->get();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Sekolah',
                    'data' => $data->pluck('count')->toArray(),
                    'fill' => 'start',
                ],
            ],
            'labels' => $data->pluck('month')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
