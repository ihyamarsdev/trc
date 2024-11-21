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
use Filament\Actions\Exports\Enums\ExportFormat;
use App\Filament\Exports\RegistrationDataExporter;
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
                Tables\Filters\TernaryFilter::make('payment_date')
                    ->label('Status Pembayaran Sekolah')
                    ->placeholder('Semua Sekolah')
                    ->trueLabel('Sudah Bayar')
                    ->falseLabel('Belum Bayar')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('payment_date'),
                        false: fn (Builder $query) => $query->whereNull('payment_date'),
                        blank: fn (Builder $query) => $query, 
                    )
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),

                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ExportBulkAction::make()
                        ->exporter(RegistrationDataExporter::class)
                        ->formats([
                            ExportFormat::Xlsx,
                        ]),
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
                                    ->label('Akun Dibuat')
                                    ->default('-'),
                                TextEntry::make('implementer_count')
                                    ->label('Pelaksanaan')
                                    ->default('-'),
                                TextEntry::make('difference')
                                    ->label('Selisih')
                                    ->default('-'),
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
                                    ->money('IDR')
                                    ->default('0'),
                                TextEntry::make('total')
                                    ->label('Total Harga')
                                    ->money('IDR')
                                    ->default('0'),
                            ]),

                        Fieldset::make('')
                            ->label('Exclusion policy')
                            ->schema([
                                TextEntry::make('student_count_1')
                                    ->label('Jumlah Siswa Net 1')
                                    ->default('-'),
                                TextEntry::make('student_count_2')
                                    ->label('Jumlah Siswa Net 2')
                                    ->default('-'),
                                TextEntry::make('net')
                                    ->label('Net 1')
                                    ->money('IDR')
                                    ->default('0'),
                                TextEntry::make('net_2')
                                    ->label('Net 2')
                                    ->money('IDR')
                                    ->default('0'),
                                TextEntry::make('subtotal_1')
                                    ->label('Sub Total 1')
                                    ->money('IDR')
                                    ->default('0'),
                                TextEntry::make('subtotal_2')
                                    ->label('Sub Total 2')
                                    ->money('IDR')
                                    ->default('0'),
                            ]),

                        Fieldset::make('')
                            ->schema([
                                TextEntry::make('total_net')
                                    ->label('Total Net')
                                    ->money('IDR')
                                    ->default('0'),
                                TextEntry::make('difference_total')
                                    ->label('Selisih Total')
                                    ->money('IDR')
                                    ->default('0'),
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
                                    ->default('-')
                            ]),
                    ])->columns(2),

                Section::make('Kwitansi')
                    ->description('Lakukan Edit untuk merubah Kwitansi')
                    ->schema([

                        Fieldset::make('')
                            ->schema([
                                TextEntry::make('schools')
                                    ->label('Telah Terima Dari')
                                    ->default('-'),
                                TextEntry::make('total')
                                    ->label('Uang Sejumlah')
                                    ->money('IDR')
                                    ->default('0'),
                                TextEntry::make('detail_kwitansi')
                                    ->label('Guna Pembayaran')
                                    ->default('-'),
                            ])->columns(1),
                    ]),

                Section::make('Invoice')
                    ->description('Lakukan Edit untuk merubah Invoice')
                    ->schema([

                        Fieldset::make('')
                            ->schema([
                                TextEntry::make('schools')
                                    ->label('Bill To')
                                    ->default('-'),
                                TextEntry::make('number_invoice')
                                    ->label('Nomor Invoice')
                                    ->default('-'),
                                TextEntry::make('detail_invoice')
                                    ->label('Deskripsi')
                                    ->default('-'),
                            ])->columns(1),

                        Fieldset::make('')
                            ->schema([
                                TextEntry::make('qty_invoice')
                                    ->label('Kuantitas')
                                    ->default('0'),
                                TextEntry::make('unit_price')
                                    ->label('Harga Per Unit')
                                    ->money('IDR')
                                    ->default('0'),
                                TextEntry::make('amount_invoice')
                                    ->label('Jumlah')
                                    ->money('IDR')
                                    ->default('-'),
                                TextEntry::make('subtotal_invoice')
                                    ->label('Sub Total')
                                    ->money('IDR')
                                    ->default('-'),
                            ])->columns(2),

                        Fieldset::make('')
                            ->schema([
                                TextEntry::make('tax_rate')
                                    ->label('Tax Rate')
                                    ->default('-'),
                                TextEntry::make('sales_tsx')
                                    ->label('Sales Tax')
                                    ->default('-')
                                    ->money('IDR'),
                                TextEntry::make('other')
                                    ->label('Other')
                                    ->default('-'),
                                TextEntry::make('total_invoice')
                                    ->label('Total')
                                    ->money('IDR')
                                    ->default('-'),
                            ])->columns(2),
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
