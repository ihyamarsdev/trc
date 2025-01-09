<?php

namespace App\Filament\User\Widgets;

use Filament\Tables;
use App\Models\Schools;
use Filament\Tables\Table;
use Database\Query\Builder;
use App\Models\RegistrationData;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Grouping\Group;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Illuminate\Database;

class SalesForceStatsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 2;
    protected static ?string $title = 'Rekap Program';
    protected static ?string $heading = 'Rekap Program';

    public static function canView(): bool
    {
        return Auth::user()->hasRole(['salesforce','admin']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                RegistrationData::query()->when(!Auth::user()->hasRole(['admin']), function ($query) {
                    // Assuming you have a 'user_id' field in your 'apps' table
                    return $query->where('users_id', Auth::id());
                })
            )
            ->columns([
                Tables\Columns\TextColumn::make('type')->label('Program'),
                Tables\Columns\TextColumn::make('schools')->label('Jumlah Sekolah')->summarize(
                    Summarizer::make()
                    ->label('')
                    ->using(fn (Database\Query\Builder $query) => $query->count('schools'))
                ),
                Tables\Columns\TextColumn::make('student_count')->label('Jumlah Siswa')->summarize(Sum::make()->label('')),
            ])
            ->groups([
                Group::make('type')
                    ->collapsible()
                    ->titlePrefixedWithLabel(false),
            ])
            ->defaultGroup('type')
            ->groupingSettingsHidden()
            ->groupsOnly();
    }
}
