<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Pages\ListDevisions;
use App\Models\Devisions;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\{Section};
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\DevisionsResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\DevisionsResource\RelationManagers;

class DevisionsResource extends Resource
{
    protected static ?string $model = Devisions::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationGroup = 'Management User';

    protected static ?string $title = 'Devisi';

    protected static ?string $navigationLabel = 'Devisi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Devisi')
                    ->aside()
                    ->description('Masukkan Nama Devisi yang ada di TRC Management')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Name')
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
                    ->label('Devisi'),
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
            'index' => Pages\ListDevisions::route('/'),
            'create' => Pages\CreateDevisions::route('/create'),
            'edit' => Pages\EditDevisions::route('/{record}/edit'),
        ];
    }
}
