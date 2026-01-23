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
        return $table
            ->poll('10s')
            ->query(
                RegistrationData::query()
                    ->addSelect([
                        // hitung hijau per user
                        'green_count' => DB::table('registration_data as r2')
                            ->selectRaw('COUNT(*)')
                            ->whereColumn('r2.users_id', 'registration_data.users_id')
                            ->where('r2.status_color', 'green'),

                        // hitung biru per user
                        'blue_count' => DB::table('registration_data as r3')
                            ->selectRaw('COUNT(*)')
                            ->whereColumn('r3.users_id', 'registration_data.users_id')
                            ->where('r3.status_color', 'blue'),

                        // hitung kuning per user
                        'yellow_count' => DB::table('registration_data as r4')
                            ->selectRaw('COUNT(*)')
                            ->whereColumn('r4.users_id', 'registration_data.users_id')
                            ->where('r4.status_color', 'yellow'),

                        // hitung merah per user (TAMBAHAN)
                        'red_count' => DB::table('registration_data as r5')
                            ->selectRaw('COUNT(*)')
                            ->whereColumn('r5.users_id', 'registration_data.users_id')
                            ->where('r5.status_color', 'red'),
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
                         ->using(fn(Database\Query\Builder $query) => $query->where('status_color', '=', 'red')->count())
                          ->formatStateUsing(fn($state) => '<span style="color: #cc0000; -webkit-text-stroke: 0.5px #cc0000;">' . $state . '</span>')
                         ->html()
                 ),
                 Tables\Columns\TextColumn::make('yellow')->alignCenter()->label('Data')->summarize(
                     Summarizer::make()
                         ->label('')
                         ->using(fn(Database\Query\Builder $query) => $query->where('status_color', '=', 'yellow')->count())
                          ->formatStateUsing(fn($state) => '<span style="color: #cc9900; -webkit-text-stroke: 0.5px #cc9900;">' . $state . '</span>')
                         ->html()
                 ),
                 Tables\Columns\TextColumn::make('blue')->alignCenter()->label('Data')->summarize(
                     Summarizer::make()
                         ->label('')
                         ->using(fn(Database\Query\Builder $query) => $query->where('status_color', '=', 'blue')->count())
                          ->formatStateUsing(fn($state) => '<span style="color: #000099; -webkit-text-stroke: 0.5px #000099;">' . $state . '</span>')
                         ->html()
                 ),
                 Tables\Columns\TextColumn::make('green')->alignCenter()->label('Data')->summarize(
                     Summarizer::make()
                         ->label('')
                         ->using(fn(Database\Query\Builder $query) => $query->where('status_color', '=', 'green')->count())
                          ->formatStateUsing(fn($state) => '<span style="color: #004400; -webkit-text-stroke: 0.5px #004400;">' . $state . '</span>')
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
