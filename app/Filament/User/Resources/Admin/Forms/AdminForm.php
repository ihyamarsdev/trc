<?php

namespace App\Filament\User\Resources\Admin\Forms;

use App\Filament\Enum\Periode;
use App\Filament\Enum\Program;
use Creasi\Nusa\Models\District;
use Creasi\Nusa\Models\Province;
use Creasi\Nusa\Models\Regency;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Builder;

class AdminForm
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

    public static function schema(): array
    {
        return [
            Section::make('Form Registrasi')
                ->description('Data utama dan konfigurasi pendaftaran')
                ->schema([
                    Section::make('Data Sekolah & Akademik')
                        ->description('Masukkan Detail Data Sekolah dan Akademik')
                        ->schema([
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
                                                        'implementer_'.$get('implementer_count') => 'Jumlah Pelaksanaan',
                                                        'account_'.$get('account_count_created') => 'Jumlah Akun',
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
                                                    'CASH' => 'CASH',
                                                ]),
                                        ]),
                                ])->columns(2),
                        ])
                        ->columnSpanFull(),

                    Section::make('Konfigurasi')
                        ->description('Pengaturan Program dan Periode')
                        ->schema([
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
                                            modifyQueryUsing: fn (Builder $query) => $query
                                                ->orderBy('order')
                                        )
                                        ->searchable()
                                        ->placeholder('Pilih status...')
                                        ->columnSpan(1),
                                ])->columns(1),

                            Section::make('Periode')
                                ->description('Pilih Periode dan Tahun Ajaran')
                                ->schema([
                                    Select::make('periode')
                                        ->label('Periode')
                                        ->options(Periode::list()),
                                    TextInput::make('years')
                                        ->label('Tahun')
                                        ->maxLength(255),
                                ])->columns(1),

                            Section::make(fn (Get $get) => self::meta($get)['nameRegister'])
                                ->description(fn (Get $get) => self::meta($get)['DescriptionRegister'])
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
                                                    return ['kS 01' => 'KS 01', 'KS_02' => 'KS 02'];
                                                } elseif ($regenciesCode == '3171') {
                                                    return ['JP 01' => 'JP 01', 'JP 02' => 'JP 02'];
                                                } elseif ($regenciesCode == '3172') {
                                                    return ['JU 01' => 'JU 01', 'JU 02' => 'JU 02'];
                                                } elseif ($regenciesCode == '3173') {
                                                    return ['JB 01' => 'JB 01', 'JB 02' => 'JB 02'];
                                                } elseif ($regenciesCode == '3174') {
                                                    return ['JS 01' => 'JS 01', 'JS 02' => 'JU 02'];
                                                } elseif ($regenciesCode == '3175') {
                                                    return ['JT 01' => 'JT 01', 'JT 02' => 'JT 02'];
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
                                ])->columns(1),
                        ])
                        ->columnSpanFull(),
                ])
                ->columns(['lg' => 12])
                ->columnSpanFull(),
        ];
    }
}
