<?php

namespace App\Filament\User\Resources\Finance\Monitoring\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database;

class AllProgramFinanceTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('monthYear')->label('Bulan'),
                TextColumn::make('schools')
                    ->label('Jumlah Sekolah')
                    ->summarize(
                        Summarizer::make()->label('')->using(fn (Database\Query\Builder $query) => $query->count('schools'))
                    ),
                TextColumn::make('payment')
                    ->label('Jumlah Terbayarkan')
                    ->summarize(
                        Summarizer::make()->label('')->using(fn (Database\Query\Builder $query) => $query->count('payment'))
                    ),
            ])
            ->filters([
                SelectFilter::make('periode')
                    ->label('Periode')
                    ->options([
                        'Januari - Juni' => 'Januari - Juni',
                        'Juli - Desember' => 'Juli - Desember',
                    ])
                    ->preload()
                    ->indicator('Periode'),
            ])
            ->recordActions([])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->groups([
                Group::make('monthYear')
                    ->collapsible()
                    ->titlePrefixedWithLabel(false),
            ])
            ->defaultGroup('monthYear')
            ->groupingSettingsHidden()
            ->groupsOnly();
    }
}
