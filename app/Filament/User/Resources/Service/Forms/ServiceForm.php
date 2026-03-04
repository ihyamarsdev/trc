<?php

namespace App\Filament\User\Resources\Service\Forms;

use App\Filament\Enum\Jenjang;
use App\Filament\Enum\Periode;
use App\Filament\Enum\Program;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Enums\IconSize;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Ysfkaya\FilamentPhoneInput\Infolists\PhoneEntry;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;

class ServiceForm
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

    public static function configure(): array
    {
          return [
            Section::make("Status")
                ->description("Isi sesuai dengan status saat ini")
                ->schema([
                    Select::make("status_id")
                        ->label("Status")
                        ->preload()
                        ->relationship(
                            name: "status",
                            titleAttribute: "name",
                            modifyQueryUsing: fn(Builder $query) => $query
                                ->where("order", "<=", 10)
                                ->orderBy("order"),
                        )
                        ->searchable()
                        ->placeholder("Pilih status...")
                        ->columnSpan(1),
                ])
                ->columns(2),

            Section::make(fn(Get $get) => self::meta($get)["nameRegister"])
                ->description(
                    fn(Get $get) => self::meta($get)["DescriptionRegister"],
                )
                ->schema([
                    DatePicker::make("group")
                        ->label("Grup")
                        ->native(false)
                        ->displayFormat("l, jS F Y"),
                    DatePicker::make("bimtek")
                        ->label("Bimtek")
                        ->native(false)
                        ->displayFormat("l, jS F Y"),
                    TextInput::make("account_count_created")
                        ->label("Jumlah Akun Dibuat")
                        ->live(debounce: 1000)
                        ->default("0")
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            self::getDifference($get, $set);
                        }),
                    TextInput::make("implementer_count")
                        ->label("Jumlah Akun Pelaksanaan")
                        ->live(debounce: 1000)
                        ->default("0")
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            self::getDifference($get, $set);
                        }),
                    TextInput::make("difference")
                        ->label("Akun Dibuat")
                        ->readOnly()
                        ->numeric()
                        ->minValue(0)
                        ->live(),
                ])
                ->columns(2),

            Section::make("Konsultasi")
                ->description("Data Konsultasi")
                ->schema([
                    Section::make("")
                        ->schema([
                            Radio::make("schools_download")
                                ->label("Download Sekolah")
                                ->options([
                                    "YA" => "YA",
                                    "TIDAK" => "TIDAK",
                                ])
                                ->inline(),
                            Radio::make("students_download")
                                ->label("Download Siswa")
                                ->options([
                                    "YA" => "YA",
                                    "TIDAK" => "TIDAK",
                                ])
                                ->inline(),
                            Radio::make("pm")
                                ->label("PM")
                                ->options([
                                    "YA" => "YA",
                                    "TIDAK" => "TIDAK",
                                ])
                                ->inline(),
                        ])
                        ->columns(2),
                    DatePicker::make("counselor_consultation_date")
                        ->label("Konsul BK")
                        ->native(false)
                        ->displayFormat("l, jS F Y"),
                    DatePicker::make("student_consultation_date")
                        ->label("Konsul Siswa")
                        ->native(false)
                        ->displayFormat("l, jS F Y"),
                ])
                ->columns(2),
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
                TextColumn::make('schools')->label('Sekolah')->description('Sekolah', position: 'above')->searchable()->wrap(),
                TextColumn::make('periode')->label('Periode')->description('Periode', position: 'above')->extraAttributes(['class' => 'uppercase'])->wrap(),
                TextColumn::make('years')->label('Tahun')->description('Tahun', position: 'above'),
                TextColumn::make('latestStatusLog.status.name')
                    ->label('Status')
                    ->badge()
                    ->color(
                        fn($record) => match ($record->latestStatusLog?->status?->color) {
                            'green' => 'success',
                            'blue' => 'blue',
                            'yellow' => 'warning',
                            'red' => 'danger',
                            default => 'gray',
                        }
                    )
                    ->placeholder('Belum Ada Status'),
            ])->from('md'),
        ];
    }

    public static function TextColumns(): array
    {
        return [
            TextColumn::make('group')
                ->label('Grup')
                ->formatStateUsing(
                    fn($state) => Carbon::parse($state)->translatedFormat(
                        'l, jS F Y',
                    ),
                ),
            TextColumn::make('bimtek')
                ->label('Bimtek')
                ->formatStateUsing(
                    fn($state) => Carbon::parse($state)->translatedFormat(
                        'l, jS F Y',
                    ),
                ),
            TextColumn::make('account_count_created')->label(
                'Jumlah Akun Dibuat',
            ),
            TextColumn::make('implementer_count')->label('Jumlah Pelaksanaan'),
            TextColumn::make('difference')->label('Selisih'),
            TextColumn::make('students_download')->label('Siswa Download'),
            TextColumn::make('schools_download')->label('Sekolah Download'),
            TextColumn::make('pm')->label('PM'),
            TextColumn::make('counselor_consultation_date')
                ->label('Tanggal Konseling')
                ->formatStateUsing(
                    fn($state) => Carbon::parse($state)->translatedFormat(
                        'l, jS F Y',
                    ),
                ),
            TextColumn::make('student_consultation_date')
                ->label('Tanggal Konseling Siswa')
                ->formatStateUsing(
                    fn($state) => Carbon::parse($state)->translatedFormat(
                        'l, jS F Y',
                    ),
                ),
        ];
    }

    public static function infolist(Model $record): array
    {
        return [
            \Filament\Schemas\Components\Section::make(
                fn() => self::metaInfo($record)['nameRegister'],
            )
                ->description(
                    fn() => self::metaInfo($record)['DescriptionRegister'],
                )
                ->schema([
                    Fieldset::make(
                        'Aktifitas Saat ini',
                    )->schema([
                                TextEntry::make('status.name')
                                    ->label('')
                                    ->placeholder('Tidak Ada Status'),
                                IconEntry::make(
                                    'latestStatusLog.status.order',
                                )
                                    ->label('')
                                    ->icon(function ($state) {
                                        // $state = nilai order (bisa null)
                                        static $iconByOrder;

                                        if ($iconByOrder === null) {
                                            // Ambil sekali: [order => icon]
                                            $iconByOrder = Status::query()
                                                ->pluck('icon', 'order') // pastikan kolom 'icon' ada
                                                ->all();
                                        }

                                        $order = (int) $state;

                                        return $iconByOrder[$order] ??
                                            'heroicon-m-clock';
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
                                        $raw = strtolower(
                                            (string) ($colorByOrder[$order] ?? ''),
                                        );

                                        // Map warna DB -> warna Filament
                                        return match ($raw) {
                                            'green' => 'green',
                                            'blue' => 'blue',
                                            'yellow' => 'yellow',
                                            'red' => 'red',
                                            default => 'gray',
                                        };
                                    })
                                    ->placeholder('Tidak Ada Status')
                                    ->size(IconSize::Large),
                            ]),
                ])
                ->columns(2),

            \Filament\Schemas\Components\Section::make('Salesforce')
                ->description('Detail data dari Salesforce')
                ->schema([
                    Fieldset::make('Periode')->schema([
                        TextEntry::make('periode')->label('Periode')->placeholder('Tidak Ada Periode'),
                        TextEntry::make('years')->label('Tahun')->placeholder('Tidak Ada Tahun'),
                    ]),

                    Fieldset::make('Salesforce')->schema([
                        TextEntry::make('users.name')->label('User')->placeholder('Tidak Ada User'),
                    ]),

                    Fieldset::make('Sekolah')->schema([
                        TextEntry::make('schools')->label('Sekolah')->placeholder('Tidak Ada Sekolah'),
                        TextEntry::make('class')->label('Kelas')->placeholder('Tidak Ada Kelas'),
                        TextEntry::make('education_level')->label('Jenjang')->placeholder('Tidak Ada Jenjang'),
                        TextEntry::make('description')->label('Keterangan')->placeholder('Tidak Ada Keterangan'),
                        TextEntry::make(
                            'schools_type',
                        )->label('Negeri / Swasta')->placeholder('Tidak Ada Jenjang'),
                        TextEntry::make(
                            'student_count',
                        )->label('Jumlah Siswa')->placeholder('Tidak Ada Jumlah Siswa'),
                        TextEntry::make(
                            'provinces',
                        )->label('Provinsi')->placeholder('Tidak Ada Provinsi'),
                        TextEntry::make(
                            'regencies',
                        )->label('Kota / Kabupaten')->placeholder('Tidak Ada Kota / Kabupaten'),
                        TextEntry::make('area')
                            ->label('Wilayah')
                            ->placeholder('Tidak Ada Wilayah'),
                    ]),

                    Fieldset::make('Bagan')->schema([
                        TextEntry::make(
                            'principal',
                        )->label('Kepala Sekolah')->placeholder('Tidak Ada Kepala Sekolah'),
                        PhoneEntry::make('principal_phone')
                            ->label('No Hp Kepala Sekolah')
                            ->displayFormat(PhoneInputNumberType::NATIONAL)
                            ->placeholder('Tidak Ada No Hp Kepala Sekolah'),
                        TextEntry::make(
                            'curriculum_deputies',
                        )->label('Wakakurikulum')->placeholder('Tidak Ada Wakakurikulum'),
                        PhoneEntry::make('curriculum_deputies_phone')
                            ->label('No Hp Wakakurikulum')
                            ->displayFormat(PhoneInputNumberType::NATIONAL)
                            ->placeholder('Tidak Ada No Hp Wakakurikulum'),
                        TextEntry::make(
                            'counselor_coordinators',
                        )->label('Koordinator BK')->placeholder('Tidak Ada Koordinator BK'),
                        PhoneEntry::make('counselor_coordinators_phone')
                            ->label('No Hp Koordinator BK')
                            ->displayFormat(PhoneInputNumberType::NATIONAL)
                            ->placeholder('Tidak Ada No Hp Koordinator BK'),
                        TextEntry::make('proctors')->label(
                            'Proktor',
                        )->placeholder('Tidak Ada Proktor'),
                        PhoneEntry::make('proctors_phone')
                            ->label('No Hp Proktor')
                            ->displayFormat(PhoneInputNumberType::NATIONAL)
                            ->placeholder('Tidak Ada No Hp Proktor'),
                    ]),
                    Fieldset::make('')->schema([
                        TextEntry::make('date_register')
                            ->label('Tanggal Pendaftaran')
                            ->dateTime('l, jS F Y H:i')
                            ->placeholder('Tidak Ada Tanggal Pendaftaran'),
                        TextEntry::make(
                            'implementation_estimate',
                        )
                            ->label('Estimasi Pelaksanaan')
                            ->dateTime('l, jS F Y H:i')
                            ->placeholder('Tidak Ada Estimasi Pelaksanaan'),
                    ]),
                ]),
            \Filament\Schemas\Components\Section::make('Service')
                ->description('Detail Data Service')
                ->schema([
                    Fieldset::make('')->schema([
                        TextEntry::make('group')
                            ->label('Grup')
                            ->dateTime('l, jS F Y')
                            ->placeholder('Belum Terjadwal'),
                        TextEntry::make('bimtek')
                            ->label('Bimtek')
                            ->dateTime('l, jS F Y')
                            ->placeholder('Belum Terjadwal'),
                    ]),

                    Fieldset::make('')->schema([
                        TextEntry::make(
                            'account_count_created',
                        )
                            ->label('Akun Dibuat')
                            ->placeholder('Belum Terbuat'),
                        TextEntry::make(
                            'implementer_count',
                        )
                            ->label('Pelaksanaan')
                            ->placeholder('Belum terbuat'),
                        TextEntry::make('difference')
                            ->label('Selisih')
                            ->placeholder('Belum terbuat'),
                    ]),

                    Fieldset::make('Konsultasi')
                        ->schema([
                            IconEntry::make(
                                'students_download',
                            )
                                ->label('Download Siswa')
                                ->icon(
                                    fn(string $state): string => match (
                                    $state
                                ) {
                                        'YA' => 'heroicon-s-check-circle',
                                        'TIDAK' => 'heroicon-s-x-circle',
                                    },
                                )
                                ->color(
                                    fn(string $state): string => match (
                                    $state
                                ) {
                                        'YA' => 'success',
                                        'TIDAK' => 'danger',
                                    },
                                )
                                ->placeholder('Tidak Ada Status'),
                            IconEntry::make(
                                'schools_download',
                            )
                                ->label('Download Sekolah')
                                ->icon(
                                    fn(string $state): string => match (
                                    $state
                                ) {
                                        'YA' => 'heroicon-s-check-circle',
                                        'TIDAK' => 'heroicon-s-x-circle',
                                    },
                                )
                                ->color(
                                    fn(string $state): string => match (
                                    $state
                                ) {
                                        'YA' => 'success',
                                        'TIDAK' => 'danger',
                                    },
                                )
                                ->placeholder('Tidak Ada Status'),
                            IconEntry::make('pm')
                                ->label('PM')
                                ->icon(
                                    fn(string $state): string => match (
                                    $state
                                ) {
                                        'YA' => 'heroicon-s-check-circle',
                                        'TIDAK' => 'heroicon-s-x-circle',
                                    },
                                )
                                ->color(
                                    fn(string $state): string => match (
                                    $state
                                ) {
                                        'YA' => 'success',
                                        'TIDAK' => 'danger',
                                    },
                                )
                                ->placeholder('Tidak Ada Status'),
                        ])
                        ->columns(3),

                    Fieldset::make('')->schema([
                        TextEntry::make(
                            'counselor_consultation_date',
                        )
                            ->label('Konsul BK')
                            ->dateTime('l, jS F Y')
                            ->placeholder('Belum Terjadwal'),
                        TextEntry::make(
                            'student_consultation_date',
                        )
                            ->label('Konsul Siswa')
                            ->dateTime('l, jS F Y')
                            ->placeholder('Belum Terjadwal'),
                    ]),
                ])
                ->columns(2),
        ];
    }

    public static function filters(): array
    {
        return [
            SelectFilter::make('periode')
                ->label('Periode')
                ->options(Periode::list())
                ->preload(),
            SelectFilter::make('education_level')
                ->label('Jenjang')
                ->options(Jenjang::list())
                ->preload()
                ->indicator('Jenjang'),
            SelectFilter::make('type')
                ->label('Program')
                ->options(Program::list())
                ->preload()
                ->indicator('Program'),
            SelectFilter::make('users_id')
                ->label('User')
                ->options(function () {
                    return User::all()
                        ->pluck('name', 'id')
                        ->toArray();
                })
                ->preload()
                ->indicator('user'),
            SelectFilter::make('status_color')
                ->label('Status Warna')
                ->options([
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
                        fn(Builder $q) => $q->where('color', $data['value']),
                    );
                }),
        ];
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

    public static function actions(): array
    {
        return [
            ViewAction::make(),
            // Tables\Actions\EditAction::make(),
        ];
    }

    public static function bulkActions(): array
    {
        return [
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ];
    }
}
