<?php

namespace App\Filament\Components;

use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Infolists;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Columns\{TextColumn};
use Illuminate\Database\Eloquent\Builder;
use Creasi\Nusa\Models\{Province, Regency,  District};
use Filament\Forms\Components\{Select, TextInput, Section, DatePicker, Radio};

class Academic
{
    public static function formSchema(array $options = []): array
    {
        return [
            Section::make($options['nameRegister'])
                ->description($options['DescriptionRegister'])
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
                ])->columns(2),

            Section::make()
                ->schema([
                    Radio::make($options['radio_name_1'])
                        ->label($options['radio_label_1'])
                        ->options([
                            'ya' => 'Ya',
                            'tidak' => 'Tidak',
                        ])
                        ->inline(),
                    Radio::make($options['radio_name_2'])
                        ->label($options['radio_label_2'])
                        ->options([
                            'ya' => 'Ya',
                            'tidak' => 'Tidak',
                        ])
                        ->inline(),
                    DatePicker::make('counselor_consultation_date')
                        ->label('Konsul BK')
                        ->native(false)
                        ->displayFormat('l, jS F Y'),
                    DatePicker::make('student_consultation_date')
                        ->label('Konsul Siswa')
                        ->native(false)
                        ->displayFormat('l, jS F Y'),
                ])->columns(2),

              Section::make('Status')
                    ->description('Merah = Belum dikerjakan • Kuning = Sales & Akademik')
                    ->schema([
                        Select::make('status_id')
                            ->label('Status')
                            ->preload()
                                ->relationship(
                                    name: 'status',
                                    titleAttribute: 'name',
                                    modifyQueryUsing: fn (Builder $query) => $query
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
            TextColumn::make('no')
                ->rowIndex(),
            TextColumn::make('periode')
                ->label('Periode'),
            TextColumn::make('years')
                ->label('Tahun'),
            TextColumn::make('users.name')
                ->label('User')
                ->searchable(),
            TextColumn::make('schools')
                ->label('Sekolah')
                ->searchable(),
            TextColumn::make('education_level')
                ->label('Jenjang'),
            TextColumn::make('status_color')
                ->label('Status')
                ->badge()
                ->formatStateUsing(fn ($state) => ucfirst($state)) // Kuning/Biru/Hijau
                ->color(fn (string $state): string => match ($state) {
                    'green'  => 'green',
                    'blue'   => 'blue',
                    'yellow' => 'yellow',
                    'red'  => 'red',
                })
                ->sortable()
                ->default('red')
                ->toggleable(),
        ];
    }

    public static function infolist(array $options = []): array
    {
        return [
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
                                Infolists\Components\TextEntry::make('principal_phone')
                                    ->label('No Hp Kepala Sekolah'),
                                Infolists\Components\TextEntry::make('curriculum_deputies')
                                    ->label('Wakakurikulum'),
                                Infolists\Components\TextEntry::make('curriculum_deputies_phone')
                                    ->label('No Hp Wakakurikulum'),
                                Infolists\Components\TextEntry::make('counselor_coordinators')
                                    ->label('Koordinator BK'),
                                Infolists\Components\TextEntry::make('counselor_coordinators_phone')
                                    ->label('No Hp Koordinator BK'),
                                Infolists\Components\TextEntry::make('proctors')
                                    ->label('Proktor'),
                                Infolists\Components\TextEntry::make('proctors_phone')
                                    ->label('No Hp Proktor'),
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
                Infolists\Components\Section::make('Academic')
                    ->description('Detail Data Academik')
                    ->schema([

                        Infolists\Components\Fieldset::make('')
                            ->schema([
                                Infolists\Components\TextEntry::make('group')
                                    ->label('Grup')
                                    ->dateTime('l, jS F Y'),
                                Infolists\Components\TextEntry::make('bimtek')
                                    ->label('Bimtek')
                                    ->dateTime('l, jS F Y'),
                            ]),

                        Infolists\Components\Fieldset::make('')
                            ->schema([
                                Infolists\Components\TextEntry::make('account_count_created')
                                    ->label('Akun Dibuat'),
                                Infolists\Components\TextEntry::make('implementer_count')
                                    ->label('Pelaksanaan'),
                                Infolists\Components\TextEntry::make('difference')
                                    ->label('Selisih'),
                            ]),

                        Infolists\Components\Fieldset::make('Status')
                            ->schema([
                                Infolists\Components\IconEntry::make($options['radio_name_1'])
                                    ->label($options['radio_label_1'])
                                    ->icon(fn (string $state): string => match ($state) {
                                        'ya' => 'heroicon-s-check-circle',
                                        'tidak' => 'heroicon-s-x-circle',
                                    })
                                    ->color(fn (string $state): string => match ($state) {
                                        'ya' => 'success',
                                        'tidak' => 'danger',
                                    }),
                                Infolists\Components\IconEntry::make($options['radio_name_2'])
                                    ->label($options['radio_label_2'])
                                    ->icon(fn (string $state): string => match ($state) {
                                        'ya' => 'heroicon-s-check-circle',
                                        'tidak' => 'heroicon-s-x-circle',
                                    })
                                    ->color(fn (string $state): string => match ($state) {
                                        'ya' => 'success',
                                        'tidak' => 'danger',
                                    }),
                            ]),

                        Infolists\Components\Fieldset::make('')
                            ->schema([
                                Infolists\Components\TextEntry::make('counselor_consultation_date')
                                    ->label('Konsul BK')
                                    ->dateTime('l, jS F Y')
                                    ->default(null),
                                Infolists\Components\TextEntry::make('student_consultation_date')
                                    ->label('Konsul Siswa')
                                    ->dateTime('l, jS F Y')
                                    ->default(null),
                            ]),

                    ])->columns(2),
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
                ->searchable(),
            Tables\Filters\SelectFilter::make('users_id')
                ->label('User')
                ->options(function () {
                    return \App\Models\User::all()->pluck('name', 'id')->toArray();
                })
                ->preload()
                ->indicator('user'),
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
                    if (empty($data['value'])) return;

                    $query->whereHas('status', fn (Builder $q) =>
                        $q->where('color', $data['value'])
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

    public static function getRoles(): array
    {
        return [
            'academik', 'admin', 'teknisi'
        ];
    }
}
