<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Proctors;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ProctorsResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProctorsResource\RelationManagers;

class ProctorsResource extends Resource
{
    protected static ?string $model = Proctors::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Management Sekolah';
    protected static ?string $title = 'Proktor';
    protected static ?string $navigationLabel = 'Proktor';
    protected static ?string $modelLabel = 'Proktor';
    protected static ?string $slug = 'proctor';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Proktor')
                    ->aside()
                    ->description('Data Detail Proktor')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(30),
                        TextInput::make('phone')
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
            'index' => Pages\ListProctors::route('/'),
            'create' => Pages\CreateProctors::route('/create'),
            'edit' => Pages\EditProctors::route('/{record}/edit'),
        ];
    }
}
