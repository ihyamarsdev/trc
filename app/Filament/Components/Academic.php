<?php

namespace App\Filament\Components;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Columns\{TextColumn};
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\{Select, TextInput, Section, DatePicker, Radio};
use App\Models\{SchoolYear, CurriculumDeputies, CounselorCoordinator, Proctors, Schools};
use App\Filament\Resources\{SchoolYearResource, CurriculumDeputiesResource, CounselorCoordinatorResource, ProctorsResource, SchoolsResource};

class Academic {

    public static function formSchema(array $options = []): array
    {
        return [
            Section::make($options['nameRegister'])
                ->description($options['DescriptionRegister'])
                ->schema([
                    DatePicker::make('group')
                        ->label('Grup')
                        ->required(),
                    DatePicker::make('bimtek')
                        ->label('Bimtek')
                        ->required(),
                    TextInput::make('account_count_created')
                        ->label('Akun Dibuat')
                        ->required()
                        ->live(debounce: 500)
                        ->default('0')
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            self::getDifference($get, $set);
                        }),
                    TextInput::make('implementer_count')
                        ->label('Pelaksanaan')
                        ->required()
                        ->live(debounce: 500)
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

            Section::make()
                ->schema([
                    Radio::make('students_download')
                        ->label('Download Siswa')
                        ->options([
                            'Ya' => 'Ya',
                            'Tidak' => 'Tidak',
                        ])
                        ->inline(),
                    Radio::make('schools_download')
                        ->label('Download Sekolah')
                        ->options([
                            'Ya' => 'Ya',
                            'Tidak' => 'Tidak',
                        ])
                        ->inline(),
                    DatePicker::make('counselor_consultation_date')
                        ->label('Konsul BK')
                        ->required(),
                    DatePicker::make('student_consultation_date')
                        ->label('Konsul Siswa')
                        ->required(),
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
                ->label('Estimasi Pelaksana')
                ->date(),
        ];
    }

    public static function getDifference(Get $get, Set $set): void {

        $accountCount = (int) $get('account_count_created');
        $implementerCount = (int) $get('implementer_count');

        if ($accountCount !== 0 || $implementerCount !== 0) {
            $set('difference', abs($accountCount - $implementerCount));
        } else {
            $set('difference', 0);
        }
    }
}
