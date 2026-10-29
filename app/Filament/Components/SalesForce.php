<?php

namespace App\Filament\Components;

use App\Filament\Components\Support\HasProgramMetadata;
use App\Filament\Components\Support\InfolistSchema;
use App\Filament\Components\Support\SharedSchema;
use App\Filament\Enum\Jenjang;
use App\Filament\Enum\Periode;
use App\Filament\Enum\Program;
use App\Models\Status;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class SalesForce
{
    use HasProgramMetadata;

    public static function schema(): array
    {
        $required = fn (string $label): HtmlString => new HtmlString(
            '<span style="color: #ef4444;">'.$label.'</span>',
        );
        $upper = fn (?string $state): string => Str::upper($state);

        return [
            Section::make('Program & Periode')
                ->description('Pilih Program, Periode dan Tahun Ajaran')
                ->icon('heroicon-o-calendar-days')
                ->columns(['default' => 1, 'sm' => 3])
                ->schema([
                    Select::make('type')
                        ->label('Program')
                        ->options(Program::list()),
                    Select::make('periode')
                        ->label('Periode')
                        ->options(Periode::list()),
                    TextInput::make('years')->label('Tahun')->maxLength(255),
                ]),

            Section::make('Data Sekolah')
                ->description('Masukkan Detail Data Sekolah')
                ->icon('heroicon-o-building-library')
                ->columns(['default' => 1, 'sm' => 2])
                ->schema([
                    TextInput::make('schools')
                        ->label($required('Nama Sekolah'))
                        ->nullable()
                        ->dehydrateStateUsing($upper)
                        ->maxLength(255)
                        ->live()
                        ->columnSpanFull(),
                    TextInput::make('class')->label('Kelas')->maxLength(10),
                    Select::make('education_level')
                        ->label('Jenjang')
                        ->options(Jenjang::list())
                        ->native(false),
                    Select::make('description')
                        ->label('Keterangan')
                        ->options([
                            'ABK' => 'ABK',
                            'NON-ABK' => 'NON ABK',
                        ])
                        ->native(false),
                    Select::make('schools_type')
                        ->label('Negeri / Swasta')
                        ->options([
                            'NEGERI' => 'NEGERI',
                            'SWASTA' => 'SWASTA',
                        ])
                        ->native(false),
                    TextInput::make('student_count')
                        ->label($required('Jumlah Siswa'))
                        ->numeric(),
                    ...SharedSchema::locationFields(),
                ]),

            Section::make('Kontak Sekolah')
                ->description('Penanggung jawab dan nomor handphone')
                ->icon('heroicon-o-user-group')
                ->columns(['default' => 1, 'sm' => 2])
                ->schema([
                    TextInput::make('principal')
                        ->label($required('Nama Kepala Sekolah'))
                        ->nullable()
                        ->dehydrateStateUsing($upper)
                        ->maxLength(255)
                        ->live(),
                    PhoneInput::make('principal_phone')
                        ->label($required('No Handphone Kepala Sekolah'))
                        ->defaultCountry('ID')
                        ->live(),
                    TextInput::make('curriculum_deputies')
                        ->label($required('Wakakurikulum'))
                        ->nullable()
                        ->dehydrateStateUsing($upper)
                        ->maxLength(50)
                        ->live(),
                    PhoneInput::make('curriculum_deputies_phone')
                        ->label($required('No Handphone Wakakurikulum'))
                        ->defaultCountry('ID')
                        ->live(),
                    TextInput::make('counselor_coordinators')
                        ->label('Koordinator BK')
                        ->nullable()
                        ->dehydrateStateUsing($upper)
                        ->maxLength(255),
                    PhoneInput::make('counselor_coordinators_phone')
                        ->label('No Handphone Koordinator BK')
                        ->defaultCountry('ID'),
                    TextInput::make('proctors')
                        ->label('Proktor')
                        ->nullable()
                        ->dehydrateStateUsing($upper)
                        ->maxLength(255),
                    PhoneInput::make('proctors_phone')
                        ->label('No Handphone Proktor')
                        ->defaultCountry('ID'),
                ]),

            Section::make('Jadwal & Catatan')
                ->icon('heroicon-o-clock')
                ->columns(['default' => 1, 'sm' => 2])
                ->schema([
                    DateTimePicker::make('date_register')
                        ->label($required('Tanggal Pendaftaran'))
                        ->native(false)
                        ->seconds(false)
                        ->displayFormat('l, jS F Y H:i'),
                    DateTimePicker::make('implementation_estimate')
                        ->label($required('Estimasi Pelaksanaan'))
                        ->native(false)
                        ->seconds(false)
                        ->displayFormat('l, jS F Y H:i')
                        ->live(),
                    Textarea::make('notes')
                        ->label('Catatan')
                        ->autosize()
                        ->columnSpanFull(),
                ]),

            Section::make('Status')
                ->description('Isi sesuai dengan status saat ini')
                ->icon('heroicon-o-flag')
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
                                ->orderBy('order'),
                        )
                        ->searchable()
                        ->placeholder('Pilih status...')
                        ->live()
                        ->afterStateUpdated(function (Set $set, $state) {
                            if ($state) {
                                $color = Status::find($state)?->color;
                                $set('status_color', $color);
                            } else {
                                $set('status_color', null);
                            }
                        }),
                ]),
        ];
    }

    public static function columns(): array
    {
        return SharedSchema::columns();
    }

    public static function infolist(Model $record): array
    {
        return InfolistSchema::compose($record);
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
            Tables\Filters\SelectFilter::make('status_color')
                ->label('Status Warna')
                ->options([
                    'red' => 'Merah',
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

    public static function getRoles(): array
    {
        return ['sales'];
    }

    public static function actions(): array
    {
        return [Tables\Actions\ViewAction::make()];
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
