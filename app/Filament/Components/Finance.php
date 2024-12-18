<?php

namespace App\Filament\Components;

use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists;
use App\Models\SchoolYear;
use Filament\Tables\Columns\{TextColumn};
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Database\Eloquent\Builder;
use Creasi\Nusa\Models\{Province, Regency};
use Filament\Forms\Components\{Select, TextInput, Section, DatePicker, Radio, Fieldset, Group};

class Finance
{
    public static function formSchema(array $options = []): array
    {
        return [
            Section::make($options['nameRegister'])
                ->description($options['DescriptionRegister'])
                ->schema([

                    Fieldset::make('')
                        ->schema([
                            TextInput::make('account_count_created')
                                ->label('Jumlah Akun Dibuat')
                                ->disabled(),
                            TextInput::make('implementer_count')
                                ->label('Jumlah Pelaksanaan')
                                ->disabled(),
                        ]),

                    Fieldset::make('Nominal')
                        ->schema([
                            TextInput::make('price')
                                ->label('Harga SPJ')
                                ->prefix('Rp')
                                ->live(1000)
                                ->reactive()
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $price = (float) $get('price');
                                    $net = 0;

                                    if ($price >= 0 && $price <= 100000) {
                                        $net = 210000;
                                    } elseif ($price > 100000 && $price <= 1100000) {
                                        $net = 100000;
                                    } elseif ($price > 1100000 && $price <= 200000) {
                                        $net = 710000;
                                    } elseif ($price > 200000) {
                                        $net = 100000;
                                    }

                                    $set('net', $net);
                                    $set('mitra_net', $net);
                                    $set('ss_net', $net);
                                    $set('dll_net', $net);
                                }),
                            TextInput::make('net_2')
                                ->label('Harga NET')
                                ->prefix('Rp')
                                ->live(1000)
                                ->reactive()
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $set('total_net', abs((float) $get('implementer_count') * (float) $get('net_2')));
                                }),
                        ]),

