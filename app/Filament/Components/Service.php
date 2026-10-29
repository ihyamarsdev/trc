<?php

namespace App\Filament\Components;

use App\Filament\Components\Support\HasProgramMetadata;
use App\Filament\Components\Support\InfolistSchema;
use App\Filament\Components\Support\SharedSchema;
use App\Filament\Enum\Jenjang;
use App\Filament\Enum\Periode;
use App\Filament\Enum\Program;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Service
{
    use HasProgramMetadata;

    public static function formSchema(): array
    {
        return [
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
                ->columns(2),

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
                        ->label('Akun Dibuat')
                        ->readOnly()
                        ->numeric()
                        ->minValue(0)
                        ->live(),
                ])
                ->columns(2),

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
        ];
    }

    public static function columns(): array
    {
        return SharedSchema::columns();
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

    public static function infolist(Model $record): array
    {
        return InfolistSchema::compose($record, [InfolistSchema::PART_SERVICE]);
    }

    public static function filters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('periode')
                ->label('Periode')
                ->options(Periode::list())
                ->preload(),
            Tables\Filters\SelectFilter::make('education_level')
                ->label('Jenjang')
                ->options(Jenjang::list())
                ->preload()
                ->indicator('Jenjang'),
            Tables\Filters\SelectFilter::make('type')
                ->label('Program')
                ->options(Program::list())
                ->preload()
                ->indicator('Program'),
            Tables\Filters\SelectFilter::make('users_id')
                ->label('User')
                ->options(function () {
                    return User::query()
                        ->select(['id', 'name'])
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all();
                })
                ->preload()
                ->indicator('user'),
            Tables\Filters\SelectFilter::make('status_color')
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
        SharedSchema::getDifference($get, $set);
    }

    public static function actions(): array
    {
        return [
            Tables\Actions\ViewAction::make(),
            // Tables\Actions\EditAction::make(),
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

    public static function getRoles(): array
    {
        return ['service'];
    }
}
