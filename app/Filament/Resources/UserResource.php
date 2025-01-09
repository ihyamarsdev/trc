<?php

namespace App\Filament\Resources;

use App\Filament\Imports\UserImporter;
use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Devisions;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserResource\RelationManagers;
use Filament\Forms\Components\{TextInput, DateTimePicker, Select};

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Management User';

    protected static ?string $title = 'User';

    protected static ?string $navigationLabel = 'User';



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('User')
                    ->description('Membuat User')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->unique()
                            ->hiddenOn('edit')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->required()
                            ->maxLength(255)
                            ->hiddenOn('edit'),
                        // Select::make('devisions_id')
                        //     ->label('Devisi')
                        //     ->options(Devisions::all()->pluck('name', 'id'))
                        //     ->searchable(),
                        Select::make('roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->label('Name'),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->label('Tanggal Verifikasi')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('roles.name')
                    ->badge()
                    // ->color(fn (string $state): string => match ($state) {
                    //     'salesforce' => 'success',
                    //     'reviewing' => 'warning',
                    //     'published' => 'success',
                    //     'rejected' => 'danger',
                    // })
                    ->label('Role'),
                // TextColumn::make('devisions.name')
                //     ->badge()
                //     ->label('Devisi'),
                TextColumn::make('created_at')
                    ->label('Tanggal Di Buat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Tanggal Di Perbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
