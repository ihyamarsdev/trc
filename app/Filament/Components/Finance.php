<?php

namespace App\Filament\Components;

use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\{TextColumn};
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Creasi\Nusa\Models\{Province, Regency, District};
use Filament\Forms\Components\{Select, TextInput, Section, DatePicker, Radio, Fieldset, Group};

class Finance
{
    protected static function meta(Get $get): array
    {
        $type = $get('type') ?? 'apps';

        return match ($type) {
            'anbk' => [
                'nameRegister'        => 'ANBK',
                'DescriptionRegister' => 'ASESMEN NASIONAL BERBASIS KOMPUTER',
            ],
            'apps' => [
                'nameRegister'        => 'APPS',
                'DescriptionRegister' => 'ASESMEN PSIKOTES POTENSI SISWA',
            ],
            'snbt' => [
                'nameRegister'        => 'SNBT',
                'DescriptionRegister' => 'SELEKSI NASIONAL BERDASARKAN TES',
            ],
            'tka' => [
                'nameRegister'        => 'TKA',
                'DescriptionRegister' => 'TEST KEMAMPUAN AKADEMIK',
            ],
            default => [
                'nameRegister'        => 'APPS',
                'DescriptionRegister' => 'ASESMEN PSIKOTES POTENSI SISWA',
            ],
        };
    }

    protected static function metaInfo(Model $record): array
    {
        $type = $record->type ?? 'apps';

        return match ($type) {
            'anbk' => [
                'nameRegister'        => 'ANBK',
                'DescriptionRegister' => 'ASESMEN NASIONAL BERBASIS KOMPUTER',
            ],
            'apps' => [
                'nameRegister'        => 'APPS',
                'DescriptionRegister' => 'ASESMEN PSIKOTES POTENSI SISWA',
            ],
            'snbt' => [
                'nameRegister'        => 'SNBT',
                'DescriptionRegister' => 'SELEKSI NASIONAL BERDASARKAN TES',
            ],
            'tka' => [
                'nameRegister'        => 'TKA',
                'DescriptionRegister' => 'TEST KEMAMPUAN AKADEMIK',
            ],
            default => [
                'nameRegister'        => 'APPS',
                'DescriptionRegister' => 'ASESMEN PSIKOTES POTENSI SISWA',
            ],
        };
    }


