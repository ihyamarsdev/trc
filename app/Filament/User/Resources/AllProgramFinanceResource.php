<?php

namespace App\Filament\User\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Illuminate\Database;
use App\Models\SchoolYear;
use Filament\Tables\Table;
use App\Models\RegistrationData;
use Filament\Resources\Resource;
use App\Models\AllProgramFinance;
use Filament\Tables\Grouping\Group;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\User\Resources\AllProgramFinanceResource\Pages;
use App\Filament\User\Resources\AllProgramFinanceResource\RelationManagers;

class AllProgramFinanceResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $title = 'Rekap All Program';
    protected static ?string $navigationGroup = 'Rekap Finance';
    protected static ?string $navigationLabel = 'All Program';
    protected static ?string $modelLabel = 'Rekap All Program';
    protected static ?string $slug = 'rekap-all-program-finance';
    protected static bool $shouldRegisterNavigation = true;

    public static function canViewAny(): bool
    {
        return Auth::user()->hasRole('finance');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('monthYear')
                    ->label('Bulan'),
                Tables\Columns\TextColumn::make('schools')
                    ->label('Jumlah Sekolah')
                    ->summarize(
                        Summarizer::make()
                            ->label('')
                            ->using(fn (Database\Query\Builder $query) => $query->count('schools'))
                    ),
                Tables\Columns\TextColumn::make('payment')
                    ->label('Jumlah Siswa Bayar')
                    ->summarize(
                        Summarizer::make()
                            ->label('')
                            ->using(fn (Database\Query\Builder $query) => $query->count('payment'))
                    ),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('periode')
                    ->label('Periode')
                    ->options([
                        'Januari - Juni' => 'Januari - Juni',
                        'Juli - Desember' => 'Juli - Desember',
                    ])
                    ->preload()
                    ->indicator('Periode'),
                Tables\Filters\SelectFilter::make('school_years_id')
                    ->label('Tahun Ajaran')
                    ->options(SchoolYear::all()->pluck('name', 'id'))
                    ->preload()
                    ->searchable(),
            ])
            ->actions([
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAllProgramFinances::route('/'),
        ];
    }
}
