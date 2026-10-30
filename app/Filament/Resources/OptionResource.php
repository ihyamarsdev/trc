<?php

namespace App\Filament\Resources;

use App\Filament\Enum\OptionGroup;
use App\Filament\Resources\OptionResource\Pages;
use App\Models\Option;
use Filament\Actions\DeleteAction as PageDeleteAction;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

class OptionResource extends Resource
{
    protected static ?string $model = Option::class;

    protected static ?string $navigationLabel = 'Data Master';

    protected static ?string $modelLabel = 'pilihan';

    protected static ?string $pluralModelLabel = 'Data Master';

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-adjustments-horizontal';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Settings';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Pilihan')
                    ->description('Pilihan yang muncul di form dan filter. Ubah di sini tanpa mengubah kode.')
                    ->schema([
                        Select::make('group')
                            ->label('Daftar')
                            ->options(collect(OptionGroup::cases())->mapWithKeys(fn (OptionGroup $group): array => [$group->value => $group->label()])->all())
                            ->required()
                            ->live()
                            ->native(false)
                            ->disabled(fn (string $operation): bool => $operation === 'edit'),
                        TextInput::make('label')
                            ->label('Nama yang ditampilkan')
                            ->required()
                            ->maxLength(150)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state, string $operation): void {
                                if ($operation === 'create' && blank($get('value'))) {
                                    $set('value', self::suggestValue(OptionGroup::tryFrom((string) $get('group')), (string) $state));
                                }
                            }),
                        TextInput::make('value')
                            ->label('Nilai yang disimpan')
                            ->helperText(fn (Get $get): string => OptionGroup::tryFrom((string) $get('group'))?->isNumeric()
                                ? 'Angka persen, contoh 11 atau 0.5. Tidak bisa diubah setelah dibuat.'
                                : 'Yang tersimpan di data sekolah. Tidak bisa diubah setelah dibuat.')
                            ->required()
                            ->maxLength(fn (Get $get): ?int => OptionGroup::tryFrom((string) $get('group'))?->isNumeric() ? null : 100)
                            ->numeric(fn (Get $get): bool => OptionGroup::tryFrom((string) $get('group'))?->isNumeric() ?? false)
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule, Get $get): Unique => $rule->where('group', $get('group')),
                            )
                            ->dehydrateStateUsing(fn (?string $state, Get $get): string => $get('group') === OptionGroup::Program->value
                                ? mb_strtolower(trim((string) $state))
                                : trim((string) $state))
                            ->disabled(fn (string $operation): bool => $operation === 'edit'),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->helperText('Tampil di judul halaman program.')
                            ->rows(2)
                            ->visible(fn (Get $get): bool => OptionGroup::tryFrom((string) $get('group'))?->hasDescription() ?? false)
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->helperText('Nonaktif: tidak muncul sebagai pilihan baru, data lama tetap aman.')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->label('Nama')
                    ->searchable()
                    ->weight('semibold'),
                TextColumn::make('value')
                    ->label('Nilai')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(60)
                    ->placeholder('-')
                    ->visibleFrom('md'),
                TextColumn::make('usage')
                    ->label('Dipakai')
                    ->state(fn (Option $record): string => $record->usageCount().' data')
                    ->color('gray'),
                ToggleColumn::make('is_active')
                    ->label('Aktif'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->paginated(false)
            ->actions([
                Tables\Actions\EditAction::make(),
                TableDeleteAction::make()
                    ->before(fn (TableDeleteAction $action, Option $record) => self::cancelIfInUse($action, $record)),
            ]);
    }

    /**
     * Records keep the stored value, so a choice that is still in use can only be retired, not deleted.
     */
    public static function cancelIfInUse(TableDeleteAction|PageDeleteAction $action, Option $record): void
    {
        $used = $record->usageCount();

        if ($used === 0) {
            return;
        }

        Notification::make()
            ->danger()
            ->title('Tidak bisa dihapus')
            ->body("Masih dipakai oleh {$used} data. Nonaktifkan saja supaya tidak muncul sebagai pilihan baru.")
            ->send();

        $action->cancel();
    }

    /**
     * Starting value for a new choice: the program code, the plain number of a tax rate, otherwise the name itself.
     */
    private static function suggestValue(?OptionGroup $group, string $label): string
    {
        $label = trim($label);

        return match (true) {
            $group === OptionGroup::Program => mb_strtolower($label),
            $group?->isNumeric() => str_replace(',', '.', (string) preg_replace('/[^0-9,.]/', '', $label)),
            default => $label,
        };
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOptions::route('/'),
            'create' => Pages\CreateOption::route('/create'),
            'edit' => Pages\EditOption::route('/{record}/edit'),
        ];
    }
}
