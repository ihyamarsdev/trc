<?php

namespace App\Filament\User\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\SchoolYear;
use Filament\Tables\Table;
use App\Models\AnbkFinance;
use Filament\Actions\Action;
use App\Models\RegistrationData;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Filament\Components\Finance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\User\Resources\AnbkFinanceResource\Pages;
use Pelmered\FilamentMoneyField\Infolists\Components\MoneyEntry;
use App\Filament\User\Resources\AnbkFinanceResource\RelationManagers;
use Filament\Infolists\Components\{TextEntry, Section, Group, Grid, Fieldset, IconEntry};

class AnbkFinanceResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Program Finance';
    protected static ?string $title = 'ANBK';
    protected static ?string $navigationLabel = 'ANBK';
    protected static ?string $modelLabel = 'ANBK';
    protected static ?string $slug = 'anbk-finance';
    protected static bool $shouldRegisterNavigation = true;


    public static function canViewAny(): bool
    {
        return Auth::user()->hasRole('finance');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(
                Finance::formSchema(options: [
                    'nameRegister' => 'ANBK',
                    'DescriptionRegister' => 'ASESMEN NASIONAL BERBASIS KOMPUTER'
                ])
            );
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'anbk')->orderBy('date_register', 'asc'))
            ->columns(
                Finance::columns()
            )
            ->filters([
                Tables\Filters\SelectFilter::make('periode')
                    ->label('Periode')
                    ->options([
                        'Januari - Juni' => 'Januari - Juni',
                        'Juli - Desember' => 'Juli - Desember',
                    ])
                    ->preload()
                    ->searchable(),
                Tables\Filters\SelectFilter::make('school_years_id')
                    ->label('Tahun Ajaran')
                    ->options(SchoolYear::all()->pluck('name', 'id'))
                    ->preload()
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Action::make('Print Invoice')
                        ->form([
                            Forms\Components\TextInput::make('subject')->required(),
                        ])
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(fn (RegistrationData $record) => route('finance.invoice.download', $record))
                        ->openUrlInNewTab()

                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Datacenter')
                    ->description('Detail data dari datacenter')
                    ->schema([
                        Fieldset::make('Periode')
                            ->schema([
                                TextEntry::make('periode')
                                        ->label('Periode'),
                                TextEntry::make('school_years.name')
                                        ->label('Tahun Ajaran'),
                            ]),

                        Fieldset::make('Salesforce')
                            ->schema([
                                TextEntry::make('users.name')
                                    ->label('User'),
                            ]),

                        Fieldset::make('Sekolah')
                            ->schema([
                                TextEntry::make('schools')
                                    ->label('Sekolah'),
                                TextEntry::make('education_level')
                                    ->label('Jenjang'),
                                TextEntry::make('education_level_type')
                                    ->label('Negeri / Swasta'),
                                TextEntry::make('student_count')
                                    ->label('Jumlah Siswa'),
                                TextEntry::make('provinces')
                                    ->label('Provinsi'),
                                TextEntry::make('regencies')
                                    ->label('Kota / Kabupaten'),
                            ]),


                        Fieldset::make('Bagan')
                            ->schema([
                                TextEntry::make('principal')
                                    ->label('Kepala Sekolah'),
                                TextEntry::make('phone_principal')
                                    ->label('No Hp Kepala Sekolah'),
                                TextEntry::make('curriculum_deputies.name')
                                    ->label('Wakakurikulum'),
                                TextEntry::make('curriculum_deputies.phone')
                                    ->label('No Hp Wakakurikulum'),
                                TextEntry::make('counselor_coordinators.name')
                                    ->label('Koordinator BK'),
                                TextEntry::make('counselor_coordinators.phone')
                                    ->label('No Hp Koordinator BK'),
                                TextEntry::make('proctors.name')
                                    ->label('Proktor'),
                                TextEntry::make('proctors.phone')
                                    ->label('No Hp Proktor'),
                            ]),

                        Fieldset::make('')
                            ->schema([
                                TextEntry::make('date_register')
                                    ->label('Tanggal Pendaftaran')
                                    ->dateTime('l, jS F Y'),
                                TextEntry::make('implementation_estimate')
                                    ->label('Estimasi Pelaksanaan')
                                    ->dateTime('l, jS F Y'),
                            ]),
                        ]),

                Section::make('Academic')
                    ->description('Detail Data Academik')
                    ->schema([

                        Fieldset::make('')
                            ->schema([
                                TextEntry::make('group')
                                    ->label('Grup')
                                    ->dateTime('l, jS F Y'),
                                TextEntry::make('bimtek')
                                    ->label('Bimtek')
                                    ->dateTime('l, jS F Y'),
                            ]),

                        Fieldset::make('')
                            ->schema([
                                TextEntry::make('account_count_created')
                                    ->label('Akun Dibuat'),
                                TextEntry::make('implementer_count')
                                    ->label('Pelaksanaan'),
                                TextEntry::make('difference')
                                    ->label('Selisih'),
                            ]),

                        Fieldset::make('')
                            ->schema([
                                IconEntry::make('schools_download')
                                    ->label('Download Sekolah')
                                    ->icon(fn (string $state): string => match ($state) {
                                        'ya' => 'heroicon-s-check-circle',
                                        'tidak' => 'heroicon-s-x-circle',
                                    })
                                    ->color(fn (string $state): string => match ($state) {
                                        'ya' => 'success',
                                        'tidak' => 'warning',
                                    }),
                                IconEntry::make('pm')
                                    ->label('PM')
                                    ->icon(fn (string $state): string => match ($state) {
                                        'ya' => 'heroicon-s-check-circle',
                                        'Tidak' => 'heroicon-s-x-circle',
                                    })
                                    ->color(fn (string $state): string => match ($state) {
                                        'ya' => 'success',
                                        'tidak' => 'warning',
                                    }),
                            ]),

                    ])->columns(2),

                Section::make('Finance')
                    ->description('Detail Data Finance')
                    ->schema([

                        Fieldset::make('')
                            ->schema([
                                MoneyEntry::make('price')
                                    ->label('Harga'),
                                MoneyEntry::make('total')
                                    ->label('Total Harga'),
                                MoneyEntry::make('net')
                                    ->label('Harga'),
                                MoneyEntry::make('total_net')
                                    ->label('Total Net'),
                            ]),

                        Fieldset::make('')
                            ->schema([
                                TextEntry::make('invoice_date')
                                    ->label('Invoice')
                                    ->dateTime('l, jS F Y'),
                                TextEntry::make('payment_date')
                                    ->label('Pembayaran')
                                    ->dateTime('l, jS F Y'),
                                TextEntry::make('spk_sent')
                                    ->label('SPK di Kirim')
                                    ->dateTime('l, jS F Y'),
                                TextEntry::make('payment')
                                    ->label('Pembayaran Via')
                            ]),
                    ])->columns(2),
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
            'index' => Pages\ListAnbkFinances::route('/'),
            'create' => Pages\CreateAnbkFinance::route('/create'),
            'edit' => Pages\EditAnbkFinance::route('/{record}/edit'),
            'view' => Pages\ViewAnbkFinance::route('/{record}'),
        ];
    }
}
