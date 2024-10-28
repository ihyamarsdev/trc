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
                    Action::make('spk')
                        ->label('Print SPK')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->url(fn (RegistrationData $record) => route('rasyidu.anbk.download', $record))
                        ->openUrlInNewTab(),

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
                                        'tidak' => 'danger',
                                    }),
                                IconEntry::make('pm')
                                    ->label('PM')
                                    ->icon(fn (string $state): string => match ($state) {
                                        'ya' => 'heroicon-s-check-circle',
                                        'Tidak' => 'heroicon-s-x-circle',
                                    })
                                    ->color(fn (string $state): string => match ($state) {
                                        'ya' => 'success',
                                        'tidak' => 'danger',
                                    }),
                            ]),

                    ])->columns(2),

                Section::make('Finance')
                    ->description('Detail Data Finance')
                    ->schema([

                        Fieldset::make('')
                            ->schema([
                                TextEntry::make('price')
                                    ->label('Harga')
                                    ->money('IDR'),
                                TextEntry::make('total')
                                    ->label('Total Harga')
                                    ->money('IDR'),
                            ]),

                        Fieldset::make('')
                            ->label('Exclusion policy')
                            ->schema([
                                TextEntry::make('student_count_1')
                                    ->label('Jumlah Siswa Net 1'),
                                TextEntry::make('student_count_2')
                                    ->label('Jumlah Siswa Net 2'),
                                TextEntry::make('net')
                                    ->label('Net 1')
                                    ->money('IDR'),
                                TextEntry::make('net_2')
                                    ->label('Net 2')
                                    ->money('IDR'),
                                TextEntry::make('subtotal_1')
                                    ->label('Sub Total 1')
                                    ->money('IDR'),
                                TextEntry::make('subtotal_2')
                                    ->label('Sub Total 2')
                                    ->money('IDR'),
                            ]),

                        Fieldset::make('')
                            ->schema([
                                TextEntry::make('total_net')
                                    ->label('Total Net')
                                    ->money('IDR'),
                                TextEntry::make('difference_total')
                                    ->label('Selisih Total')
                                    ->money('IDR'),
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

                Section::make('Kwitansi')
                    ->description('Lakukan Edit untuk merubah Kwitansi')
                    ->schema([

                        Fieldset::make('')
                            ->schema([
                                TextEntry::make('schools')
                                    ->label('Telah Terima Dari')
                                    ->money('IDR'),
                                TextEntry::make('total')
                                    ->label('Uang Sejumlah')
                                    ->money('IDR'),
                                TextEntry::make('detail_kwitansi')
                                    ->label('Guna Pembayaran'),
                            ])->columns(1),
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
            'index' => Pages\ListAnbkFinances::route('/'),
            'create' => Pages\CreateAnbkFinance::route('/create'),
            'edit' => Pages\EditAnbkFinance::route('/{record}/edit'),
            'view' => Pages\ViewAnbkFinance::route('/{record}'),
        ];
    }
}
