<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Devisions;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Hash;
use App\Filament\Imports\UserImporter;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
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
                            ->unique(ignoreRecord: true)
                            ->email()
                            ->required()
                            ->maxLength(255),
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
                ImageColumn::make('avatar_url')
                    ->label('Avatar')
                    ->circular()
                    ->size(40),
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
                    ->color(fn (string $state): string => match ($state) {
                        'finance'  => 'green',
                        'akademik'   => 'blue',
                        'teknisi'   => 'blue',
                        'sales' => 'yellow',
                        'admin'  => 'indigo',
                    })
                    ->label('Role'),
            ])
            ->filters([
                    //
            ])
            ->actions([
                    Tables\Actions\ActionGroup::make([
                        Tables\Actions\EditAction::make(),
                        Tables\Actions\Action::make('change-password')
                        ->label('Ubah Password')
                        ->icon('heroicon-o-key')
                        ->action(function ($record, array $data) {
                            // Logika untuk mengganti password
                            $record->password = Hash::make($data['new_password']);
                            $record->save();

                            Notification::make()
                                ->title('Password berhasil diubah')
                                ->success()
                                ->send();
                        })
                        ->form([
                            Forms\Components\TextInput::make('new_password')
                                ->label('Password Baru')
                                ->password()
                                ->required(),
                            Forms\Components\TextInput::make('confirm_password')
                                ->label('Konfirmasi Password')
                                ->password()
                                ->required()
                                ->same('new_password')
                                ->dehydrated(fn ($state) => ! is_null($state)),
                        ])
                        ->requiresConfirmation(),
                    ]),
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
