<?php

namespace App\Filament\Components;

use Carbon\Carbon;
use Filament\Tables;
use App\Models\Status;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists;
use App\Filament\Enum\Periode;
use App\Filament\Enum\Program;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Fieldset;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\{TextColumn};
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\TextEntry;
use Filament\Forms\Components\DateTimePicker;
use Creasi\Nusa\Models\{Province, Regency, District};
use Filament\Forms\Components\{Select, TextInput, Section};
use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;

class Admin
{
    protected static function meta(Get $get): array
    {
        $type = $get('type') ?? 'apps';

        return match ($type) {
            'anbk' => [
                'nameRegister' => 'ANBK',
                'DescriptionRegister' => 'ASESMEN NASIONAL BERBASIS KOMPUTER',
            ],
            'apps' => [
                'nameRegister' => 'APPS',
                'DescriptionRegister' => 'ASESMEN PSIKOTES POTENSI SISWA',
            ],
            'snbt' => [
                'nameRegister' => 'SNBT',
                'DescriptionRegister' => 'SELEKSI NASIONAL BERDASARKAN TES',
            ],
            'tka' => [
                'nameRegister' => 'TKA',
                'DescriptionRegister' => 'TEST KEMAMPUAN AKADEMIK',
            ],
            default => [
                'nameRegister' => 'APPS',
                'DescriptionRegister' => 'ASESMEN PSIKOTES POTENSI SISWA',
            ],
        };
    }

    protected static function metaInfo(Model $record): array
    {
        $type = $record->type;

        return match ($type) {
            'anbk' => [
                'nameRegister' => 'ANBK',
                'DescriptionRegister' => 'ASESMEN NASIONAL BERBASIS KOMPUTER',
            ],
            'apps' => [
                'nameRegister' => 'APPS',
                'DescriptionRegister' => 'ASESMEN PSIKOTES POTENSI SISWA',
            ],
            'snbt' => [
                'nameRegister' => 'SNBT',
                'DescriptionRegister' => 'SELEKSI NASIONAL BERDASARKAN TES',
            ],
            'tka' => [
                'nameRegister' => 'TKA',
                'DescriptionRegister' => 'TEST KEMAMPUAN AKADEMIK',
            ],
            default => [
                'nameRegister' => 'NONE',
                'DescriptionRegister' => 'NONE',
            ],
        };
    }


    public static function getDifference(Get $get, Set $set): void
    {

        $accountCount = (int) $get('account_count_created');
        $implementerCount = (int) $get('implementer_count');

        if ($accountCount !== 0 || $implementerCount !== 0) {
            $set('difference', abs($accountCount - $implementerCount));
        } else {
            $set('difference', 0);
        }
    }

