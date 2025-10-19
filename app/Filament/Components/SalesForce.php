<?php

namespace App\Filament\Components;

use Carbon\Carbon;
use Filament\Tables;
use App\Models\Status;
use Filament\Forms\Get;
use Filament\Infolists;
use Illuminate\Support\Str;
use App\Filament\Enum\Jenjang;
use App\Filament\Enum\Periode;
use App\Filament\Enum\Program;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\{TextColumn};
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\Infolists\PhoneEntry;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;
use Creasi\Nusa\Models\{Province, Regency, District};
use Filament\Forms\Components\{Select, TextInput, Section};
use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;

class SalesForce
{
    protected static function meta(Get $get): array
    {
        $type = $get('type');

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
                        ->options(Program::list()),
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

            Section::make()
                ->description()
                ->schema([
                    DateTimePicker::make('date_register')
                        ->label('Tanggal Pendaftaran')
                        ->native(false)
                        ->seconds(false)
                        ->displayFormat('l, jS F Y H:i'),
                    Select::make('provinces')
                        ->label('Provinsi')
                        ->options(Province::all()->pluck('name', 'name'))
                        ->searchable()
                        ->reactive()
                        ->dehydrateStateUsing(fn (string $state): string => Str::upper($state))
                        ->live(500),
                    Select::make('regencies')
                        ->label('Kota / Kabupaten')
                        ->preload()
                        ->searchable()
                        ->reactive()
                        ->dehydrateStateUsing(fn (string $state): string => Str::upper($state))
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
                        ->dehydrateStateUsing(fn (string $state): string => Str::upper($state))
                        ->options(function (Get $get) {
                            $regencies = Regency::where('name', $get('regencies'))->first();
                            $regenciesCode = $regencies ? $regencies->code : null;
                            if ($regenciesCode) {
                                if ($regenciesCode == '31.01') {
                                    return ['kS 01' => 'KS 01', 'KS_02' => 'KS 02',];
                                } elseif ($regenciesCode == '31.71') {
                                    return ['JP 01' => 'JP 01', 'JP 02' => 'JP 02',];
                                } elseif ($regenciesCode == '31.72') {
                                    return ['JU 01' => 'JU 01', 'JU 02' => 'JU 02',];
                                } elseif ($regenciesCode == '31.73') {
                                    return ['JB 01' => 'JB 01', 'JB 02' => 'JB 02',];
                                } elseif ($regenciesCode == '31.74') {
                                    return ['JS 01' => 'JS 01', 'JS 02' => 'JU 02',];
                                } elseif ($regenciesCode == '31.75') {
                                    return ['JT 01' => 'JT 01', 'JT 02' => 'JT 02',];
                                } else {
                                    return [];
                                }
                            }
                            return [];
                        })
                        ->visible(function (Get $get) {
                            return $get('provinces') === 'Daerah Khusus Ibukota Jakarta';
                        }),
                    Select::make('district')
                        ->label('Kecamatan')
                        ->preload()
                        ->searchable()
                        ->reactive()
                        ->live(100)
                        ->dehydrateStateUsing(fn (string $state): string => Str::upper($state))
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
                        ->dehydrateStateUsing(fn (string $state): string => Str::upper($state))
                        ->maxLength(50)
                        ->live(),
                    PhoneInput::make('curriculum_deputies_phone')
                        ->label('No Handphone Wakakurikulum')
                        ->defaultCountry('ID')
                        ->live(),
                    TextInput::make('counselor_coordinators')
                        ->label('Koordinator BK')
                        ->dehydrateStateUsing(fn (string $state): string => Str::upper($state))
                        ->maxLength(255),
                    PhoneInput::make('counselor_coordinators_phone')
                        ->label('No Handphone Koordinator BK')
                        ->defaultCountry('ID'),
                    TextInput::make('proctors')
                        ->label('Proktor')
                        ->dehydrateStateUsing(fn (string $state): string => Str::upper($state))
                        ->maxLength(255),
                    PhoneInput::make('proctors_phone')
                        ->label('No Handphone Proktor')
                        ->defaultCountry('ID'),
                    TextInput::make('student_count')
                        ->label('Jumlah Siswa')
                        ->numeric(),
                    DateTimePicker::make('implementation_estimate')
                        ->label('Estimasi Pelaksanaan')
                        ->native(false)
                        ->seconds(false)
                        ->displayFormat('l, jS F Y H:i')
                        ->live(),
                ])->columns(2),

                Section::make('Sekolah')
                ->description('Masukkan Detail Data Sekolah')
                ->schema([
                    TextInput::make('schools')
                        ->label('Nama Sekolah')
                        ->dehydrateStateUsing(fn (string $state): string => Str::upper($state))
                        ->maxLength(255)
                        ->live(),
                    TextInput::make('class')
                        ->label('Kelas')
                        ->maxLength(10),
                    Select::make('education_level')
                        ->label('Jenjang')
                        ->options(Jenjang::list())
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
                            'NEGERI' => 'NEGERI',
                            'SWASTA' => 'SWASTA',
                        ])
                        ->native(false),
                    TextInput::make('principal')
                        ->label('Nama Kepala Sekolah')
                        ->dehydrateStateUsing(fn (string $state): string => Str::upper($state))
                        ->maxLength(255)
                        ->live(),
                    PhoneInput::make('principal_phone')
                        ->label('No Handphone Kepala Sekolah')
                        ->defaultCountry('ID')
                        ->live(),
                ])->columns(2),

                Section::make('Status')
                    ->description('Isi sesuai dengan status saat ini')
                    ->visible(function (Get $get) {
                        $keys = [
                            'principal',
                            'principal_phone',
                            'student_count',
                            'schools',
                            'implementation_estimate',
                            'curriculum_deputies',
                            'curriculum_deputies_phone',
                        ];

                        // tampil hanya jika SEMUA kolom di atas terisi (non-blank)
                        foreach ($keys as $key) {
                            if (blank($get($key))) {
                                return false;
                            }
                        }
                        return true;
                    })
                    ->schema([
                        Select::make('status_id')
                            ->label('Status')
                            ->preload()
                            ->relationship(
                                name: 'status',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query) => $query
                                    ->where('order', '<=', 2)
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
                    ->default('red'),
            ])->from('md')

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
                                        $raw   = strtolower((string) ($colorByOrder[$order] ?? ''));

                                        // Map warna DB -> warna Filament
                                        return match ($raw) {
                                            'green'  => 'green',
                                            'blue'   => 'blue',
                                            'yellow' => 'yellow',
                                            'red'    => 'red',
                                            default  => 'gray',
                                        };
                                    })
                                    ->default('red')
                                    ->size('lg'),

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
                                PhoneEntry::make('principal_phone')
                                    ->label('No Hp Kepala Sekolah')
                                    ->displayFormat(PhoneInputNumberType::NATIONAL),
                                Infolists\Components\TextEntry::make('curriculum_deputies')
                                    ->label('Wakakurikulum'),
                                PhoneEntry::make('curriculum_deputies_phone')
                                    ->label('No Hp Wakakurikulum')
                                    ->displayFormat(PhoneInputNumberType::NATIONAL),
                                Infolists\Components\TextEntry::make('counselor_coordinators')
                                    ->label('Koordinator BK'),
                                PhoneEntry::make('counselor_coordinators_phone')
                                    ->label('No Hp Koordinator BK')
                                    ->displayFormat(PhoneInputNumberType::NATIONAL),
                                Infolists\Components\TextEntry::make('proctors')
                                    ->label('Proktor'),
                                PhoneEntry::make('proctors_phone')
                                    ->label('No Hp Proktor')
                                    ->displayFormat(PhoneInputNumberType::NATIONAL),
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
                ->options(Program::list())
                ->preload()
                ->indicator('Program'),
            Tables\Filters\SelectFilter::make('periode')
                ->label('Periode')
                ->options(Periode::list())
                ->preload()
                ->indicator('Periode'),
            Tables\Filters\SelectFilter::make('education_level')
                ->label('Jenjang')
                ->options(Jenjang::list())
                ->preload()
                ->indicator('Jenjang'),
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
        return [
            Tables\Actions\ViewAction::make(),
        ];
    }

    public static function bulkActions(): array
    {
        return [
            Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
        ];
    }
}
