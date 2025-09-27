<?php

namespace App\Filament\Components;

use Filament\Tables;
use Filament\Forms\Get;
use Filament\Infolists;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\{TextColumn};
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use Creasi\Nusa\Models\{Province, Regency, District};
use Filament\Forms\Components\{Select, TextInput, Section};

class SalesForce
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

    public static function schema(): array
    {
        return [
            Section::make('Program')
                ->description('Pilih Program')
                ->schema([
                    Select::make('type')
                        ->label('Program')
                        ->options([
                            'apps' => 'APPS',
                            'anbk' => 'ANBK',
                            'snbt' => 'SNBT',
                            'tka' => 'TKA',
                        ]),
                ])->columns(2),

            Section::make('Periode')
                ->description('Pilih Periode dan Tahun Ajaran')
                ->schema([
                    Select::make('periode')
                        ->label('Periode')
                        ->options([
                            'Januari - Juni' => 'Januari - Juni',
                            'Juli - Desember' => 'Juli - Desember',
                        ]),
                    TextInput::make('years')
                        ->label('Tahun')
                        ->maxLength(255),
                ])->columns(2),

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
                            $regencyCode = $district ? $district ->code : null;
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

                Section::make('Status')
                    ->description('Isi sesuai dengan status saat ini')
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
                TextColumn::make('no')
                    ->rowIndex(),
                TextColumn::make('type')
                    ->label('Program'),
                TextColumn::make('periode')
                    ->label('Periode'),
                TextColumn::make('years')
                    ->label('Tahun'),
                TextColumn::make('schools')
                    ->label('Sekolah'),
                TextColumn::make('education_level')
                    ->label('Jenjang'),
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
            ])
            ];
    }

    public static function infolist(): array
    {
        return [
            Infolists\Components\Section::make('Status')
                    ->description('Progres Activity Saat ini')
                    ->schema([
                        Infolists\Components\Fieldset::make('Kondisi Saat ini')
                            ->schema([
                                Infolists\Components\TextEntry::make('status.name')
                                    ->label(''),
                                Infolists\Components\IconEntry::make('latestStatusLog.status.color')
                                    ->label('')
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

            Infolists\Components\Section::make('Sales')
                    ->description('Detail data dari Sales')
                    ->schema([
                        Infolists\Components\Fieldset::make('Periode')
                            ->schema([
                                Infolists\Components\TextEntry::make('periode')
                                        ->label('Periode'),
                                Infolists\Components\TextEntry::make('years')
                                        ->label('Tahun'),
                            ]),

                        Infolists\Components\Fieldset::make('Salesforce')
                            ->schema([
                                Infolists\Components\TextEntry::make('users.name')
                                    ->label('User'),
                                Infolists\Components\TextEntry::make('type')
                                    ->label('Program'),
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
                                    ->default('-'),
                            ]),


                        Infolists\Components\Fieldset::make('Bagan')
                            ->schema([
                                Infolists\Components\TextEntry::make('principal')
                                    ->label('Kepala Sekolah'),
                                Infolists\Components\TextEntry::make('principal_phone')
                                    ->label('No Hp Kepala Sekolah'),
                                Infolists\Components\TextEntry::make('curriculum_deputies')
                                    ->label('Wakakurikulum'),
                                Infolists\Components\TextEntry::make('curriculum_deputies_phone')
                                    ->label('No Hp Wakakurikulum'),
                                Infolists\Components\TextEntry::make('counselor_coordinators')
                                    ->label('Koordinator BK'),
                                Infolists\Components\TextEntry::make('counselor_coordinators_phone')
                                    ->label('No Hp Koordinator BK'),
                                Infolists\Components\TextEntry::make('proctors')
                                    ->label('Proktor'),
                                Infolists\Components\TextEntry::make('proctors_phone')
                                    ->label('No Hp Proktor'),
                            ]),

                        Infolists\Components\Fieldset::make('')
                            ->schema([
                                Infolists\Components\TextEntry::make('date_register')
                                    ->label('Tanggal Pendaftaran')
                                    ->dateTime('l, jS F Y H:i'),
                                Infolists\Components\TextEntry::make('implementation_estimate')
                                    ->label('Estimasi Pelaksanaan')
                                    ->dateTime('l, jS F Y H:i'),
                            ]),
                        ]),
        ];
    }

    public static function filters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('type')
                ->label('Program')
                ->options([
                    'anbk' => 'ANBK',
                    'apps' => 'APPS',
                    'snbt' => 'SNBT',
                    'tka' => 'TKA',
                ])
                ->preload()
                ->indicator('Program'),
            Tables\Filters\SelectFilter::make('periode')
                ->label('Periode')
                ->options([
                    'Januari - Juni' => 'Januari - Juni',
                    'Juli - Desember' => 'Juli - Desember',
                ])
                ->preload()
                ->indicator('Periode'),
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
        ];
    }

    public static function getRoles(): array
    {
        return [
            'sales'
        ];
    }

    public static function actions(): array
    {
        return [];
    }
}
