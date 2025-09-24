<?php

namespace App\Filament\Components;

use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists;
use Filament\Tables\Columns\{TextColumn};
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Database\Eloquent\Builder;
use Creasi\Nusa\Models\{Province, Regency, District};
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
                                        $net = 25000;
                                    } elseif ($price > 100000 && $price <= 150000) {
                                        $net = 50000;
                                    } elseif ($price > 150000 && $price <= 200000) {
                                        $net = 75000;
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
                                    return [
                                        'implementer_' . $get('implementer_count') => 'Jumlah Pelaksanaan',
                                        'account_' . $get('account_count_created') => 'Jumlah Akun',
                                    ];
                                })
                                ->live(1000)
                                ->reactive()
                                ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                    preg_match('/\d+/', $state, $matches);
                                    $count = (float) ($matches[0] ?? 0);

                                    $set('total', (float) $get('price') * $count);
                                }),

                        ]),

                    Fieldset::make('Total')
                        ->schema([
                            TextInput::make('total')
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
                                ->native(false)
                                ->displayFormat('l, jS F Y')                             ,
                            DatePicker::make('payment_date')
                                ->label('Pembayaran')
                                ->native(false)
                                ->displayFormat('d/m/Y')                             ,
                            DatePicker::make('spk_sent')
                                ->label('SPK di Kirim')
                                ->native(false)
                                ->displayFormat('d/m/Y')                             ,
                            Select::make('payment')
                                ->label('Pembayaran Via')
                                ->options([
                                    'SIPLAH' => 'SIPLAH',
                                    'SI/TF' => 'SI / TF',
                                    'CASH' => 'CASH'
                                ])
                        ]),
                ])->columns(2),

                Section::make('Status')
                    ->description('Merah = Belum dikerjakan • Kuning = Sales & Akademik')
                    ->schema([
                        Select::make('status_color')
                            ->label('Status')
                            ->native(false)
                            ->options([
                                'blue'   => 'Biru (Teknisi)',
                                'green'  => 'Hijau (Finance)',
                            ])
                            ->searchable()
                            ->placeholder('Pilih status...')
                            ->helperText('Biru: Teknisi • Hijau: Finance')
                            ->columnSpan(1),
                    ])->columns(2),

        ];
    }

    public static function columns(): array
    {
        return [
            TextColumn::make('no')
                ->rowIndex(),
            TextColumn::make('periode')
                ->label('Periode'),
            TextColumn::make('years')
                ->label('Tahun'),
            TextColumn::make('users.name')
                ->label('User')
                ->searchable(),
            TextColumn::make('schools')
                ->label('Sekolah'),
            TextColumn::make('education_level')
                ->label('Jenjang'),
            TextColumn::make('status_color')
                ->label('Status')
                ->badge()
                ->formatStateUsing(fn ($state) => ucfirst($state)) // Kuning/Biru/Hijau
                ->color(fn (string $state): string => match ($state) {
                    'green'  => 'green',
                    'blue'   => 'blue',
                    'yellow' => 'yellow',
                    'red'  => 'red',
                })
                ->sortable()
                ->toggleable(),
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
                                Infolists\Components\TextEntry::make('schools')
                                    ->label('Sekolah'),
                                Infolists\Components\TextEntry::make('class')
                                    ->label('Kelas'),
                                Infolists\Components\TextEntry::make('education_level')
                                    ->label('Jenjang'),
                                Infolists\Components\TextEntry::make('description')
                                    ->label('Keterangan'),
                                Infolists\Components\TextEntry::make('education_level_type')
                                    ->label('Negeri / Swasta'),
                                Infolists\Components\TextEntry::make('student_count')
                                    ->label('Jumlah Siswa'),
                                Infolists\Components\TextEntry::make('provinces')
                                    ->label('Provinsi'),
                                Infolists\Components\TextEntry::make('regencies')
                                    ->label('Kota / Kabupaten'),
                                Infolists\Components\TextEntry::make('sudin')
                                    ->label('Wilayah')
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
                                TextEntry::make('total')
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
            Tables\Filters\SelectFilter::make('users_id')
                ->label('User')
                ->options(function () {
                    return \App\Models\User::all()->pluck('name', 'id')->toArray();
                })
                ->preload()
                ->indicator('user'),
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

    public static function getRoles(): array
    {
        return [
            'finance', 'admin'
        ];
    }
}