                    Fieldset::make('Opsi')
                        ->schema([
                            Radio::make('option_price')
                                ->label('Pilih Opsi')
                                ->options(function (Get $get): array {
                                    $accountCount = (float) $get('account_count_created');
                                    $implementerCount = (float) $get('implementer_count');

                                    return [
                                        $accountCount => 'Jumlah Akun',
                                        $implementerCount => 'Jumlah Pelaksanaan'
                                    ];
                                })
                                ->live(1000)
                                ->reactive()
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $set('total', (float) $get('price') * (float) $get('option_price'));
                                    $set('difference_total', abs((float) $get('total')));
                                }),
                            TextInput::make('total')
                                ->label('Total')
                                ->prefix('Rp')
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                ->readOnly(),
                        ]),

                    Fieldset::make('Total')
                        ->schema([
                            TextInput::make('difference_total')
                                ->label('Total Dana Sesuai SPJ')
                                ->prefix('Rp')
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                ->readOnly(),
                            TextInput::make('total_net')
                                ->label('Total Net')
                                ->prefix('Rp')
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                ->readOnly(),
                        ]),


                    Fieldset::make('')
                        ->label('TRC')
                        ->schema([
                            TextInput::make('student_count_1')
                                ->label('Selisih Siswa TRC')
                                ->numeric()
                                ->live(debounce: 1000)
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $set('subtotal_1', (float) $get('student_count_1') * (float) $get('net'));
                                }),
                            TextInput::make('net')
                                ->label('Satuan')
                                ->live(debounce: 1000)
                                ->prefix('Rp')
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                ->numeric()
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $set('subtotal_1', (float) $get('student_count_1') * (float) $get('net'));
                                }),
                            TextInput::make('subtotal_1')
                                ->label('Subtotal')
                                ->prefix('Rp')
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                ->numeric()
                                ->readOnly(),
                        ])->columns(3),

                    Fieldset::make('')
                        ->label('MITRA')
                        ->schema([
                            TextInput::make('mitra_difference')
                                ->label('Selisih Siswa Sekolah')
                                ->numeric()
                                ->live(debounce: 1000)
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $set('mitra_subtotal', (float) $get('mitra_difference') * (float) $get('mitra_net'));
                                }),
                            TextInput::make('mitra_net')
                                ->label('Satuan')
                                ->live(debounce: 1000)
                                ->prefix('Rp')
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                ->numeric()
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $set('mitra_subtotal', (float) $get('mitra_difference') * (float) $get('mitra_net'));
                                }),
                            TextInput::make('mitra_subtotal')
                                ->label('Subtotal')
                                ->prefix('Rp')
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                ->numeric()
                                ->readOnly(),

                            TextInput::make('implementer_count')
                                ->label('SS')
                                ->numeric()
                                ->live(debounce: 1000)
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $set('ss_subtotal', (float) $get('implementer_count') * (float) $get('ss_net'));
                                })
                                ->readOnly(),
                            TextInput::make('ss_net')
                                ->label('Satuan')
                                ->live(debounce: 1000)
                                ->prefix('Rp')
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                ->numeric()
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $set('ss_subtotal', (float) $get('implementer_count') * (float) $get('ss_net'));
                                }),
                            TextInput::make('ss_subtotal')
                                ->label('Subtotal')
                                ->prefix('Rp')
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                ->numeric()
                                ->readOnly(),

                            TextInput::make('dll_difference')
                                ->label('Lain-lain')
                                ->numeric()
                                ->live(debounce: 1000)
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $set('dll_subtotal', (float) $get('dll_difference') * (float) $get('dll_net'));
                                }),
                            TextInput::make('dll_net')
                                ->label('Satuan')
                                ->live(debounce: 1000)
                                ->prefix('Rp')
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                ->numeric()
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $set('subtotal_1', (float) $get('student_count_1') * (float) $get('net'));
                                }),
                            TextInput::make('dll_subtotal')
                                ->label('Subtotal')
                                ->prefix('Rp')
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                ->numeric()
                                ->readOnly(),
                        ])->columns(3),

                    Fieldset::make('')
                        ->schema([
                            DatePicker::make('invoice_date')
                                ->label('Invoice')
                                ->required(),
                            DatePicker::make('payment_date')
                                ->label('Pembayaran')
                                ->required(),
                            DatePicker::make('spk_sent')
                                ->label('SPK di Kirim')
                                ->required(),
                            Select::make('payment')
                                ->label('Pembayaran Via')
                                ->options([
                                    'SIPLAH' => 'SIPLAH',
                                    'SI/TF' => 'SI / TF',
                                    'CASH' => 'CASH'
                                ])
                        ]),
                ])->columns(2),

            Section::make('Kwitansi')
                ->schema([
                    Fieldset::make('')
                        ->schema([
                            TextInput::make('schools')
                                ->label('Sekolah')
                                ->readOnly(),
                            TextInput::make('detail_kwitansi')
                                ->label('Guna Pembayaran')
                                ->helperText('Contoh: 146 Paket Program TRY OUT Ujian Tertulis Berbasis Komputer (UTBK SNBT)'),
                        ])->columns(1),
                ]),

            Section::make('Invoice')
                ->schema([
                    Fieldset::make('')
                        ->schema([
                            TextInput::make('schools')
                                ->label('Sekolah')
                                ->readOnly(),
                            TextInput::make('number_invoice')
                                ->prefix('#')
                                ->label('Nomor Invoice')
                                ->live()
                                ->numeric(),
                            TextInput::make('detail_invoice')
                                ->label('Deskripsi')
                                ->helperText('Contoh: Try Out Asesmen Nasional (AKM)'),
                        ])->columns(1),

                    Fieldset::make('')
                        ->schema([
                            TextInput::make('qty_invoice')
                                ->label('Quantity')
                                ->live(1000)
                                ->numeric()
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $set('amount_invoice', abs((float) $get('qty_invoice') * (float) $get('unit_price')));
                                    $set('subtotal_invoice', abs((float) $get('amount_invoice')));
                                    $set('total_invoice', abs((float) $get('subtotal_invoice')));
                                }),
                            TextInput::make('unit_price')
                                ->label('Unit Price')
                                ->prefix('Rp')
                                ->live(1000)
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $set('amount_invoice', abs((float) $get('qty_invoice') * (float) $get('unit_price')));
                                    $set('subtotal_invoice', abs((float) $get('amount_invoice')));
                                    $set('total_invoice', abs((float) $get('subtotal_invoice')));
                                }),
                            TextInput::make('amount_invoice')
                                ->label('Amount')
                                ->prefix('Rp')
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                ->numeric()
                                ->readOnly(),
                            TextInput::make('subtotal_invoice')
                                ->label('Sub Total')
                                ->prefix('Rp')
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                ->numeric()
                                ->readOnly(),

                        ])->columns(2),

                    Fieldset::make('Pajak')
                        ->schema([
                            Radio::make('option_tax')
                                ->label('Pilih Opsi Pajak')
                                ->options([
                                    '0.02' => 'PPH 23',
                                    '0.11' => 'PPN',
                                    '0' => 'None',
                                ])
                                ->live(1000)
                                ->reactive()
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $set('total_invoice', (float) $get('subtotal_invoice') + ((float) $get('subtotal_invoice') * (float) $get('option_tax')));
                                    if ($get('option_tax') == '0.02') {
                                        $set('tax_rate', "2");
                                        $set('sales_tsx', "0");
                                    } elseif ($get('option_tax') == '0.11') {
                                        $set('tax_rate', "0");
                                        $set('sales_tsx', "11");
                                    } elseif ($get('option_tax') == '0') {
                                        $set('tax_rate', "0");
                                        $set('sales_tsx', "0");
                                    }
                                }),
                            Group::make([
                                TextInput::make('tax_rate')
                                    ->label('PPH 23')
                                    ->suffix('%')
                                    ->readOnly()
                                    ->live(1000),
                                TextInput::make('sales_tsx')
                                    ->label('PPN')
                                    ->suffix('%')
                                    ->readOnly()
                                    ->live(1000),
                            ]),

                        ])->columns(2),

                    Fieldset::make('')
                        ->schema([
                            TextInput::make('total_invoice')
                                ->label('Total')
                                ->prefix('Rp')
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                ->readOnly(),
                        ])->columns(2),
                ]),
        ];
    }

    public static function columns(): array
    {
        return [
            TextColumn::make('no')
                ->rowIndex(),
            TextColumn::make('periode')
                ->label('Periode'),
            TextColumn::make('school_years.name')
                ->label('Tahun Ajaran'),
            TextColumn::make('users.name')
                ->label('User')
                ->searchable(),
            TextColumn::make('date_register')
                ->label('Tanggal Pendaftaran')
                ->date()
                ->sortable(),
            TextColumn::make('provinces')
                ->label('Provinsi')
                ->formatStateUsing(function ($state) {
                    $province = Province::search($state)->first() ;
                    return $province ? $province->name : 'Unknown';
                }),
            TextColumn::make('regencies')
                ->label('Kota / Kabupaten')
                ->formatStateUsing(function ($state) {
                    $regency = Regency::search($state)->first();
                    return $regency ? $regency->name : 'Unknown';
                }),
            TextColumn::make('sudin')
                ->label('Daerah Tambahan'),
            TextColumn::make('schools')
                ->label('Sekolah'),
            TextColumn::make('education_level')
                ->label('Jenjang'),
            TextColumn::make('education_level_type')
                ->label('Negeri / Swasta'),
            TextColumn::make('principal')
                ->label('Kepala Sekolah'),
            TextColumn::make('phone_principal')
                ->label('No Hp Kepala Sekolah'),
            TextColumn::make('curriculum_deputies.name')
                ->label('Wakakurikulum'),
            TextColumn::make('curriculum_deputies.phone')
                ->label('No Hp Wakakurikulum'),
            TextColumn::make('counselor_coordinators.name')
                ->label('Koordinator BK'),
            TextColumn::make('counselor_coordinators.phone')
                ->label('No Hp Koordinator BK'),
            TextColumn::make('proctors.name')
                ->label('Proktor'),
            TextColumn::make('proctors.phone')
                ->label('No Hp Proktor'),
            TextColumn::make('student_count')
                ->label('Jumlah Siswa')
                ->numeric(),
            TextColumn::make('implementation_estimate')
                ->label('Estimasi Pelaksana')
                ->date(),
        ];
    }

    public static function infolist(): array
    {
        return [
            Infolists\Components\Section::make('Datacenter')
                    ->description('Detail data dari datacenter')
                    ->schema([
                        Infolists\Components\Fieldset::make('Periode')
                            ->schema([
                                TextEntry::make('periode')
                                        ->label('Periode'),
                                TextEntry::make('school_years.name')
                                        ->label('Tahun Ajaran'),
                            ]),

                        Infolists\Components\Fieldset::make('Salesforce')
                            ->schema([
                                TextEntry::make('users.name')
                                    ->label('User'),
                            ]),

                        Infolists\Components\Fieldset::make('Sekolah')
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
                                TextEntry::make('sudin')
                                    ->label('Daerah Tambahan')
                                    ->default('-'),
                            ]),


                        Infolists\Components\Fieldset::make('Bagan')
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

                        Infolists\Components\Fieldset::make('')
                            ->schema([
                                TextEntry::make('date_register')
                                    ->label('Tanggal Pendaftaran')
                                    ->dateTime('l, jS F Y'),
                                TextEntry::make('implementation_estimate')
                                    ->label('Estimasi Pelaksanaan')
                                    ->dateTime('l, jS F Y'),
                            ]),
                        ]),

                Infolists\Components\Section::make('Academic')
                    ->description('Detail Data Academik')
                    ->schema([

                        Infolists\Components\Fieldset::make('')
                            ->schema([
                                TextEntry::make('group')
                                    ->label('Grup')
                                    ->dateTime('l, jS F Y'),
                                TextEntry::make('bimtek')
                                    ->label('Bimtek')
                                    ->dateTime('l, jS F Y'),
                            ]),

                        Infolists\Components\Fieldset::make('')
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

                        Infolists\Components\Fieldset::make('')
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

                        Infolists\Components\Fieldset::make('')
                            ->schema([
                                TextEntry::make('counselor_consultation_date')
                                    ->label('Konsul BK')
                                    ->dateTime('l, jS F Y')
                                    ->default(null),
                                TextEntry::make('student_consultation_date')
                                    ->label('Konsul Siswa')
                                    ->dateTime('l, jS F Y')
                                    ->default(null),
                            ]),

                    ])->columns(2),

                Infolists\Components\Section::make('Finance')
                    ->description('Detail Data Finance')
                    ->schema([

                        Infolists\Components\Fieldset::make('Opsi Jumlah Akun / Jumlah Pelaksanaan')
                        ->schema([
                            TextEntry::make('option_price')
                                ->label('')
                                ->default('-'),
                        ]),


                        Infolists\Components\Fieldset::make('Nominal')
                            ->schema([
                                TextEntry::make('price')
                                    ->label('Harga SPJ')
                                    ->money('IDR')
                                    ->default('0'),
                                TextEntry::make('net_2')
                                    ->label('Harga Net')
                                    ->money('IDR')
                                    ->default('0'),
                            ])->columns(3),

                        Infolists\Components\Fieldset::make('')
                            ->label('TRC')
                            ->schema([
                                TextEntry::make('student_count_1')
                                    ->label('Selisih Siswa TRC')
                                    ->default('-'),
                                TextEntry::make('net')
                                    ->label('Satuan')
                                    ->money('IDR')
                                    ->default('0'),
                                TextEntry::make('subtotal_1')
                                    ->label('Sub Total')
                                    ->money('IDR')
                                    ->default('0'),
                            ])->columns(3),

                        Infolists\Components\Fieldset::make('')
                            ->label('MITRA')
                            ->schema([
                                TextEntry::make('mitra_difference')
                                    ->label('Selisih Siswa Sekolah')
                                    ->default('-'),
                                TextEntry::make('mitra_net')
                                    ->label('Satuan')
                                    ->money('IDR')
                                    ->default('0'),
                                TextEntry::make('mitra_subtotal')
                                    ->label('Sub Total')
                                    ->money('IDR')
                                    ->default('0'),

                                TextEntry::make('implementer_count')
                                    ->label('SS')
                                    ->default('-'),
                                TextEntry::make('ss_net')
                                    ->label('Satuan')
                                    ->money('IDR')
                                    ->default('0'),
                                TextEntry::make('ss_subtotal')
                                    ->label('Sub Total')
                                    ->money('IDR')
                                    ->default('0'),

                                TextEntry::make('dll_difference')
                                    ->label('SS')
                                    ->default('-'),
                                TextEntry::make('dll_net')
                                    ->label('Satuan')
                                    ->money('IDR')
                                    ->default('0'),
                                TextEntry::make('dll_subtotal')
                                    ->label('Sub Total')
                                    ->money('IDR')
                                    ->default('0'),
                            ])->columns(3),

                        Infolists\Components\Fieldset::make('')
                            ->label('Total')
                            ->schema([
                                TextEntry::make('difference_total')
                                    ->label('Total Dana Sesuai SPJ')
                                    ->money('IDR')
                                    ->default('0'),
                                TextEntry::make('total_net')
                                    ->label('Total Net')
                                    ->money('IDR')
                                    ->default('0'),
                            ])->columns(3),

                        Infolists\Components\Fieldset::make('')
                            ->label('Tanggal')
                            ->schema([
                                TextEntry::make('invoice_date')
                                    ->label('Invoice')
                                    ->dateTime('l, jS F Y')
                                    ->default(null),
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
                    ])->columns(3),

                Infolists\Components\Section::make('Kwitansi')
                    ->description('Lakukan Edit untuk merubah Kwitansi')
                    ->schema([

                        Infolists\Components\Fieldset::make('')
                            ->label('Detail Kwitansi')
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

                Infolists\Components\Section::make('Invoice')
                    ->description('Lakukan Edit untuk merubah Invoice')
                    ->schema([

                        Infolists\Components\Fieldset::make('')
                            ->label('Detail Invoice')
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

                        Infolists\Components\Fieldset::make('')
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

                        Infolists\Components\Fieldset::make('')
                            ->schema([
                                TextEntry::make('tax_rate')
                                    ->label('PPH 23')
                                    ->formatStateUsing(fn (string $state): string => __("{$state}%"))
                                    ->default('0'),
                                TextEntry::make('sales_tsx')
                                    ->label('PPN')
                                    ->formatStateUsing(fn (string $state): string => __("{$state}%"))
                                    ->default('0'),
                                TextEntry::make('total_invoice')
                                    ->label('Total')
                                    ->money('IDR')
                                    ->default('-'),
                            ])->columns(2),
                    ]),
        ];
    }

    public static function filters(): array
    {
        return [
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
        ];
    }
}
