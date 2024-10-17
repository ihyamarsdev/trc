<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\CurriculumDeputies;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CurriculumDeputiesResource\Pages;
use App\Filament\Resources\CurriculumDeputiesResource\RelationManagers;

class CurriculumDeputiesResource extends Resource
{
    protected static ?string $model = CurriculumDeputies::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Management Sekolah';
    protected static ?string $title = 'Wakakurikulum';
    protected static ?string $navigationLabel = 'Wakakurikulum';
    protected static ?string $modelLabel = 'Wakakurikulum';
    protected static ?string $slug = 'curriculum-deputies';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Wakakurikulum')
                    ->aside()
                    ->description('Data Detail Wakakurikulum')
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
            'index' => Pages\ListCurriculumDeputies::route('/'),
            'create' => Pages\CreateCurriculumDeputies::route('/create'),
            'edit' => Pages\EditCurriculumDeputies::route('/{record}/edit'),
        ];
    }
}