    public static function formSchema(): array
    {
        return [
            Section::make()
                ->description()
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

                            TextInput::make('ss_difference')
                                ->label('SS')
                                ->numeric()
                                ->live(debounce: 1000)
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $set('ss_subtotal', (float) $get('ss_difference') * (float) $get('ss_net'));
                                })
                                ->readOnly(),
                            TextInput::make('ss_net')
                                ->label('Satuan')
                                ->live(debounce: 1000)
                                ->prefix('Rp')
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                ->numeric()
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $set('ss_subtotal', (float) $get('ss_difference') * (float) $get('ss_net'));
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
                                ->displayFormat('l, jS F Y'),
                            DatePicker::make('payment_date')
                                ->label('Jadwal Pembayaran')
                                ->native(false)
                                ->displayFormat('l, jS F Y'),
                            DatePicker::make('spk')
                                ->label('Jadwal SPK')
                                ->native(false)
                                ->displayFormat('l, jS F Y'),
                            Select::make('payment_name')
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
                        Select::make('status_id')
                            ->label('Status')
                            ->preload()
                                ->relationship(
                                    name: 'status',
                                    titleAttribute: 'name',
                                    modifyQueryUsing: fn (Builder $query) => $query
                                        ->orderBy('order')
                                )
                            ->searchable()
                            ->placeholder('Pilih status...')
                            ->columnSpan(1),
                    ])->columns(2),
        ];
    }

    public static function columns(): array
    {
        return [
            Split::make([
                TextColumn::make('type')
                    ->label('Program')
                    ->extraAttributes(['class' => 'uppercase']),
                TextColumn::make('schools')
                    ->label('Sekolah')
                    ->wrap(),
                TextColumn::make('periode')
                    ->label('Periode')
                    ->wrap(),
                TextColumn::make('years')
                    ->label('Tahun'),

                TextColumn::make('latestStatusLog.status.color')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'green'  => 'green',
                        'blue'   => 'blue',
                        'yellow' => 'yellow',
                        'red'  => 'red',
                    })
                    ->default('red')
                    ->toggleable(),
            ])->from('md')

            ];
    }

    public static function infolist(Model $record): array
    {
        return [
            Infolists\Components\Section::make(fn () => self::metaInfo($record)['nameRegister'])
                    ->description(fn () => self::metaInfo($record)['DescriptionRegister'])
                    ->schema([
                        Infolists\Components\Fieldset::make('Aktifitas Saat ini')
                            ->schema([
                                Infolists\Components\TextEntry::make('status.name')
                                    ->label('Status'),
                                Infolists\Components\IconEntry::make('latestStatusLog.status.color')
                                    ->label('Status Warna dan Icon')
                                    ->icon(fn (string $state): string => match ($state) {
                                        'red' => 'heroicon-s-x-circle',
                                        'yellow'  => 'heroicon-m-presentation-chart-line',
                                        'blue'  => 'heroicon-m-academic-cap',
                                        'green'  => 'heroicon-m-credit-card',
                                    })
                                    ->color(fn (string $state): string => match ($state) {
                                        'yellow' => 'yellow',
                                        'blue'   => 'blue',
                                        'green'  => 'green',
                                        'red'    => 'red',
                                    })
                                    ->default('red'),
                            ]),
                    ])->columns(2),
            Infolists\Components\Section::make('Salesforce')
                    ->description('Detail data dari Salesforce')
                    ->schema([
                        Infolists\Components\Fieldset::make('Periode')
                            ->schema([
                                TextEntry::make('periode')
                                        ->label('Periode'),
                                TextEntry::make('years')
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
                                Infolists\Components\TextEntry::make('schools_type')
                                    ->label('Negeri / Swasta'),
                                Infolists\Components\TextEntry::make('student_count')
                                    ->label('Jumlah Siswa'),
                                Infolists\Components\TextEntry::make('provinces')
                                    ->label('Provinsi'),
                                Infolists\Components\TextEntry::make('regencies')
                                    ->label('Kota / Kabupaten'),
                                Infolists\Components\TextEntry::make('area')
                                    ->label('Wilayah')
                                    ->placeholder('tidak ada wilayah'),
                            ]),


                        Infolists\Components\Fieldset::make('Bagan')
                            ->schema([
                                TextEntry::make('principal')
                                    ->label('Kepala Sekolah'),
                                TextEntry::make('principal_phone')
                                    ->label('No Hp Kepala Sekolah'),
                                TextEntry::make('curriculum_deputies')
                                    ->label('Wakakurikulum'),
                                TextEntry::make('curriculum_deputies_phone')
                                    ->label('No Hp Wakakurikulum'),
                                TextEntry::make('counselor_coordinators')
                                    ->label('Koordinator BK'),
                                TextEntry::make('counselor_coordinators_phone')
                                    ->label('No Hp Koordinator BK'),
                                TextEntry::make('proctors')
                                    ->label('Proktor'),
                                TextEntry::make('proctors_phone')
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
                                Infolists\Components\TextEntry::make('group')
                                    ->label('Grup')
                                    ->dateTime('l, jS F Y')
                                    ->placeholder('Belum Terjadwal'),
                                Infolists\Components\TextEntry::make('bimtek')
                                    ->label('Bimtek')
                                    ->dateTime('l, jS F Y')
                                    ->placeholder('Belum Terjadwal'),
                            ]),

                        Infolists\Components\Fieldset::make('')
                            ->schema([
                                Infolists\Components\TextEntry::make('account_count_created')
                                    ->label('Akun Dibuat')
                                    ->placeholder('Belum Terbuat'),
                                Infolists\Components\TextEntry::make('implementer_count')
                                    ->label('Pelaksanaan')
                                    ->placeholder('Belum terbuat'),
                                Infolists\Components\TextEntry::make('difference')
                                    ->label('Selisih')
                                    ->placeholder('Belum terbuat'),
                            ]),

                        Infolists\Components\Fieldset::make('Konsultasi')
                            ->schema([
                                Infolists\Components\IconEntry::make('students_download')
                                    ->label('Download Siswa')
                                    ->icon(fn (string $state): string => match ($state) {
                                        'ya' => 'heroicon-s-check-circle',
                                        'tidak' => 'heroicon-s-x-circle',
                                    })
                                    ->color(fn (string $state): string => match ($state) {
                                        'ya' => 'success',
                                        'tidak' => 'danger',
                                    })
                                    ->placeholder('Tidak Ada Status'),
                                Infolists\Components\IconEntry::make('schools_download')
                                    ->label('Download Sekolah')
                                    ->icon(fn (string $state): string => match ($state) {
                                        'ya' => 'heroicon-s-check-circle',
                                        'tidak' => 'heroicon-s-x-circle',
                                    })
                                    ->color(fn (string $state): string => match ($state) {
                                        'ya' => 'success',
                                        'tidak' => 'danger',
                                    })
                                    ->placeholder('Tidak Ada Status'),
                                Infolists\Components\IconEntry::make('pm')
                                    ->label('PM')
                                    ->icon(fn (string $state): string => match ($state) {
                                        'ya' => 'heroicon-s-check-circle',
                                        'tidak' => 'heroicon-s-x-circle',
                                    })
                                    ->color(fn (string $state): string => match ($state) {
                                        'ya' => 'success',
                                        'tidak' => 'danger',
                                    })
                                    ->placeholder('Tidak Ada Status'),
                            ])->columns(3),

                        Infolists\Components\Fieldset::make('')
                            ->schema([
                                Infolists\Components\TextEntry::make('counselor_consultation_date')
                                    ->label('Konsul BK')
                                    ->dateTime('l, jS F Y')
                                    ->placeholder('Belum Terjadwal'),
                                Infolists\Components\TextEntry::make('student_consultation_date')
                                    ->label('Konsul Siswa')
                                    ->dateTime('l, jS F Y')
                                    ->placeholder('Belum Terjadwal'),
                            ]),

                    ])->columns(2),

                Infolists\Components\Section::make('Finance')
                    ->description('Detail Data Finance')
                    ->schema([

                        Infolists\Components\Fieldset::make('Opsi Jumlah Akun / Jumlah Pelaksanaan')
                        ->schema([
                            TextEntry::make('option_price')
                                ->label('')
                                ->placeholder('Belum di Pilih'),

                        ]),


                        Infolists\Components\Fieldset::make('Nominal')
                            ->schema([
                                TextEntry::make('price')
                                    ->label('Harga SPJ')
                                    ->money('IDR')
                                    ->placeholder('Belum Terisi'),
                                TextEntry::make('net_2')
                                    ->label('Harga Net')
                                    ->money('IDR')
                                    ->placeholder('Belum Terisi'),
                            ])->columns(3),

                        Infolists\Components\Fieldset::make('')
                            ->label('TRC')
                            ->schema([
                                TextEntry::make('student_count_1')
                                    ->label('Selisih Siswa TRC')
                                    ->placeholder('Belum Terisi'),
                                TextEntry::make('net')
                                    ->label('Satuan')
                                    ->money('IDR')
                                    ->placeholder('Belum Terisi'),
                                TextEntry::make('subtotal_1')
                                    ->label('Sub Total')
                                    ->money('IDR')
                                    ->placeholder('Belum Terisi'),
                            ])->columns(3),

                        Infolists\Components\Fieldset::make('')
                            ->label('MITRA')
                            ->schema([
                                TextEntry::make('mitra_difference')
                                    ->label('Selisih Siswa Sekolah')
                                    ->placeholder('Belum Terisi'),
                                TextEntry::make('mitra_net')
                                    ->label('Satuan')
                                    ->money('IDR')
                                    ->placeholder('Belum Terisi'),
                                TextEntry::make('mitra_subtotal')
                                    ->label('Sub Total')
                                    ->money('IDR')
                                    ->placeholder('Belum Terisi'),

                                TextEntry::make('implementer_count')
                                    ->label('SS')
                                    ->placeholder('Belum Terisi'),
                                TextEntry::make('ss_net')
                                    ->label('Satuan')
                                    ->money('IDR')
                                    ->placeholder('Belum Terisi'),
                                TextEntry::make('ss_subtotal')
                                    ->label('Sub Total')
                                    ->money('IDR')
                                    ->placeholder('Belum Terisi'),

                                TextEntry::make('dll_difference')
                                    ->label('SS')
                                    ->placeholder('Belum Terisi'),
                                TextEntry::make('dll_net')
                                    ->label('Satuan')
                                    ->money('IDR')
                                    ->placeholder('Belum Terisi'),
                                TextEntry::make('dll_subtotal')
                                    ->label('Sub Total')
                                    ->money('IDR')
                                    ->placeholder('Belum Terisi'),
                            ])->columns(3),

                        Infolists\Components\Fieldset::make('')
                            ->label('Total')
                            ->schema([
                                TextEntry::make('total')
                                    ->label('Total Dana Sesuai SPJ')
                                    ->money('IDR')
                                    ->placeholder('Belum Terisi'),
                                TextEntry::make('total_net')
                                    ->label('Total Net')
                                    ->money('IDR')
                                    ->placeholder('Belum Terisi'),
                            ])->columns(3),

                        Infolists\Components\Fieldset::make('')
                            ->label('Tanggal')
                            ->schema([
                                TextEntry::make('invoice_date')
                                    ->label('Invoice')
                                    ->dateTime('l, jS F Y')
                                    ->placeholder('Belum Terjadwal'),
                                TextEntry::make('payment_date')
                                    ->label('Pembayaran')
                                    ->dateTime('l, jS F Y')
                                    ->placeholder('Belum Terjadwal'),
                                TextEntry::make('spk')
                                    ->label('SPK di Kirim')
                                    ->dateTime('l, jS F Y')
                                    ->placeholder('Belum Terjadwal'),
                                TextEntry::make('payment_name')
                                    ->label('Pembayaran Via')
                                    ->placeholder('Belum Terisi')
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
                                    ->placeholder('Belum Terisi'),
                                TextEntry::make('total')
                                    ->label('Uang Sejumlah')
                                    ->money('IDR')
                                    ->placeholder('Belum Terisi'),
                                TextEntry::make('detail_kwitansi')
                                    ->label('Guna Pembayaran')
                                    ->placeholder('Belum Terisi'),
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
                                    ->placeholder('Belum Terisi'),
                                TextEntry::make('number_invoice')
                                    ->label('Nomor Invoice')
                                    ->placeholder('Belum Terisi'),
                                TextEntry::make('detail_invoice')
                                    ->label('Deskripsi')
                                    ->placeholder('Belum Terisi'),
                            ])->columns(1),

                        Infolists\Components\Fieldset::make('')
                            ->schema([
                                TextEntry::make('qty_invoice')
                                    ->label('Kuantitas')
                                    ->placeholder('Belum Terisi'),
                                TextEntry::make('unit_price')
                                    ->label('Harga Per Unit')
                                    ->money('IDR')
                                    ->placeholder('Belum Terisi'),
                                TextEntry::make('amount_invoice')
                                    ->label('Jumlah')
                                    ->money('IDR')
                                    ->placeholder('Belum Terisi'),
                                TextEntry::make('subtotal_invoice')
                                    ->label('Sub Total')
                                    ->money('IDR')
                                    ->placeholder('Belum Terisi'),
                            ])->columns(2),

                        Infolists\Components\Fieldset::make('')
                            ->schema([
                                TextEntry::make('tax_rate')
                                    ->label('PPH 23')
                                    ->formatStateUsing(function ($state) {

                                        // Kalau disimpan 2 (artinya 2%), tampilkan "2%"
                                        // Kalau disimpan 0.02 (decimal), ubah ke persen:
                                        $value = is_numeric($state) && (float)$state > 0 && (float)$state < 1
                                            ? (float)$state * 100
                                            : (float)$state;

                                        // Rapikan trailing .00
                                        $str = rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');

                                        return $str . '%';
                                    })
                                    ->placeholder('Belum Terisi'),
                                TextEntry::make('sales_tsx')
                                    ->label('PPN')
                                    ->formatStateUsing(function ($state) {

                                        // Kalau disimpan 2 (artinya 2%), tampilkan "2%"
                                        // Kalau disimpan 0.02 (decimal), ubah ke persen:
                                        $value = is_numeric($state) && (float)$state > 0 && (float)$state < 1
                                            ? (float)$state * 100
                                            : (float)$state;

                                        // Rapikan trailing .00
                                        $str = rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');

                                        return $str . '%';
                                    })
                                    ->placeholder('Belum Terisi'),
                                TextEntry::make('total_invoice')
                                    ->label('Total')
                                    ->money('IDR')
                                    ->placeholder('Belum Terisi'),
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
            Tables\Filters\SelectFilter::make('latestStatusLog.status.color')
                ->label('Status Warna')
                ->options([
                    'red'    => 'Merah',
                    'yellow' => 'Kuning',
                    'blue'   => 'Biru',
                    'green'  => 'Hijau',
                ])
                ->preload()
                ->indicator('Status Warna')
                ->query(function (Builder $query, array $data) {
                    if (empty($data['value'])) {
                        return;
                    }

                    $query->whereHas(
                        'status',
                        fn (Builder $q) =>
                        $q->where('color', $data['value'])
                    );
                }),
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
            'finance'
        ];
    }
}
