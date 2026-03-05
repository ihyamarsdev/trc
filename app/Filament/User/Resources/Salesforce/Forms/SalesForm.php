<?php

namespace App\Filament\User\Resources\Salesforce\Forms;

use App\Filament\Enum\Jenjang;
use App\Filament\Enum\Periode;
use App\Filament\Enum\Program;
use App\Models\Status;
use Creasi\Nusa\Models\District;
use Creasi\Nusa\Models\Province;
use Creasi\Nusa\Models\Regency;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class SalesForm
{
    public static function schema(): array
    {
        return [
            Section::make('Form Sales')
                ->description('Data pendaftaran, sekolah, program, dan status')
                ->schema([
                    // === MAIN CONTENT ===
                    Group::make()
                        ->schema([
                            Section::make('Program & Periode')
                                ->description('Pilih Program dan Periode')
                                ->schema([
                                    Select::make('type')
                                        ->label('Program')
                                        ->options(Program::list()),
                                    Select::make('periode')
                                        ->label('Periode')
                                        ->options(Periode::list()),
                                    TextInput::make('years')->label('Tahun')->maxLength(255),
                                ])
                                ->columns(3),

                            Section::make('Pendaftaran & Pelaksanaan')
                                ->description('Detail pendaftaran dan pelaksanaan')
                                ->schema([
                                    DateTimePicker::make('date_register')
                                        ->label(
                                            new \Illuminate\Support\HtmlString(
                                                '<span style="color: #ef4444;">Tanggal Pendaftaran</span>',
                                            ),
                                        )
                                        ->native(false)
                                        ->seconds(false)
                                        ->displayFormat('l, jS F Y H:i'),
                                    Select::make('provinces')
                                        ->label('Provinsi')
                                        ->options(Province::all()->pluck('name', 'name'))
                                        ->searchable()
                                        ->reactive()
                                        ->dehydrateStateUsing(
                                            fn (?string $state): string => Str::upper($state),
                                        )
                                        ->live(500),
                                    Select::make('regencies')
                                        ->label('Kota / Kabupaten')
                                        ->preload()
                                        ->searchable()
                                        ->reactive()
                                        ->dehydrateStateUsing(
                                            fn (?string $state): string => Str::upper($state),
                                        )
                                        ->live(100)
                                        ->options(function (Get $get) {
                                            $province = Province::where(
                                                'name',
                                                $get('provinces'),
                                            )->first();
                                            $provinceCode = $province ? $province->code : null;
                                            if ($provinceCode) {
                                                return Regency::where(
                                                    'province_code',
                                                    $provinceCode,
                                                )->pluck('name', 'name');
                                            }

                                            return [];
                                        }),
                                    Select::make('area')
                                        ->label('Wilayah')
                                        ->dehydrateStateUsing(
                                            fn (?string $state): string => Str::upper($state),
                                        )
                                        ->options(function (Get $get) {
                                            $regencies = Regency::where(
                                                'name',
                                                $get('regencies'),
                                            )->first();
                                            $regenciesCode = $regencies
                                                ? $regencies->code
                                                : null;
                                            if ($regenciesCode) {
                                                if ($regenciesCode == '31.01') {
                                                    return [
                                                        'kS 01' => 'KS 01',
                                                        'KS_02' => 'KS 02',
                                                    ];
                                                } elseif ($regenciesCode == '31.71') {
                                                    return [
                                                        'JP 01' => 'JP 01',
                                                        'JP 02' => 'JP 02',
                                                    ];
                                                } elseif ($regenciesCode == '31.72') {
                                                    return [
                                                        'JU 01' => 'JU 01',
                                                        'JU 02' => 'JU 02',
                                                    ];
                                                } elseif ($regenciesCode == '31.73') {
                                                    return [
                                                        'JB 01' => 'JB 01',
                                                        'JB 02' => 'JB 02',
                                                    ];
                                                } elseif ($regenciesCode == '31.74') {
                                                    return [
                                                        'JS 01' => 'JS 01',
                                                        'JS 02' => 'JU 02',
                                                    ];
                                                } elseif ($regenciesCode == '31.75') {
                                                    return [
                                                        'JT 01' => 'JT 01',
                                                        'JT 02' => 'JT 02',
                                                    ];
                                                } else {
                                                    return [];
                                                }
                                            }

                                            return [];
                                        })
                                        ->visible(function (Get $get) {
                                            return $get('provinces') ===
                                                'Daerah Khusus Ibukota Jakarta';
                                        }),
                                    Select::make('district')
                                        ->label('Kecamatan')
                                        ->preload()
                                        ->searchable()
                                        ->reactive()
                                        ->live(100)
                                        ->dehydrateStateUsing(
                                            fn (?string $state): string => Str::upper($state),
                                        )
                                        ->options(function (Get $get) {
                                            $district = Regency::where(
                                                'name',
                                                $get('regencies'),
                                            )->first();
                                            $regencyCode = $district ? $district->code : null;
                                            if ($regencyCode) {
                                                return District::where(
                                                    'regency_code',
                                                    $regencyCode,
                                                )->pluck('name', 'name');
                                            }

                                            return [];
                                        }),
                                    TextInput::make('curriculum_deputies')
                                        ->label(
                                            new \Illuminate\Support\HtmlString(
                                                '<span style="color: #ef4444;">Wakakurikulum</span>',
                                            ),
                                        )
                                        ->nullable()
                                        ->dehydrateStateUsing(
                                            fn (?string $state): string => Str::upper($state),
                                        )
                                        ->maxLength(50)
                                        ->live(),
                                    PhoneInput::make('curriculum_deputies_phone')
                                        ->label(
                                            new \Illuminate\Support\HtmlString(
                                                '<span style="color: #ef4444;">No Handphone Wakakurikulum</span>',
                                            ),
                                        )
                                        ->defaultCountry('ID')
                                        ->live(),
                                    TextInput::make('counselor_coordinators')
                                        ->label('Koordinator BK')
                                        ->nullable()
                                        ->dehydrateStateUsing(
                                            fn (?string $state): string => Str::upper($state),
                                        )
                                        ->maxLength(255),
                                    PhoneInput::make('counselor_coordinators_phone')
                                        ->label('No Handphone Koordinator BK')
                                        ->defaultCountry('ID'),
                                    TextInput::make('proctors')
                                        ->label('Proktor')
                                        ->nullable()
                                        ->dehydrateStateUsing(
                                            fn (?string $state): string => Str::upper($state),
                                        )
                                        ->maxLength(255),
                                    PhoneInput::make('proctors_phone')
                                        ->label('No Handphone Proktor')
                                        ->defaultCountry('ID'),
                                    TextInput::make('student_count')
                                        ->label(
                                            new \Illuminate\Support\HtmlString(
                                                '<span style="color: #ef4444;">Jumlah Siswa</span>',
                                            ),
                                        )
                                        ->numeric(),
                                    DateTimePicker::make('implementation_estimate')
                                        ->label(
                                            new \Illuminate\Support\HtmlString(
                                                '<span style="color: #ef4444;">Estimasi Pelaksanaan</span>',
                                            ),
                                        )
                                        ->native(false)
                                        ->seconds(false)
                                        ->displayFormat('l, jS F Y H:i')
                                        ->live(),
                                    Textarea::make('notes')->label('Catatan')->autosize()->columnSpanFull(),
                                ])
                                ->columns(2),

                            Section::make('Sekolah')
                                ->description('Masukkan Detail Data Sekolah')
                                ->schema([
                                    TextInput::make('schools')
                                        ->label(
                                            new \Illuminate\Support\HtmlString(
                                                '<span style="color: #ef4444;">Nama Sekolah</span>',
                                            ),
                                        )
                                        ->nullable()
                                        ->dehydrateStateUsing(
                                            fn (?string $state): string => Str::upper($state),
                                        )
                                        ->maxLength(255)
                                        ->live(),
                                    TextInput::make('class')->label('Kelas')->maxLength(10),
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
                                        ->label(
                                            new \Illuminate\Support\HtmlString(
                                                '<span style="color: #ef4444;">Nama Kepala Sekolah</span>',
                                            ),
                                        )
                                        ->nullable()
                                        ->dehydrateStateUsing(
                                            fn (?string $state): string => Str::upper($state),
                                        )
                                        ->maxLength(255)
                                        ->live(),
                                    PhoneInput::make('principal_phone')
                                        ->label(
                                            new \Illuminate\Support\HtmlString(
                                                '<span style="color: #ef4444;">No Handphone Kepala Sekolah</span>',
                                            ),
                                        )
                                        ->defaultCountry('ID')
                                        ->live(),
                                ])
                                ->columns(2),

                        ])
                        ->columnSpan(['lg' => 8]),

                    // === SIDEBAR ===
                    Group::make()
                        ->schema([
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
                                                ->orderBy('order'),
                                        )
                                        ->searchable()
                                        ->placeholder('Pilih status...')
                                        ->columnSpanFull()
                                        ->live()
                                        ->afterStateUpdated(function (Set $set, $state) {
                                            if ($state) {
                                                $color = Status::find($state)?->color;
                                                $set('status_color', $color);
                                            } else {
                                                $set('status_color', null);
                                            }
                                        }),
                                ])
                                ->columns(1),
                        ])
                        ->columnSpan(['lg' => 4]),
                ])
                ->columnSpanFull(),
        ];
    }
}
