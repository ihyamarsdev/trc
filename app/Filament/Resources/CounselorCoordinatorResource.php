<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\CounselorCoordinator;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CounselorCoordinatorResource\Pages;
use App\Filament\Resources\CounselorCoordinatorResource\RelationManagers;

class CounselorCoordinatorResource extends Resource
{
    protected static ?string $model = CounselorCoordinator::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Management Sekolah';
    protected static ?string $title = 'Koordinator BK';
    protected static ?string $navigationLabel = 'Koordinator BK';
    protected static ?string $modelLabel = 'Koordinator BK';
    protected static ?string $slug = 'counselor-coordinator';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Koodinator BK')
                    ->aside()
                    ->description('Data Detail Koordinator BK')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(30),
                        Forms\Components\TextInput::make('phone')
                            ->label('No Handphone')
                            ->required()
                            ->maxLength(30),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->rowIndex(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
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
            ]);
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
            'index' => Pages\ListCounselorCoordinators::route('/'),
            'create' => Pages\CreateCounselorCoordinator::route('/create'),
            'edit' => Pages\EditCounselorCoordinator::route('/{record}/edit'),
        ];
    }
}