    public static function formSchema(): array
    {
        return [
            Section::make('Program')
                ->description('Pilih Program')
                ->schema([
                    Select::make('type')
                        ->label('Program')
                        ->options(Program::list()),
                    Select::make('status_id')
                        ->label('Status')
                        ->preload()
                        ->relationship(
                            name: 'status',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn(Builder $query) => $query
                                ->orderBy('order')
                        )
                        ->searchable()
                        ->placeholder('Pilih status...')
                        ->columnSpan(1),
                ])->columns(2),

            Section::make('Periode')
                ->description('Pilih Periode dan Tahun Ajaran')
                ->schema([
                    Select::make('periode')
                        ->label('Periode')
                        ->options(Periode::list()),
                    TextInput::make('years')
                        ->label('Tahun')
                        ->maxLength(255),
                ])->columns(2),

            Section::make(fn(Get $get) => self::meta($get)['nameRegister'])
                ->description(fn(Get $get) => self::meta($get)['DescriptionRegister'])
                ->schema([
                    DateTimePicker::make('date_register')
                        ->label('Tanggal Pendaftaran')
                        ->native(false)
                        ->displayFormat('l, jS F Y H:i'),
                    Select::make('provinces')
                        ->label('Provinsi')
                        ->options(Province::all()->pluck('name', 'name'))
                        ->searchable()
                        ->reactive()
                        ->live(500),
                    Select::make('regencies')
                        ->label('Kota / Kabupaten')
                        ->preload()
                        ->searchable()
                        ->reactive()
                        ->live(100)
                        ->options(function (Get $get) {
                            $province = Province::where('name', $get('provinces'))->first();
                            $provinceCode = $province ? $province->code : null;
                            if ($provinceCode) {
                                return Regency::where('province_code', $provinceCode)->pluck('name', 'name');
                            }
                            return [];
                        }),
                    Select::make('area')
                        ->label('Wilayah')
                        ->options(function (Get $get) {
                            $regencies = Regency::where('name', $get('regencies'))->first();
                            $regenciesCode = $regencies ? $regencies->code : null;
                            if ($regenciesCode) {
                                if ($regenciesCode == '3101') {
                                    return ['kS 01' => 'KS 01', 'KS_02' => 'KS 02',];
                                } elseif ($regenciesCode == '3171') {
                                    return ['JP 01' => 'JP 01', 'JP 02' => 'JP 02',];
                                } elseif ($regenciesCode == '3172') {
                                    return ['JU 01' => 'JU 01', 'JU 02' => 'JU 02',];
                                } elseif ($regenciesCode == '3173') {
                                    return ['JB 01' => 'JB 01', 'JB 02' => 'JB 02',];
                                } elseif ($regenciesCode == '3174') {
                                    return ['JS 01' => 'JS 01', 'JS 02' => 'JU 02',];
                                } elseif ($regenciesCode == '3175') {
                                    return ['JT 01' => 'JT 01', 'JT 02' => 'JT 02',];
                                } else {
                                    return [];
                                }
                            }
                            return [];
                        })
                        ->visible(function (Get $get) {
                            return $get('provinces') === 'Dki Jakarta';
                        }),
                    Select::make('district')
                        ->label('Kecamatan')
                        ->preload()
                        ->searchable()
                        ->reactive()
                        ->live(100)
                        ->options(function (Get $get) {
                            $district = Regency::where('name', $get('regencies'))->first();
                            $regencyCode = $district ? $district->code : null;
                            if ($regencyCode) {
                                return District::where('regency_code', $regencyCode)->pluck('name', 'name');
                            }
                            return [];
                        }),
                    TextInput::make('curriculum_deputies')
                        ->label('Wakakurikulum')
                        ->maxLength(255),
                    TextInput::make('curriculum_deputies_phone')
                        ->label('No Handphone Wakakurikulum')
                        ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                        ->maxLength(255),
                    TextInput::make('counselor_coordinators')
                        ->label('Koordinator BK')
                        ->maxLength(255),
                    TextInput::make('counselor_coordinators_phone')
                        ->label('No Handphone Koordinator BK')
                        ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                        ->maxLength(255),
                    TextInput::make('proctors')
                        ->label('Proktor')
                        ->maxLength(255),
                    TextInput::make('proctors_phone')
                        ->label('No Handphone Proktor')
                        ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                        ->maxLength(255),
                    TextInput::make('student_count')
                        ->label('Jumlah Siswa')
                        ->numeric(),
                    DateTimePicker::make('implementation_estimate')
                        ->label('Estimasi Pelaksanaan')
                        ->native(false)
                        ->displayFormat('l, jS F Y H:i'),
                ])->columns(2),

            Section::make('Sekolah')
                ->description('Masukkan Detail Data Sekolah')
                ->schema([
                    TextInput::make('schools')
                        ->label('Nama Sekolah')
                        ->maxLength(255),
                    TextInput::make('class')
                        ->label('Kelas')
                        ->maxLength(10),
                    Select::make('education_level')
                        ->label('Jenjang')
                        ->options([
                            'SD' => 'SD',
                            'MI' => 'MI',
                            'SMP' => 'SMP',
                            'MTS' => 'MTS',
                            'SMA' => 'SMA',
                            'MA' => 'MA',
                            'SMK' => 'SMK',
                        ])
                        ->native(false),
                    select::make('description')
                        ->label('Keterangan')
                        ->options([
                            'ABK' => 'ABK',
                            'NON-ABK' => 'NON ABK',
                        ])
                        ->native(false),
                    select::make('schools_type')
                        ->label('Negeri / Swasta')
                        ->options([
                            'Negeri' => 'Negeri',
                            'Swasta' => 'Swasta',
                        ])
                        ->native(false),
                    TextInput::make('principal')
                        ->label('Nama Kepala Sekolah')
                        ->maxLength(255),
                    TextInput::make('principal_phone')
                        ->label('No Handphone Kepala Sekolah')
                        ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                        ->maxLength(255),
                ])->columns(2),


            Section::make('Akademik')
                ->description('Detail Data Akademik')
                ->schema([
                    DatePicker::make('group')
                        ->label('Grup')
                        ->native(false)
                        ->displayFormat('l, jS F Y'),
                    DatePicker::make('bimtek')
                        ->label('Bimtek')
                        ->native(false)
                        ->displayFormat('l, jS F Y'),
                    TextInput::make('account_count_created')
                        ->label('Jumlah Akun Dibuat')
                        ->live(debounce: 1000)
                        ->default('0')
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            self::getDifference($get, $set);
                        }),
                    TextInput::make('implementer_count')
                        ->label('Jumlah Akun Pelaksanaan')
                        ->live(debounce: 1000)
                        ->default('0')
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            self::getDifference($get, $set);
                        }),
                    TextInput::make('difference')
                        ->label('Akun Dibuat')
                        ->readOnly()
                        ->numeric()
                        ->minValue(0)
                        ->live(),
                ])->columns(2),

            Section::make('Konsultasi')
                ->description('Detail Data Konsultasi')
                ->schema([
                    Section::make('')
                        ->schema([
                            Radio::make('schools_download')
                                ->label('Download Sekolah')
                                ->options([
                                    'YA' => 'YA',
                                    'TIDAK' => 'TIDAK',
                                ])
                                ->inline(),
                            Radio::make('students_download')
                                ->label('Download Siswa')
                                ->options([
                                    'YA' => 'YA',
                                    'TIDAK' => 'TIDAK',
                                ])
                                ->inline(),
                            Radio::make('pm')
                                ->label('PM')
                                ->options([
                                    'YA' => 'YA',
                                    'TIDAK' => 'TIDAK',
                                ])
                                ->inline(),
                        ])->columns(2),
                    DatePicker::make('counselor_consultation_date')
                        ->label('Konsul BK')
                        ->native(false)
                        ->displayFormat('l, jS F Y'),
                    DatePicker::make('student_consultation_date')
                        ->label('Konsul Siswa')
                        ->native(false)
                        ->displayFormat('l, jS F Y'),
                ])->columns(2),

            Section::make('Finance')
                ->description('Data dari Finance')
                ->schema([

                    Fieldset::make('Akun Siswa')
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
        ];
    }

    public static function columns(): array
    {
        return [
            Split::make([
                TextColumn::make('type')
                    ->label('Program')
                    ->description('Program', position: 'above')
                    ->extraAttributes(['class' => 'uppercase']),
                TextColumn::make('schools')
                    ->label('Sekolah')
                    ->description('Sekolah', position: 'above')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('periode')
                    ->label('Periode')
                    ->description('Periode', position: 'above')
                    ->wrap(),
                TextColumn::make('years')
                    ->label('Tahun')
                    ->description('Tahun', position: 'above'),

                TextColumn::make('latestStatusLog.status.color')
                    ->label('Status')
                    ->description('Status', position: 'above')
                    ->badge()
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->color(fn(string $state): string => match ($state) {
                        'green' => 'green',
                        'blue' => 'blue',
                        'yellow' => 'yellow',
                        'red' => 'red',
                    })
                    ->default('red'),
            ])->from('md')

        ];
    }

    public static function infolist(Model $record): array
    {
        return [
            Infolists\Components\Section::make(fn() => self::metaInfo($record)['nameRegister'])
                ->description(fn() => self::metaInfo($record)['DescriptionRegister'])
                ->schema([
                    Infolists\Components\Fieldset::make('Aktifitas Saat ini')
                        ->schema([
                            Infolists\Components\TextEntry::make('status.name')
                                ->label(''),
                            Infolists\Components\IconEntry::make('latestStatusLog.status.order')
                                ->label('')
                                ->icon(function ($state) {
                                    // $state = nilai order (bisa null)
                                    static $iconByOrder;

                                    if ($iconByOrder === null) {
                                        // Ambil sekali: [order => icon]
                                        $iconByOrder = Status::query()
                                            ->pluck('icon', 'order')  // pastikan kolom 'icon' ada
                                            ->all();
                                    }

                                    $order = (int) $state;
                                    return $iconByOrder[$order] ?? 'heroicon-m-clock';
                                })
                                ->color(function ($state) {
                                    static $colorByOrder;

                                    if ($colorByOrder === null) {
                                        // Ambil sekali: [order => color_dari_DB]
                                        $colorByOrder = Status::query()
                                            ->pluck('color', 'order')
                                            ->all();
                                    }

                                    $order = (int) $state;
                                    $raw = strtolower((string) ($colorByOrder[$order] ?? ''));

                                    // Map warna DB -> warna Filament
                                    return match ($raw) {
                                        'green' => 'green',
                                        'blue' => 'blue',
                                        'yellow' => 'yellow',
                                        'red' => 'red',
                                        default => 'gray',
                                    };
                                })
                                ->default('red')
                                ->size('lg'),
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
                                ->icon(fn(string $state): string => match ($state) {
                                    'YA' => 'heroicon-s-check-circle',
                                    'TIDAK' => 'heroicon-s-x-circle',
                                })
                                ->color(fn(string $state): string => match ($state) {
                                    'YA' => 'success',
                                    'TIDAK' => 'danger',
                                })
                                ->placeholder('Tidak Ada Status'),
                            Infolists\Components\IconEntry::make('schools_download')
                                ->label('Download Sekolah')
                                ->icon(fn(string $state): string => match ($state) {
                                    'YA' => 'heroicon-s-check-circle',
                                    'TIDAK' => 'heroicon-s-x-circle',
                                })
                                ->color(fn(string $state): string => match ($state) {
                                    'YA' => 'success',
                                    'TIDAK' => 'danger',
                                })
                                ->placeholder('Tidak Ada Status'),
                            Infolists\Components\IconEntry::make('pm')
                                ->label('PM')
                                ->icon(fn(string $state): string => match ($state) {
                                    'YA' => 'heroicon-s-check-circle',
                                    'TIDAK' => 'heroicon-s-x-circle',
                                })
                                ->color(fn(string $state): string => match ($state) {
                                    'YA' => 'success',
                                    'TIDAK' => 'danger',
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
                                ->formatStateUsing(fn(string $state): string => __("{$state}%"))
                                ->placeholder('Belum Terisi'),
                            TextEntry::make('sales_tsx')
                                ->label('PPN')
                                ->formatStateUsing(fn(string $state): string => __("{$state}%"))
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
            Tables\Filters\SelectFilter::make('users')
                ->label('Salesforce')
                ->relationship('users', 'name')
                ->searchable()
                ->preload()
                ->indicator('Salesforce'),
            Tables\Filters\SelectFilter::make('type')
                ->label('Program')
                ->options(Program::list())
                ->preload()
                ->indicator('Program'),
            Tables\Filters\SelectFilter::make('periode')
                ->label('Periode')
                ->options(Periode::list())
                ->preload()
                ->indicator('Periode'),
            Tables\Filters\SelectFilter::make('latestStatusLog.status.color')
                ->label('Status Warna')
                ->options([
                    'red' => 'Merah',
                    'yellow' => 'Kuning',
                    'blue' => 'Biru',
                    'green' => 'Hijau',
                ])
                ->preload()
                ->indicator('Status Warna')
                ->query(function (Builder $query, array $data) {
                    if (empty($data['value'])) {
                        return;
                    }

                    $query->whereHas(
                        'status',
                        fn(Builder $q) =>
                        $q->where('color', $data['value'])
                    );
                }),
        ];
    }

    public static function actions(): array
    {
        return [
            Tables\Actions\ViewAction::make(),
            // Tables\Actions\EditAction::make(),
        ];
    }

    public static function bulkActions(): array
    {
        return [
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
                FilamentExportBulkAction::make('Export')
                    ->withColumns(self::TextColumns())
                    ->disableTableColumns()
                    ->formatStates([
                        'type' => fn(?Model $record) => strtoupper($record->type),
                    ])
            ]),
        ];
    }

    public static function getRoles(): array
    {
        return ['admin'];
    }

    public static function TextColumns(): array
    {
        return [
            // --- Sales ---
            TextColumn::make('type')->label('Program'),
            TextColumn::make('periode')->label('Periode'),
            TextColumn::make('years')->label('Tahun'),
            TextColumn::make('date_register')
                ->label('Tanggal Pendaftaran')
                ->formatStateUsing(fn($state) => $state ? Carbon::parse($state)->translatedFormat('l, j F Y H:i') : '-'),
            TextColumn::make('provinces')->label('Provinsi'),
            TextColumn::make('regencies')->label('Kota / Kabupaten'),
            TextColumn::make('area')->label('Wilayah'),
            TextColumn::make('district')->label('Kecamatan'),
            TextColumn::make('student_count')->label('Jumlah Siswa'),
            TextColumn::make('implementation_estimate')
                ->label('Estimasi Pelaksanaan')
                ->formatStateUsing(fn($state) => $state ? Carbon::parse($state)->translatedFormat('l, j F Y H:i') : '-'),
            TextColumn::make('curriculum_deputies')->label('Wakakurikulum'),
            TextColumn::make('curriculum_deputies_phone')->label('No. HP Wakakurikulum'),
            TextColumn::make('counselor_coordinators')->label('Koordinator BK'),
            TextColumn::make('counselor_coordinators_phone')->label('No. HP Koordinator BK'),
            TextColumn::make('proctors')->label('Proktor'),
            TextColumn::make('proctors_phone')->label('No. HP Proktor'),

            TextColumn::make('schools')->label('Sekolah'),
            TextColumn::make('class')->label('Kelas'),
            TextColumn::make('education_level')->label('Jenjang'),
            TextColumn::make('description')->label('Keterangan'),
            TextColumn::make('schools_type')->label('Negeri / Swasta'),

            TextColumn::make('principal')->label('Kepala Sekolah'),
            TextColumn::make('principal_phone')->label('No. HP Kepala Sekolah'),

            // --- Akademik & Teknisi ---
            TextColumn::make('group')
                ->label('Tanggal Group')
                ->formatStateUsing(fn($state) => $state ? Carbon::parse($state)->translatedFormat('l, j F Y') : '-')
                ->sortable(),
            TextColumn::make('bimtek')
                ->label('Tanggal Bimtek')
                ->formatStateUsing(fn($state) => $state ? Carbon::parse($state)->translatedFormat('l, j F Y') : '-')
                ->sortable(),
            TextColumn::make('account_count_created')->label('Akun Dibuat'),
            TextColumn::make('implementer_count')->label('Jumlah Pelaksana'),
            TextColumn::make('difference')->label('Selisih'),
            TextColumn::make('students_download')->label('Unduhan Siswa'),
            TextColumn::make('schools_download')->label('Unduhan Sekolah'),
            TextColumn::make('pm')->label('PM'),
            TextColumn::make('counselor_consultation_date')
                ->label('Konsultasi BK')
                ->formatStateUsing(fn($state) => $state ? Carbon::parse($state)->translatedFormat('l, j F Y') : '-'),
            TextColumn::make('student_consultation_date')
                ->label('Konsultasi Siswa')
                ->formatStateUsing(fn($state) => $state ? Carbon::parse($state)->translatedFormat('l, j F Y') : '-'),

            // --- Finance (utama) ---
            TextColumn::make('price')->label('Harga'),
            TextColumn::make('total')->label('Total'),
            TextColumn::make('net')->label('Netto'),
            TextColumn::make('total_net')->label('Total Netto'),
            TextColumn::make('invoice_date')
                ->label('Tgl Invoice')
                ->formatStateUsing(fn($state) => $state ? Carbon::parse($state)->translatedFormat('j F Y') : '-')
                ->sortable(),
            TextColumn::make('spk')
                ->label('Tgl SPK')
                ->formatStateUsing(fn($state) => $state ? Carbon::parse($state)->translatedFormat('j F Y') : '-')
                ->sortable(),
            TextColumn::make('payment_date')
                ->label('Tgl Pembayaran')
                ->formatStateUsing(fn($state) => $state ? Carbon::parse($state)->translatedFormat('j F Y') : '-')
                ->sortable(),
            TextColumn::make('payment_name')->label('Nama Pembayaran'),

            // --- Finance (Mitra) ---
            TextColumn::make('mitra_difference')->label('Mitra Selisih'),
            TextColumn::make('mitra_net')->label('Mitra Net'),
            TextColumn::make('mitra_subtotal')->label('Mitra Subtotal'),

            // --- Finance (SS) ---
            TextColumn::make('ss_difference')->label('SS Selisih'),
            TextColumn::make('ss_net')->label('SS Net'),
            TextColumn::make('ss_subtotal')->label('SS Subtotal'),

            // --- Finance (DLL) ---
            TextColumn::make('dll_difference')->label('DLL Selisih'),
            TextColumn::make('dll_net')->label('DLL Net'),
            TextColumn::make('dll_subtotal')->label('DLL Subtotal'),

            // --- Invoice detail ---
            TextColumn::make('detail_invoice')->label('Detail Invoice'),
            TextColumn::make('number_invoice')->label('Nomor Invoice'),
            TextColumn::make('qty_invoice')->label('Qty Invoice'),
            TextColumn::make('unit_price')->label('Harga Satuan'),
            TextColumn::make('amount_invoice')->label('Jumlah Invoice'),
            TextColumn::make('ppn')->label('PPN'),
            TextColumn::make('pph')->label('PPH'),
            TextColumn::make('subtotal_invoice')->label('Subtotal Invoice'),
            TextColumn::make('total_invoice')->label('Total Invoice'),

            TextColumn::make('detail_kwitansi')->label('Detail Kwitansi'),
            TextColumn::make('difference_total')->label('Total Selisih'),

            TextColumn::make('subtotal_1')->label('Subtotal 1'),
            TextColumn::make('subtotal_2')->label('Subtotal 2'),
            TextColumn::make('student_count_1')->label('Jumlah Siswa 1'),
        ];
    }
}
