<?php

namespace App\Filament\User\Widgets;

use App\Models\RegistrationData;
use App\Filament\Enum\Jenjang;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\Widget;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;

class SalesForceStatsWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 2;
    protected static ?string $heading = 'Rekap Program';

    protected static string $view = 'filament.user.widgets.sales-force-stats-widget';

    // Direct public properties for form binding
    public ?string $education_level = null;
    public ?string $years = null;

    public function mount(): void
    {
        $this->form->fill([
            'education_level' => null,
            'years' => null,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('education_level')
                    ->label('Jenjang')
                    ->options(Jenjang::list())
                    ->placeholder('Semua Jenjang')
                    ->live()
                    ->afterStateUpdated(fn() => $this->dispatch('$refresh')),
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
                    ->afterStateUpdated(fn() => $this->dispatch('$refresh')),
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

    public function getChartData(): array
    {
        $query = RegistrationData::query()
            ->when(!Auth::user()->hasRole(['admin', 'service', 'finance']), function ($query) {
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

        // Status colors matching SalesLeaderboard pattern
        // Using CSS variables for light/dark mode support
        $statusColors = [
            [
                'light' => '#cc0000', // Red (darker for light mode)
                'dark' => '#ff6b6b',  // Red (lighter for dark mode)
                'name' => 'red'
            ],
            [
                'light' => '#cc9900', // Yellow (darker for light mode)
                'dark' => '#ffd93d',  // Yellow (lighter for dark mode)
                'name' => 'yellow'
            ],
            [
                'light' => '#000099', // Blue (darker for light mode)
                'dark' => '#6bb3ff',  // Blue (lighter for dark mode)
                'name' => 'blue'
            ],
            [
                'light' => '#004400', // Green (darker for light mode)
                'dark' => '#6bff6b',  // Green (lighter for dark mode)
                'name' => 'green'
            ],
            [
                'light' => '#990000', // Dark Red
                'dark' => '#ff8888',  // Light Red
                'name' => 'dark-red'
            ],
            [
                'light' => '#996600', // Dark Yellow
                'dark' => '#ffe066',  // Light Yellow
                'name' => 'dark-yellow'
            ],
            [
                'light' => '#000066', // Dark Blue
                'dark' => '#5599ff',  // Light Blue
                'name' => 'dark-blue'
            ],
            [
                'light' => '#003300', // Dark Green
                'dark' => '#55ff55',  // Light Green
                'name' => 'dark-green'
            ],
            [
                'light' => '#660000', // Very Dark Red
                'dark' => '#ffaaaa',  // Very Light Red
                'name' => 'very-dark-red'
            ],
            [
                'light' => '#664400', // Very Dark Yellow
                'dark' => '#ffdd88',  // Very Light Yellow
                'name' => 'very-dark-yellow'
            ],
        ];

        $chartData = [];
        $labels = [];
        $backgroundColors = [];
        $details = [];
        $totalStudents = 0;
        $totalSchools = 0;

        foreach ($data as $index => $item) {
            $studentCount = (int) $item->total_students;
            $schoolCount = (int) $item->school_count;
            $colorInfo = $statusColors[$index % count($statusColors)];
            
            $labels[] = strtoupper($item->type);
            $chartData[] = $studentCount;
            // Use dark color for chart (works well in both modes)
            $backgroundColors[] = $colorInfo['dark'];
            
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

        // Calculate percentages for each program
        foreach ($details as &$detail) {
            $detail['percentage'] = $totalStudents > 0 
                ? round(($detail['student_count'] / $totalStudents) * 100, 1) 
                : 0;
            $detail['avg_students_per_school'] = $detail['school_count'] > 0
                ? round($detail['student_count'] / $detail['school_count'], 1)
                : 0;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $chartData,
                    'backgroundColor' => $backgroundColors,
                    'borderWidth' => 3,
                    'borderColor' => '#ffffff',
                    'hoverBorderWidth' => 4,
                    'hoverBorderColor' => '#ffffff',
                ],
            ],
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
