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
use Illuminate\Support\Facades\DB;

class SalesLeaderboard extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    protected static ?string $title = 'Leaderboard Sales';

    protected static ?string $heading = 'Leaderboard Sales';

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

        $summarizerCondition = function (Database\Query\Builder $query, string $color) {
            return (clone $query)
                ->where('latest_statuses.color', $color)
                ->count();
        };

        /** @var \Illuminate\Database\Eloquent\Builder $leaderboardQuery */
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
                Tables\Columns\TextColumn::make('red')->alignCenter()->label('Data')->summarize(
                    Summarizer::make()
                        ->label('')
                        ->using(fn (Database\Query\Builder $query) => $summarizerCondition($query, 'red'))
                        ->formatStateUsing(fn ($state) => '<span class="status-red" style="--light: #cc0000; --dark: #ff6b6b; color: var(--light); font-weight: 600;">'.$state.'</span>')
                        ->html()
                ),
                Tables\Columns\TextColumn::make('yellow')->alignCenter()->label('Data')->summarize(
                    Summarizer::make()
                        ->label('')
                        ->using(fn (Database\Query\Builder $query) => $summarizerCondition($query, 'yellow'))
                        ->formatStateUsing(fn ($state) => '<span class="status-yellow" style="--light: #cc9900; --dark: #ffd93d; color: var(--light); font-weight: 600;">'.$state.'</span>')
                        ->html()
                ),
                Tables\Columns\TextColumn::make('blue')->alignCenter()->label('Data')->summarize(
                    Summarizer::make()
                        ->label('')
                        ->using(fn (Database\Query\Builder $query) => $summarizerCondition($query, 'blue'))
                        ->formatStateUsing(fn ($state) => '<span class="status-blue" style="--light: #000099; --dark: #6bb3ff; color: var(--light); font-weight: 600;">'.$state.'</span>')
                        ->html()
                ),
                Tables\Columns\TextColumn::make('green')->alignCenter()->label('Data')->summarize(
                    Summarizer::make()
                        ->label('')
                        ->using(fn (Database\Query\Builder $query) => $summarizerCondition($query, 'green'))
                        ->formatStateUsing(fn ($state) => '<span class="status-green" style="--light: #004400; --dark: #6bff6b; color: var(--light); font-weight: 600;">'.$state.'</span>')
                        ->html()
                ),
                Tables\Columns\TextColumn::make('schools')->alignCenter()->label('Jumlah Sekolah')->summarize(
                    Summarizer::make()
                        ->label('')
                        ->using(fn (Database\Query\Builder $query) => $query->count('schools'))
                ),
                Tables\Columns\TextColumn::make('student_count')->alignCenter()->label('Jumlah Siswa')->summarize(Sum::make()->label('')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Program')
                    ->options(Program::list())
                    ->preload()
                    ->indicator('Program'),
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
                Tables\Filters\SelectFilter::make('warna')
                    ->label('Status')
                    ->options([
                        'green' => 'Hijau',
                        'blue' => 'Biru',
                        'yellow' => 'Kuning',
                        'red' => 'Merah',
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        if (! empty($data['value'])) {
                            return $query->where('latest_statuses.color', $data['value']);
                        }

                        return $query;
                    }),
            ])
            ->defaultGroup('users.name')
            ->groupingSettingsHidden()
            ->groupsOnly();

    }
}
