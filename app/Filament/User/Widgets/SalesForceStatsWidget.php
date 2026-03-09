<?php

namespace App\Filament\User\Widgets;

use App\Filament\Enum\Jenjang;
use App\Models\RegistrationData;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class SalesForceStatsWidget extends Widget implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    protected static ?string $heading = 'Rekap Program';

    protected string $view = 'filament.user.widgets.sales-force-stats-widget';

    public ?string $education_level = null;

    public ?string $years = null;

    public string $period = 'week';

    public function mount(): void
    {
        $this->form->fill([
            'education_level' => null,
            'years' => null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('education_level')
                    ->label('Jenjang')
                    ->options(Jenjang::list())
                    ->placeholder('Semua Jenjang')
                    ->live()
                    ->afterStateUpdated(fn () => $this->dispatch('$refresh')),
                Select::make('years')
                    ->label('Tahun')
                    ->options(function () {
                        return RegistrationData::query()
                            ->whereNotNull('years')
                            ->distinct()
                            ->orderBy('years', 'desc')
                            ->pluck('years', 'years')
                            ->toArray();
                    })
                    ->placeholder('Semua Tahun')
                    ->live()
                    ->afterStateUpdated(fn () => $this->dispatch('$refresh')),
            ]);
    }

    public function resetFilters(): void
    {
        $this->education_level = null;
        $this->years = null;
        $this->form->fill([
            'education_level' => null,
            'years' => null,
        ]);
    }

    public function setPeriod(string $period): void
    {
        $this->period = $period;
    }

    public function getLineChartData(): array
    {
        [$labels, $startDate, $bucketFn] = match ($this->period) {
            'day' => $this->buildDayConfig(),
            'month' => $this->buildMonthConfig(),
            default => $this->buildWeekConfig(),
        };

        // Fetch raw rows — no SQL date functions (SQLite + MySQL compatible)
        $rows = RegistrationData::query()
            ->when(! Auth::user()->hasRole(['admin', 'service', 'finance']), fn ($q) => $q->where('users_id', Auth::id()))
            ->when($this->education_level, fn ($q) => $q->where('education_level', $this->education_level))
            ->when($this->years, fn ($q) => $q->where('years', $this->years))
            ->whereNotNull('type')
            ->whereNotNull('schools')
            ->where('created_at', '>=', $startDate)
            ->select(['type', 'created_at'])
            ->get();

        // Bucket in PHP via Carbon closure — count schools (1 row = 1 school)
        $aggregated = [];
        foreach ($rows as $row) {
            $key = $bucketFn(Carbon::parse($row->created_at));
            $aggregated[$row->type][$key] = ($aggregated[$row->type][$key] ?? 0) + 1;
        }

        $types = array_keys($aggregated);
        sort($types);

        $palette = [
            '#38bdf8', '#a3e635', '#fb923c', '#f472b6',
            '#c084fc', '#34d399', '#fbbf24', '#f87171',
            '#60a5fa', '#2dd4bf',
        ];

        $datasets = [];
        foreach ($types as $index => $type) {
            $color = $palette[$index % count($palette)];
            $datasets[] = [
                'label' => strtoupper($type),
                'data' => array_map(fn ($key) => $aggregated[$type][$key] ?? 0, $labels['keys']),
                'borderColor' => $color,
                'backgroundColor' => $color.'33',
                'pointBackgroundColor' => $color,
                'pointBorderColor' => '#ffffff',
                'pointBorderWidth' => 2,
                'pointRadius' => 4,
                'pointHoverRadius' => 6,
                'tension' => 0.4,
                'fill' => true,
                'borderWidth' => 2,
            ];
        }

        return [
            'labels' => $labels['display'],
            'datasets' => $datasets,
        ];
    }

    /** @return array{array{keys: array<string>, display: array<string>}, Carbon, \Closure} */
    private function buildDayConfig(): array
    {
        $start = Carbon::now()->subDays(29)->startOfDay();
        $keys = [];
        $display = [];

        for ($i = 0; $i < 30; $i++) {
            $day = $start->copy()->addDays($i);
            $keys[] = $day->format('Y-m-d');
            $display[] = $day->translatedFormat('d M');
        }

        return [['keys' => $keys, 'display' => $display], $start, fn (Carbon $dt) => $dt->format('Y-m-d')];
    }

    /** @return array{array{keys: array<string>, display: array<string>}, Carbon, \Closure} */
    private function buildWeekConfig(): array
    {
        $start = Carbon::now()->subWeeks(11)->startOfWeek();
        $keys = [];
        $display = [];

        for ($i = 0; $i < 12; $i++) {
            $week = $start->copy()->addWeeks($i);
            $keys[] = $week->format('Y-m-d'); // Monday of that week as key
            $display[] = 'W'.$week->format('W').' '.$week->translatedFormat('M');
        }

        return [['keys' => $keys, 'display' => $display], $start, fn (Carbon $dt) => $dt->copy()->startOfWeek()->format('Y-m-d')];
    }

    /** @return array{array{keys: array<string>, display: array<string>}, Carbon, \Closure} */
    private function buildMonthConfig(): array
    {
        $start = Carbon::now()->subMonths(11)->startOfMonth();
        $keys = [];
        $display = [];

        for ($i = 0; $i < 12; $i++) {
            $month = $start->copy()->addMonths($i);
            $keys[] = $month->format('Y-m');
            $display[] = $month->translatedFormat('M Y');
        }

        return [['keys' => $keys, 'display' => $display], $start, fn (Carbon $dt) => $dt->format('Y-m')];
    }

    public function getChartData(): array
    {
        $query = RegistrationData::query()
            ->when(! Auth::user()->hasRole(['admin', 'service', 'finance']), function ($query) {
                return $query->where('users_id', Auth::id());
            })
            ->when($this->education_level, function ($query) {
                return $query->where('education_level', $this->education_level);
            })
            ->when($this->years, function ($query) {
                return $query->where('years', $this->years);
            });

        $data = $query->selectRaw('type, COUNT(schools) as school_count, SUM(student_count) as total_students')
            ->groupBy('type')
            ->get();

        $statusColors = [
            ['light' => '#cc0000', 'dark' => '#ff6b6b', 'name' => 'red'],
            ['light' => '#cc9900', 'dark' => '#ffd93d', 'name' => 'yellow'],
            ['light' => '#000099', 'dark' => '#6bb3ff', 'name' => 'blue'],
            ['light' => '#004400', 'dark' => '#6bff6b', 'name' => 'green'],
            ['light' => '#990000', 'dark' => '#ff8888', 'name' => 'dark-red'],
            ['light' => '#996600', 'dark' => '#ffe066', 'name' => 'dark-yellow'],
            ['light' => '#000066', 'dark' => '#5599ff', 'name' => 'dark-blue'],
            ['light' => '#003300', 'dark' => '#55ff55', 'name' => 'dark-green'],
            ['light' => '#660000', 'dark' => '#ffaaaa', 'name' => 'very-dark-red'],
            ['light' => '#664400', 'dark' => '#ffdd88', 'name' => 'very-dark-yellow'],
        ];

        $details = [];
        $totalStudents = 0;
        $totalSchools = 0;

        foreach ($data as $index => $item) {
            $studentCount = (int) $item->total_students;
            $schoolCount = (int) $item->school_count;
            $colorInfo = $statusColors[$index % count($statusColors)];

            $totalStudents += $studentCount;
            $totalSchools += $schoolCount;

            $details[] = [
                'label' => strtoupper($item->type),
                'school_count' => $schoolCount,
                'student_count' => $studentCount,
                'color' => $colorInfo['dark'],
                'color_light' => $colorInfo['light'],
                'color_dark' => $colorInfo['dark'],
                'color_name' => $colorInfo['name'],
                'program_type' => $item->type,
            ];
        }

        foreach ($details as &$detail) {
            $detail['percentage'] = $totalStudents > 0
                ? round(($detail['student_count'] / $totalStudents) * 100, 1)
                : 0;
            $detail['avg_students_per_school'] = $detail['school_count'] > 0
                ? round($detail['student_count'] / $detail['school_count'], 1)
                : 0;
        }

        return [
            'details' => $details,
            'totals' => [
                'programs' => count($data),
                'schools' => $totalSchools,
                'students' => $totalStudents,
                'avg_students_per_school' => $totalSchools > 0 ? round($totalStudents / $totalSchools, 1) : 0,
            ],
        ];
    }
}
