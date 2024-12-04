<?php

namespace App\Filament\Components;

use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Columns\{TextColumn};
use Illuminate\Database\Eloquent\Builder;
use Creasi\Nusa\Models\{Province, Regency};
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
                        ->label('Tahun Ajaran')
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
                        ->required(),
                    Select::make('provinces')
                        ->label('Provinsi')
                        ->required()
                        ->options(Province::all()->pluck('name', 'code'))
                        ->searchable()
                        ->reactive()
                        ->live(500),
                    Select::make('regencies')
                        ->label('Kabupaten')
                        ->required()
                        ->preload()
                        ->searchable()
                        ->reactive()
                        ->live(100)
                        ->options(function (Get $get) {
                            $provinceCode = $get('provinces');
                            if ($provinceCode) {
                                return Regency::where('province_code', $provinceCode)->pluck('name', 'code');
                            }
                            return [];
                        }),
                    Select::make('sudin')
                        ->label('Kota / Kab')
                        ->options(function (Get $get) {
                            $regenciesCode = $get('regencies');
                            if ($regenciesCode) {
                                if ($regenciesCode == '3101') {
                                    return ['ks_01' => 'KS 01', 'ks_02' => 'KS 02',];
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
                            return $get('provinces') === '31';
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
                        ->label('Proctor ')
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
                        ->required(),
                ])->columns(2),

                Section::make('Sekolah')
                ->description('Masukkan Detail Data Sekolah')
                ->schema([
                    TextInput::make('schools')
                        ->label('Nama Sekolah')
                        ->required()
                        ->maxLength(255),
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
                    TextInput::make('principal')
                        ->label('Nama Kepala Sekolah')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('phone_principal')
                        ->label('No Handphone Kepala Sekolah')
                        ->tel()
                        ->required()
                        ->maxLength(255),
                    select::make('education_level_type')
                        ->label('Negeri / Swasta')
                        ->options([
                            'Negeri' => 'Negeri',
                            'Swasta' => 'Swasta',
                        ]),
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
                ->label('Tahun Ajaran'),
            TextColumn::make('users.name')
                ->label('User')
                ->searchable(),
            TextColumn::make('date_register')
                ->label('Tanggal Pendaftaran')
                ->date()
                ->sortable(),
            TextColumn::make('provinces')
                ->label('Provinsi'),
            TextColumn::make('regencies')
                ->label('Kota / Kabupaten'),
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
                ->label('Estimasi Pelaksanaan')
                ->date(),
            ];
    }

    public static function filters(): array {
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
