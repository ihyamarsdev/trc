<?php

namespace App\Filament\User\Widgets;

use Filament\Tables;
use Illuminate\Database;
use Filament\Tables\Table;
use App\Filament\Enum\Jenjang;
use App\Filament\Enum\Program;
use App\Models\RegistrationData;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Grouping\Group;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\Summarizers\Summarizer;

class SalesLeaderboard extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 2;
    protected static ?string $title = 'Leaderboard Sales';
    protected static ?string $heading = 'Leaderboard Sales';





    public function table(Table $table): Table
    {
        $colorCountSubquery = function (string $color) {
            return DB::table('registration_data as r2')
                ->selectRaw('COUNT(*)')
                ->whereColumn('r2.users_id', 'registration_data.users_id')
                ->whereExists(function ($query) use ($color) {
                    $query->select(DB::raw(1))
                        ->from('registration_statuses')
                        ->join('statuses', 'statuses.id', '=', 'registration_statuses.status_id')
                        ->whereColumn('registration_statuses.registration_id', 'r2.id')
                        ->whereRaw('registration_statuses.id = (SELECT MAX(id) FROM registration_statuses WHERE registration_id = r2.id)')
                        ->where('statuses.color', $color);
                });
        };

        $summarizerCondition = function (Database\Query\Builder $query, string $color) {
            return $query->whereExists(function ($subQuery) use ($color) {
                $subQuery->select(DB::raw(1))
                    ->from('registration_statuses')
                    ->join('statuses', 'statuses.id', '=', 'registration_statuses.status_id')
                    ->whereColumn('registration_statuses.registration_id', 'registration_data.id')
                    ->whereRaw('registration_statuses.id = (SELECT MAX(id) FROM registration_statuses WHERE registration_id = registration_data.id)')
                    ->where('statuses.color', $color);
            })->count();
        };

        return $table
            ->poll('10s')
            ->query(
                RegistrationData::query()
                    ->addSelect([
                        'green_count' => $colorCountSubquery('green'),
                        'blue_count' => $colorCountSubquery('blue'),
                        'yellow_count' => $colorCountSubquery('yellow'),
                        'red_count' => $colorCountSubquery('red'),
                    ])
                    ->orderByDesc('green_count')
                    ->orderByDesc('blue_count')
                    ->orderByDesc('yellow_count')
                    ->orderByDesc('red_count')
                    ->orderBy('users_id') // tie-breaker opsional
            )
            ->columns([
                Tables\Columns\TextColumn::make('users.name')->label('Sales'),
                Tables\Columns\TextColumn::make('red')->alignCenter()->label('Data')->summarize(
                    Summarizer::make()
                        ->label('')
                        ->using(fn(Database\Query\Builder $query) => $summarizerCondition($query, 'red'))
                        ->formatStateUsing(fn($state) => '<span class="status-red" style="--light: #cc0000; --dark: #ff6b6b; color: var(--light); font-weight: 600;">' . $state . '</span>')
                        ->html()
                ),
                Tables\Columns\TextColumn::make('yellow')->alignCenter()->label('Data')->summarize(
                    Summarizer::make()
                        ->label('')
                        ->using(fn(Database\Query\Builder $query) => $summarizerCondition($query, 'yellow'))
                        ->formatStateUsing(fn($state) => '<span class="status-yellow" style="--light: #cc9900; --dark: #ffd93d; color: var(--light); font-weight: 600;">' . $state . '</span>')
                        ->html()
                ),
                Tables\Columns\TextColumn::make('blue')->alignCenter()->label('Data')->summarize(
                    Summarizer::make()
                        ->label('')
                        ->using(fn(Database\Query\Builder $query) => $summarizerCondition($query, 'blue'))
                        ->formatStateUsing(fn($state) => '<span class="status-blue" style="--light: #000099; --dark: #6bb3ff; color: var(--light); font-weight: 600;">' . $state . '</span>')
                        ->html()
                ),
                Tables\Columns\TextColumn::make('green')->alignCenter()->label('Data')->summarize(
                    Summarizer::make()
                        ->label('')
                        ->using(fn(Database\Query\Builder $query) => $summarizerCondition($query, 'green'))
                        ->formatStateUsing(fn($state) => '<span class="status-green" style="--light: #004400; --dark: #6bff6b; color: var(--light); font-weight: 600;">' . $state . '</span>')
                        ->html()
                ),
                Tables\Columns\TextColumn::make('schools')->alignCenter()->label('Jumlah Sekolah')->summarize(
                    Summarizer::make()
                        ->label('')
                        ->using(fn(Database\Query\Builder $query) => $query->count('schools'))
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
                    ->searchable()
            ])
            // ->groups([
            //     Group::make('users.name')
            //         ->collapsible()
            //         ->titlePrefixedWithLabel(false),
            // ])
            ->defaultGroup('users.name')
            ->groupingSettingsHidden()
            ->groupsOnly();

    }


}
