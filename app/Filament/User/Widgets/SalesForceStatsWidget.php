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
use App\Filament\Enum\Jenjang;
use Illuminate\Database;

class SalesForceStatsWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 2;
    protected static ?string $title = 'Rekap Program';
    protected static ?string $heading = 'Rekap Program';

    // public static function canView(): bool
    // {
    //     return Auth::user()->hasRole(['sales','admin']);
    // }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                RegistrationData::query()->when(!Auth::user()->hasRole(['admin', 'service', 'finance']), function ($query) {
                    // Assuming you have a 'user_id' field in your 'apps' table
                    return $query->where('users_id', Auth::id());
                })
            )
            ->columns([
                Tables\Columns\TextColumn::make('type')->label('Program')->extraAttributes(["class" => "uppercase"]),
                Tables\Columns\TextColumn::make('schools')->label('Jumlah Sekolah')->alignCenter()->summarize(
                    Summarizer::make()
                        ->label('')
                        ->using(fn(Database\Query\Builder $query) => $query->count('schools'))
                ),
                Tables\Columns\TextColumn::make('student_count')->label('Jumlah Siswa')->alignCenter()->summarize(Sum::make()->label('')),
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
                    ->searchable()
            ])
            ->groups([
                Group::make('type')
                    ->collapsible()
                    ->titlePrefixedWithLabel(false)
                    ->getTitleFromRecordUsing(fn($record): string => strtoupper($record->type)),
            ])
            ->defaultGroup('type')
            ->groupingSettingsHidden()
            ->groupsOnly();
    }
}
