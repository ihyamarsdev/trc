<?php

namespace App\Filament\Components;

use Filament\Tables;
use Filament\Forms\Get;
use Filament\Tables\Columns\{TextColumn};
use Filament\Forms\Components\DateTimePicker;
use Creasi\Nusa\Models\{Province, Regency, District};
use Filament\Forms\Components\{Select, TextInput, Section};

class SalesForce
{
    public static function schema(array $options = []): array
    {
        return [

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

            Section::make($options['nameRegister'])
                ->description($options['DescriptionRegister'])
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
                        ->tel()
                        ->maxLength(255),
                    TextInput::make('counselor_coordinators')
                        ->label('Koordinator BK')
                        ->maxLength(255),
                    TextInput::make('counselor_coordinators_phone')
                        ->label('No Handphone Koordinator BK')
                        ->tel()
                        ->maxLength(255),
                    TextInput::make('proctors')
                        ->label('Proktor')
                        ->maxLength(255),
                    TextInput::make('proctors_phone')
                        ->label('No Handphone Proktor')
                        ->tel()
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
                        ->tel()                      
                        ->maxLength(255),
                ])->columns(2),

                Section::make('Status')
                    ->description('Merah = Belum dikerjakan • Kuning = Sales & Akademik')
                    ->schema([
                        Select::make('status_color')
                            ->label('Status')
                            ->native(false)
                            ->options([
                                'kuning' => 'Kuning (Sales & Akademik)',
                                'merah'  => 'Belum dikerjakan (Merah)',
                            ])
                            ->searchable()
                            ->placeholder('Pilih status...')
                            ->helperText('Kuning: Sales & Akademik • Biru: Teknisi • Hijau: Finance')
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
            TextColumn::make('schools')
                ->label('Sekolah'),
            TextColumn::make('education_level')
                ->label('Jenjang'),
            TextColumn::make('status_color')
                ->label('Status')
                ->badge()
                ->formatStateUsing(fn ($state) => ucfirst($state)) // Kuning/Biru/Hijau
                ->color(fn (string $state): string => match ($state) {
                    'hijau'  => 'hijau',
                    'biru'   => 'biru',
                    'kuning' => 'kuning',
                    'merah'  => 'merah',
                })
                ->sortable()
                ->toggleable(),
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
                ->indicator('Periode'),
            Tables\Filters\SelectFilter::make('status_color')
                ->label('Status Warna')
                ->options([
                    'hijau'  => 'Hijau',
                    'biru'   => 'Biru',
                    'kuning' => 'Kuning',
                    'merah'  => 'Merah',
                ])
                ->preload()
                ->indicator('Status Warna'),
        ];
    }

    public static function getRoles(): array
    {
        return ['admin', 'sales'];
    }
}
