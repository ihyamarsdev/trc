<?php

namespace App\Filament\User\Resources\Academic\Monitoring;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Illuminate\Database;
use Filament\Tables\Table;
use App\Models\RegistrationData;
use Filament\Resources\Resource;
use App\Models\DifferenceAcademic;
use Filament\Tables\Grouping\Group;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\User\Resources\Academic\Monitoring\DifferenceAcademicResource\Pages;
use App\Filament\User\Resources\DifferenceAcademicResource\RelationManagers;

class DifferenceAcademicResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Data Biru';
    protected static ?string $title = 'Perbandingan Data Kuning dan Biru';
    protected static ?string $navigationLabel = 'Perbandingan';
    protected static ?string $modelLabel = 'Perbandingan Data Kuning dan Biru';
    protected static ?string $slug = 'difference-academic';
    protected static ?int $navigationSort = 15;
    protected static bool $shouldRegisterNavigation = true;

    public static function canViewAny(): bool
    {
        return Auth::user()->hasRole(['academic','finance', 'admin']);
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
                Tables\Columns\TextColumn::make('student_count')
                    ->label('Data Kuning')
                    ->summarize(
                        Summarizer::make()
                            ->label('')
                            ->using(fn (Database\Query\Builder $query) => $query->sum('student_count'))
                    ),
                Tables\Columns\TextColumn::make('implementer_count')
                    ->label('Data Biru')
                    ->summarize(
                        Summarizer::make()
                            ->label('')
                            ->using(fn (Database\Query\Builder $query) => $query->sum('implementer_count'))
                    ),
                Tables\Columns\TextColumn::make('difference_data')
                    ->label('Selisih')
                    ->formatStateUsing(fn ($record) => $record->student_count - $record->implementer_count)
                    ->summarize(
                        Summarizer::make()
                            ->label('')
                            ->using(fn (Database\Query\Builder $query) => $query->sum('student_count') - $query->sum('implementer_count'))
                    ),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListDifferenceAcademics::route('/'),
            'create' => Pages\CreateDifferenceAcademic::route('/create'),
            'edit' => Pages\EditDifferenceAcademic::route('/{record}/edit'),
        ];
    }
}
