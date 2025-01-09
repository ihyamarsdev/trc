<?php

namespace App\Filament\Components;

use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Columns\{TextColumn};
use Illuminate\Database\Eloquent\Builder;
use Creasi\Nusa\Models\{Province, Regency, District};
use Filament\Forms\Components\{Select, TextInput, Section, DatePicker};
use App\Models\{SchoolYear, CurriculumDeputies, CounselorCoordinator, Proctors, Schools};
use App\Filament\Resources\{SchoolYearResource, CurriculumDeputiesResource, CounselorCoordinatorResource, ProctorsResource, SchoolsResource};

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
                        ->required()
                        ->options([
                            'Januari - Juni' => 'Januari - Juni',
                            'Juli - Desember' => 'Juli - Desember',
                        ]),
                    Select::make('school_years_id')
                        ->label('Tahun')
                        ->required()
                        ->options(SchoolYear::all()->pluck('name', 'id'))
                        ->searchable()
                        ->createOptionForm(fn (Form $form) => SchoolYearResource::form($form))
                        ->createOptionUsing(function (array $data): int {
                            $schoolyear = Schoolyear::create([
                                'name' => $data['name'],
                            ]);
                            return $schoolyear->id;
                        }),
                ])->columns(2),

            Section::make($options['nameRegister'])
                ->description($options['DescriptionRegister'])
                ->schema([
                    DatePicker::make('date_register')
                        ->label('Tanggal Pendaftaran')
                        ->native(false)
                        ->displayFormat('l, jS F Y')
                        ->required(),
                    Select::make('provinces')
                        ->label('Provinsi')
                        ->required()
                        ->options(Province::all()->pluck('name', 'name'))
                        ->searchable()
                        ->reactive()
                        ->live(500),
                    Select::make('regencies')
                        ->label('Kota / Kabupaten')
                        ->required()
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
                    Select::make('sudin')
                        ->label('Wilayah')
                        ->options(function (Get $get) {
                            $regencies = Regency::where('name', $get('regencies'))->first();
                            $regenciesCode = $regencies ? $regencies->code : null;
                            if ($regenciesCode) {
                                if ($regenciesCode == '3101') {
                                    return ['kS_01' => 'KS 01', 'KS_02' => 'KS 02',];
                                } elseif ($regenciesCode == '3171') {
                                    return ['JP_01' => 'JP 01', 'JP_02' => 'JP 02',];
                                } elseif ($regenciesCode == '3172') {
                                    return ['JU_01' => 'JU 01', 'JU_02' => 'JU 02',];
                                } elseif ($regenciesCode == '3173') {
                                    return ['JB_01' => 'JB 01', 'JB_02' => 'JB 02',];
                                } elseif ($regenciesCode == '3174') {
                                    return ['JS_01' => 'JS 01', 'JS_02' => 'JU 02',];
                                } elseif ($regenciesCode == '3175') {
                                    return ['JT_01' => 'JT 01', 'JT_02' => 'JT 02',];
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
                        ->required()
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
                    Select::make('curriculum_deputies_id')
                        ->label('Wakakurikulum')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->relationship('curriculum_deputies', modifyQueryUsing: fn (Builder $query) => $query->orderBy('name')->orderBy('phone'), )
                        ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->name} - No HP: {$record->phone}")
                        ->createOptionForm(fn (Form $form) => CurriculumDeputiesResource::form($form)),
                    Select::make('counselor_coordinators_id')
                        ->label('Koordinator BK ')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->relationship('counselor_coordinators', modifyQueryUsing: fn (Builder $query) => $query->orderBy('name')->orderBy('phone'), )
                        ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->name} - No HP: {$record->phone}")
                        ->createOptionForm(fn (Form $form) => CounselorCoordinatorResource::form($form)),
                    Select::make('proctors_id')
                        ->label('Proktor ')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->relationship('proctors', modifyQueryUsing: fn (Builder $query) => $query->orderBy('name')->orderBy('phone'), )
                        ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->name} - No HP: {$record->phone}")
                        ->createOptionForm(fn (Form $form) => ProctorsResource::form($form)),
                    TextInput::make('student_count')
                        ->label('Jumlah Siswa')
                        ->required()
                        ->numeric(),
                    DatePicker::make('implementation_estimate')
                        ->label('Estimasi Pelaksanaan')
                        ->native(false)
                        ->displayFormat('l, jS F Y')
                        ->required(),
                ])->columns(2),

                Section::make('Sekolah')
                ->description('Masukkan Detail Data Sekolah')
                ->schema([
                    TextInput::make('schools')
                        ->label('Nama Sekolah')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('class')
                        ->label('Kelas')
                        ->required()
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
                    select::make('education_level_type')
                        ->label('Negeri / Swasta')
                        ->options([
                            'Negeri' => 'Negeri',
                            'Swasta' => 'Swasta',
                        ])
                        ->native(false),
                    TextInput::make('principal')
                        ->label('Nama Kepala Sekolah')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('phone_principal')
                        ->label('No Handphone Kepala Sekolah')
                        ->tel()
                        ->required()
                        ->maxLength(255),
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
            TextColumn::make('school_years.name')
                ->label('Tahun'),
            TextColumn::make('users.name')
                ->label('User')
                ->searchable(),
            TextColumn::make('date_register')
                ->label('Tanggal Pendaftaran')
                ->date('l, jS F Y')
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
                ->label('Wilayah')
                ->badge(),
            TextColumn::make('district')
                ->label('Kecamatan')
                ->formatStateUsing(function ($state) {
                    $district = District::search($state)->first();
                    return $district ? $district->name : 'Unknown';
                }),
            TextColumn::make('schools')
                ->label('Sekolah'),
            TextColumn::make('class')
                ->label('Kelas'),
            TextColumn::make('education_level')
                ->label('Jenjang'),
            TextColumn::make('description')
                ->label('Keterangan'),
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
                ->label('Estimasi Pelaksanaan')
                ->date('l, jS F Y'),
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
            Tables\Filters\SelectFilter::make('school_years_id')
                ->label('Tahun Ajaran')
                ->options(SchoolYear::all()->pluck('name', 'id'))
                ->preload()
                ->searchable()
                ->indicator('Tahun Ajaran'),
                ];
    }
}
