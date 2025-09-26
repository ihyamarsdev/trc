<?php

namespace App\Filament\User\Widgets;

use Filament\Tables;
use Illuminate\Database;
use Filament\Tables\Table;
use App\Models\RegistrationData;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Grouping\Group;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\Summarizers\Summarizer;

class SalesLeaderboard extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 2;
    protected static ?string $title = 'Leaderboard Sales';
    protected static ?string $heading = 'Leaderboard Sales';

    public function table(Table $table): Table
    {
        return $table
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
                ])
                ->orderByDesc('green_count')
                ->orderByDesc('blue_count')
                ->orderByDesc('yellow_count')
                ->orderBy('users_id') // tie-breaker opsional
            )
            ->columns([
                    Tables\Columns\TextColumn::make('users.name')->label('Sales'),
                    Tables\Columns\TextColumn::make('yellow')->label('Data Kuning')->summarize(
                        Summarizer::make()
                        ->label('')
                        ->using(fn (Database\Query\Builder $query) => $query->where('status_color', '=', 'yellow')->count())
                    ),
                    Tables\Columns\TextColumn::make('blue')->label('Data Biru')->summarize(
                        Summarizer::make()
                        ->label('')
                        ->using(fn (Database\Query\Builder $query) => $query->where('status_color', '=', 'blue')->count())
                    ),
                    Tables\Columns\TextColumn::make('green')->label('Data Hijau')->summarize(
                        Summarizer::make()
                        ->label('')
                        ->using(fn (Database\Query\Builder $query) => $query->where('status_color', '=', 'green')->count())
                    ),
                    Tables\Columns\TextColumn::make('schools')->label('Jumlah Sekolah')->summarize(
                        Summarizer::make()
                            ->label('')
                            ->using(fn (Database\Query\Builder $query) => $query->count('schools'))
                            ),
                    Tables\Columns\TextColumn::make('student_count')->label('Jumlah Siswa')->summarize(Sum::make()->label('')),
            ])
            ->groups([
                Group::make('users.name')
                    ->collapsible()
                    ->titlePrefixedWithLabel(false),
            ])
            ->defaultGroup('users.name')
            ->groupingSettingsHidden()
            ->groupsOnly();
        ;

    }
}
