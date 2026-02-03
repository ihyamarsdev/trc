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

        $colors = [
            '#FF6384', // Red/Pink
            '#36A2EB', // Blue
            '#FFCE56', // Yellow
            '#4BC0C0', // Teal
            '#9966FF', // Purple
            '#FF9F40', // Orange
            '#E7E9ED', // Gray
            '#7C4DFF', // Deep Purple
        ];

        $chartData = [];
        $labels = [];
        $backgroundColors = [];
        $details = [];

        foreach ($data as $index => $item) {
            $labels[] = strtoupper($item->type);
            $chartData[] = (int) $item->total_students;
            $backgroundColors[] = $colors[$index % count($colors)];

            $details[] = [
                'label' => strtoupper($item->type),
                'school_count' => $item->school_count,
                'student_count' => $item->total_students,
                'color' => $colors[$index % count($colors)],
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $chartData,
                    'backgroundColor' => $backgroundColors,
                    'borderWidth' => 2,
                    'borderColor' => '#ffffff',
                ],
            ],
            'details' => $details,
            'totals' => [
                'schools' => $data->sum('school_count'),
                'students' => $data->sum('total_students'),
            ],
        ];
    }
}
