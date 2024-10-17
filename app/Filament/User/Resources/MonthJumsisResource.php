<?php

namespace App\Filament\User\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Illuminate\Database;
use Filament\Tables\Table;
use App\Models\MonthJumsis;
use App\Models\RegistrationData;
use Filament\Resources\Resource;
use Filament\Tables\Grouping\Group;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\User\Resources\MonthJumsisResource\Pages;
use App\Filament\User\Resources\MonthJumsisResource\RelationManagers;

class MonthJumsisResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Data Kuning';
    protected static ?string $title = 'Jumsis Bulanan';
    protected static ?string $navigationLabel = 'Jumsis Bulanan';
    protected static ?string $modelLabel = 'Jumlah Siswa Bulanan';
    protected static ?string $slug = 'month-jumsis-datacenter';
    protected static ?int $navigationSort = 14;
    protected static bool $shouldRegisterNavigation = true;

    public static function canViewAny(): bool
    {
        return Auth::user()->hasRole('datacenter');
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
                Tables\Columns\TextColumn::make('id')
                    ->label('Jumlah')
                    ->summarize(
                        Summarizer::make()
                            ->label('')
                            ->using(fn (Database\Query\Builder $query) => $query->count('id'))
                    ),
            ])
            ->filters([
                //
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
                        fn(Builder $query) => $query->orderBy('date_register', 'asc')
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
            'index' => Pages\ListMonthJumses::route('/'),
        ];
    }
}