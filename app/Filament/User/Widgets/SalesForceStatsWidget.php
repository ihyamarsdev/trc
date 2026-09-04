<?php

namespace App\Filament\User\Widgets;

use App\Filament\Enum\Jenjang;
use App\Filament\Enum\Program;
use App\Models\RegistrationData;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

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

        // Initialize with all programs defined in Program enum
        $programs = [];
        foreach (Program::cases() as $case) {
            $programs[$case->value] = [
                'type' => $case->value,
                'label' => $case->label(),
                'school_count' => 0,
                'student_count' => 0,
            ];
        }

        // Aggregate registration data grouped by normalized type
        $records = $query->selectRaw('LOWER(TRIM(type)) as normalized_type, COUNT(*) as school_count, COALESCE(SUM(student_count), 0) as total_students')
            ->whereNotNull('type')
            ->where('type', '!=', '')
            ->groupByRaw('LOWER(TRIM(type))')
            ->get();

        foreach ($records as $record) {
            $typeKey = strtolower(trim((string) $record->normalized_type));
            if ($typeKey === '') {
                continue;
            }

            if (! isset($programs[$typeKey])) {
                $enumProgram = Program::tryFrom($typeKey);
                $programs[$typeKey] = [
                    'type' => $typeKey,
                    'label' => $enumProgram?->label() ?? strtoupper($typeKey),
                    'school_count' => 0,
                    'student_count' => 0,
                ];
            }

            $programs[$typeKey]['school_count'] = (int) $record->school_count;
            $programs[$typeKey]['student_count'] = (int) $record->total_students;
        }

        $totalStudents = array_sum(array_column($programs, 'student_count'));
        $totalSchools = array_sum(array_column($programs, 'school_count'));

        // Status colors matching SalesLeaderboard pattern and vibrant accents
        // Using CSS variables for light/dark mode support
        $statusColors = [
            [
                'light' => '#cc0000', // Red
                'dark' => '#ff6b6b',
                'name' => 'red',
            ],
            [
                'light' => '#cc9900', // Yellow
                'dark' => '#ffd93d',
                'name' => 'yellow',
            ],
            [
                'light' => '#000099', // Blue
                'dark' => '#6bb3ff',
                'name' => 'blue',
            ],
            [
                'light' => '#004400', // Green
                'dark' => '#6bff6b',
                'name' => 'green',
            ],
            [
                'light' => '#7c3aed', // Purple
                'dark' => '#a78bfa',
                'name' => 'purple',
            ],
            [
                'light' => '#0891b2', // Cyan
                'dark' => '#38bdf8',
                'name' => 'cyan',
            ],
            [
                'light' => '#ea580c', // Orange
                'dark' => '#fb923c',
                'name' => 'orange',
            ],
            [
                'light' => '#db2777', // Pink
                'dark' => '#f472b6',
                'name' => 'pink',
            ],
            [
                'light' => '#059669', // Emerald
                'dark' => '#34d399',
                'name' => 'emerald',
            ],
            [
                'light' => '#4f46e5', // Indigo
                'dark' => '#818cf8',
                'name' => 'indigo',
            ],
        ];

        $chartData = [];
        $labels = [];
        $backgroundColors = [];
        $details = [];

        $index = 0;
        foreach ($programs as $item) {
            $studentCount = $item['student_count'];
            $schoolCount = $item['school_count'];
            $colorInfo = $statusColors[$index % count($statusColors)];

            $labels[] = $item['label'];
            $chartData[] = $studentCount;
            // Use dark color for chart (works well in both modes)
            $backgroundColors[] = $colorInfo['dark'];

            $rawPercentage = $totalStudents > 0
                ? ($studentCount / $totalStudents) * 100
                : 0;

            $percentage = round($rawPercentage, 2);

            if ($totalStudents === 0 || $studentCount === 0) {
                $percentageFormatted = '0%';
            } else {
                $round2 = round($rawPercentage, 2);
                if ($round2 >= 0.1) {
                    $percentageFormatted = number_format($rawPercentage, 1).'%';
                } elseif ($round2 >= 0.01) {
                    $percentageFormatted = number_format($rawPercentage, 2).'%';
                } else {
                    $percentageFormatted = '<0.01%';
                }
            }

            $avgPerSchool = $schoolCount > 0
                ? round($studentCount / $schoolCount, 1)
                : 0;

            $details[] = [
                'label' => $item['label'],
                'school_count' => $schoolCount,
                'student_count' => $studentCount,
                'percentage' => $percentage,
                'percentage_formatted' => $percentageFormatted,
                'avg_students_per_school' => $avgPerSchool,
                'color' => $colorInfo['dark'],
                'color_light' => $colorInfo['light'],
                'color_dark' => $colorInfo['dark'],
                'color_name' => $colorInfo['name'],
                'program_type' => $item['type'],
            ];

            $index++;
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
                'programs' => count($programs),
                'schools' => $totalSchools,
                'students' => $totalStudents,
                'avg_students_per_school' => $totalSchools > 0 ? round($totalStudents / $totalSchools, 1) : 0,
            ],
        ];
    }
}
