<?php

namespace App\Filament\User\Resources\Service\Forms;

use App\Filament\Enum\Jenjang;
use App\Filament\Enum\Periode;
use App\Filament\Enum\Program;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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
            Section::make('Form Service')
                ->description('Status, informasi program, dan data konsultasi')
                ->schema([
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
                                        ->where('order', '<=', 10)
                                        ->orderBy('order'),
                                )
                                ->searchable()
                                ->placeholder('Pilih status...')
                                ->columnSpan(1),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),

                    Section::make(fn (Get $get) => self::meta($get)['nameRegister'])
                        ->description(
                            fn (Get $get) => self::meta($get)['DescriptionRegister'],
                        )
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
                                ->label('Selisih')
                                ->readOnly()
                                ->numeric()
                                ->minValue(0)
                                ->live(),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),

                    Section::make('Konsultasi')
                        ->description('Data Konsultasi')
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
                                ])
                                ->columns(2),
                            Section::make('')
                                ->schema([
                                    DatePicker::make('counselor_consultation_date')
                                        ->label('Konsul BK')
                                        ->native(false)
                                        ->displayFormat('l, jS F Y'),
                                    DatePicker::make('student_consultation_date')
                                        ->label('Konsul Siswa')
                                        ->native(false)
                                        ->displayFormat('l, jS F Y'),
                                ])
                                ->columns(2),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),
                ])
                ->columns(['lg' => 12])
                ->columnSpanFull(),
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
                        fn ($record) => match ($record->latestStatusLog?->status?->color) {
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
                    fn ($state) => Carbon::parse($state)->translatedFormat(
                        'l, jS F Y',
                    ),
                ),
            TextColumn::make('bimtek')
                ->label('Bimtek')
                ->formatStateUsing(
                    fn ($state) => Carbon::parse($state)->translatedFormat(
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
                    fn ($state) => Carbon::parse($state)->translatedFormat(
                        'l, jS F Y',
                    ),
                ),
            TextColumn::make('student_consultation_date')
                ->label('Tanggal Konseling Siswa')
                ->formatStateUsing(
                    fn ($state) => Carbon::parse($state)->translatedFormat(
                        'l, jS F Y',
                    ),
                ),
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
                        fn (Builder $q) => $q->where('color', $data['value']),
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
