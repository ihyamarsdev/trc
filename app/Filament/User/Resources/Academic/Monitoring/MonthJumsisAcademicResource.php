<?php

namespace App\Filament\User\Resources\Academic\Monitoring;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Illuminate\Database;
use App\Models\SchoolYear;
use Filament\Tables\Table;
use App\Models\RegistrationData;
use Filament\Resources\Resource;
use App\Models\MonthJumsisAcademic;
use Filament\Tables\Grouping\Group;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\User\Resources\Academic\Monitoring\MonthJumsisAcademicResource\Pages;
use App\Filament\User\Resources\MonthJumsisAcademicResource\RelationManagers;

class MonthJumsisAcademicResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Data Biru';
    protected static ?string $title = 'Jumsis Bulanan';
    protected static ?string $navigationLabel = 'Jumsis Bulanan';
    protected static ?string $modelLabel = 'Jumlah Siswa Bulanan';
    protected static ?string $slug = 'month-jumsis-academic';
    protected static ?int $navigationSort = 14;
    protected static bool $shouldRegisterNavigation = true;

    public static function canViewAny(): bool
    {
        return Auth::user()->hasRole(['academic','finance', 'admin']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('monthYear')
                    ->label('Bulan'),
                Tables\Columns\TextColumn::make('implementer_count')
                    ->label('Jumlah')
                    ->summarize(
                        Summarizer::make()
                            ->label('')
                            ->using(fn (Database\Query\Builder $query) => $query->sum('implementer_count'))
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
                    ->titlePrefixedWithLabel(false)
                    ->orderQueryUsing(
                        fn (Builder $query) => $query->orderBy('date_register', 'asc')
                    ),
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
            'index' => Pages\ListMonthJumsisAcademics::route('/'),
        ];
    }
}
