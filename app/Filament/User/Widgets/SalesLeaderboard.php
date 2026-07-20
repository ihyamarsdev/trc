<?php

namespace App\Filament\User\Widgets;

use App\Filament\Enum\Jenjang;
use App\Filament\Enum\Program;
use App\Models\RegistrationData;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SalesLeaderboard extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    protected static ?string $title = 'Leaderboard Sales';

    protected static ?string $heading = 'Leaderboard Sales';

    protected static string $view = 'filament.user.widgets.sales-leaderboard';

    public ?int $selectedUserId = null;

    public function getUserRegistrationMap(): array
    {
        return RegistrationData::query()
            ->join('users', 'users.id', '=', 'registration_data.users_id')
            ->select('users.name', 'registration_data.id', 'registration_data.users_id')
            ->get()
            ->unique('users_id')
            ->mapWithKeys(fn ($row) => [$row->name => $row->id])
            ->toArray();
    }

    public function openSalesSchools(int $registrationId): void
    {
        $registration = RegistrationData::find($registrationId);
        $this->selectedUserId = $registration?->users_id;
        $this->dispatch('open-modal', id: 'sales-schools-modal');
    }

    public function table(Table $table): Table
    {
        $latestStatusSubquery = static function (): Database\Query\Builder {
            return DB::table('registration_statuses as rs')
                ->select(['rs.registration_id', 'rs.status_id'])
                ->joinSub(
                    DB::table('registration_statuses')
                        ->selectRaw('registration_id, MAX(id) as latest_id')
                        ->groupBy('registration_id'),
                    'latest_rs',
                    fn (Database\Query\JoinClause $join) => $join->on('latest_rs.latest_id', '=', 'rs.id'),
                );
        };

        $leaderboardAggregateSubquery = static function () use ($latestStatusSubquery): Database\Query\Builder {
            return DB::table('registration_data as r2')
                ->leftJoinSub(
                    $latestStatusSubquery(),
                    'latest_registration_statuses_r2',
                    fn (Database\Query\JoinClause $join) => $join->on('latest_registration_statuses_r2.registration_id', '=', 'r2.id'),
                )
                ->leftJoin('statuses as latest_statuses_r2', 'latest_statuses_r2.id', '=', 'latest_registration_statuses_r2.status_id')
                ->select('r2.users_id')
                ->selectRaw("SUM(CASE WHEN latest_statuses_r2.color = 'green' THEN 1 ELSE 0 END) as green_count")
                ->selectRaw("SUM(CASE WHEN latest_statuses_r2.color = 'blue' THEN 1 ELSE 0 END) as blue_count")
                ->selectRaw("SUM(CASE WHEN latest_statuses_r2.color = 'yellow' THEN 1 ELSE 0 END) as yellow_count")
                ->selectRaw("SUM(CASE WHEN latest_statuses_r2.color = 'red' THEN 1 ELSE 0 END) as red_count")
                ->groupBy('r2.users_id');
        };

        /** @var Builder $leaderboardQuery */
        $leaderboardQuery = RegistrationData::query()
            ->leftJoinSub(
                $latestStatusSubquery(),
                'latest_registration_statuses',
                fn (Database\Query\JoinClause $join) => $join->on('latest_registration_statuses.registration_id', '=', 'registration_data.id'),
            )
            ->leftJoin('statuses as latest_statuses', 'latest_statuses.id', '=', 'latest_registration_statuses.status_id')
            ->leftJoinSub(
                $leaderboardAggregateSubquery(),
                'leaderboard_aggregate',
                fn (Database\Query\JoinClause $join) => $join->on('leaderboard_aggregate.users_id', '=', 'registration_data.users_id'),
            )
            ->select('registration_data.*')
            ->selectRaw("COALESCE(latest_statuses.color, '') as latest_status_color")
            ->selectRaw('COALESCE(leaderboard_aggregate.green_count, 0) as green_count')
            ->selectRaw('COALESCE(leaderboard_aggregate.blue_count, 0) as blue_count')
            ->selectRaw('COALESCE(leaderboard_aggregate.yellow_count, 0) as yellow_count')
            ->selectRaw('COALESCE(leaderboard_aggregate.red_count, 0) as red_count');

        $leaderboardQuery
            ->orderByDesc('green_count')
            ->orderByDesc('blue_count')
            ->orderByDesc('yellow_count')
            ->orderByDesc('red_count')
            ->orderBy('users_id'); // tie-breaker opsional

        return $table
            ->poll('30s')
            ->query($leaderboardQuery)
            ->columns([
                Tables\Columns\TextColumn::make('users.name')->label('Sales'),
                Tables\Columns\TextColumn::make('program_details')
                    ->label('Detail Program')
                    ->html()
                    ->summarize(
                        Summarizer::make()
                            ->label('')
                            ->html()
                            ->using(function (Database\Query\Builder $query) {
                                $results = (clone $query)
                                    ->select('type')
                                    ->selectRaw('COUNT(schools) as schools_count')
                                    ->selectRaw('SUM(student_count) as students_sum')
                                    ->whereNotNull('type')
                                    ->groupBy('type')
                                    ->get();

                                $lines = $results->map(function ($row) {
                                    $program = Program::tryFrom(strtolower($row->type));
                                    $programLabel = $program ? $program->label() : strtoupper($row->type);
                                    $studentsSum = (int) $row->students_sum;

                                    return "<div class='flex items-center justify-between gap-4 text-xs'>".
                                           "<span class='font-semibold text-gray-800 dark:text-slate-100'>{$programLabel}:</span>".
                                           "<span class='font-medium text-gray-600 dark:text-slate-300'>{$row->schools_count} Sekolah / {$studentsSum} Siswa</span>".
                                           '</div>';
                                });

                                return "<div class='inline-block text-left py-1 min-w-[210px] space-y-0.5'>".$lines->implode('').'</div>';
                            })
                    ),
                Tables\Columns\TextColumn::make('status_details')
                    ->label('Status')
                    ->html()
                    ->summarize(
                        Summarizer::make()
                            ->label('')
                            ->html()
                            ->using(function (Database\Query\Builder $query) {
                                $results = (clone $query)
                                    ->select('latest_status_color')
                                    ->selectRaw('COUNT(*) as data_count')
                                    ->selectRaw('COALESCE(SUM(student_count), 0) as students_sum')
                                    ->groupBy('latest_status_color')
                                    ->get()
                                    ->keyBy('latest_status_color');

                                $colors = [
                                    'red' => 'Merah',
                                    'yellow' => 'Kuning',
                                    'blue' => 'Biru',
                                    'green' => 'Hijau',
                                ];

                                $lines = [];
                                foreach ($colors as $colorKey => $colorLabel) {
                                    $row = $results->get($colorKey);
                                    $dataCount = (int) ($row?->data_count ?? 0);
                                    $studentsSum = (int) ($row?->students_sum ?? 0);

                                    $lines[] = "<div class='flex items-center justify-between gap-4 text-xs'>".
                                           "<span class='font-semibold status-{$colorKey}'>{$colorLabel}:</span>".
                                           "<span class='font-semibold status-{$colorKey}'>{$dataCount} Data / {$studentsSum} Siswa</span>".
                                           '</div>';
                                }

                                return "<div class='inline-block text-left py-1 min-w-[210px] space-y-0.5'>".implode('', $lines).'</div>';
                            })
                    ),
                Tables\Columns\TextColumn::make('schools')->alignCenter()->label('Jumlah Sekolah')->summarize(
                    Summarizer::make()
                        ->label('')
                        ->using(fn (Database\Query\Builder $query) => $query->count('schools'))
                ),
                Tables\Columns\TextColumn::make('student_count')->alignCenter()->label('Jumlah Siswa')->summarize(Sum::make()->label('')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('education_level')
                    ->label('Jenjang')
                    ->options(Jenjang::list())
                    ->preload()
                    ->indicator('Jenjang'),
                Tables\Filters\SelectFilter::make('years')
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
                    ->searchable(),
            ])
            ->defaultGroup('users.name')
            ->groupingSettingsHidden()
            ->groupsOnly();

    }
}
