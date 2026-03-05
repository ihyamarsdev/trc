<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|\UnitEnum|null $navigationGroup = 'Management User';

    protected static ?string $title = 'User';

    protected static ?string $navigationLabel = 'User';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Manajemen User')
                    ->description('Data akun dan hak akses user')
                    ->schema([
                        Section::make('Data Akun')
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->extraInputAttributes(['style' => 'text-transform: capitalize'])
                                    ->dehydrateStateUsing(fn ($state) => is_string($state)
                                        ? Str::of($state)->squish()->lower()->title()
                                        : $state)
                                    ->maxLength(50),
                                TextInput::make('email')
                                    ->unique(ignoreRecord: true)
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                        Section::make('Hak Akses')
                            ->schema([
                                Select::make('roles')
                                    ->relationship('roles', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable(),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columns(['lg' => 12]),
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
                        'super_admin' => 'danger',
                        'admin' => 'warning',
                        'finance' => 'success',
                        'service' => 'info',
                        'sales' => 'yellow',
                        'akademik' => 'primary',
                        'teknisi' => 'gray',
                        'panel_user' => 'gray',
                        default => 'primary',
                    })
                    ->label('Role'),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->indicator('Role'),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    Action::make('change-password')
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
                        ->schema([
                            TextInput::make('new_password')
                                ->label('Password Baru')
                                ->password()
                                ->required(),
                            TextInput::make('confirm_password')
                                ->label('Konfirmasi Password')
                                ->password()
                                ->required()
                                ->same('new_password')
                                ->dehydrated(fn ($state) => ! is_null($state)),
                        ])
                        ->requiresConfirmation(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
