<?php

namespace App\Filament\User\Resources\Salesforce\Infolists;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\IconSize;
use Illuminate\Database\Eloquent\Model;
use Ysfkaya\FilamentPhoneInput\Infolists\PhoneEntry;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;

class SalesInfolist
{
    public static function configure(Schema $schema, ?Model $record = null): Schema
    {
        return $schema->schema([
            // Status Section
            Section::make('Status')
                ->description('Progres Activity Saat Ini')
                ->schema([
                    Fieldset::make('Informasi Status')
                        ->schema([
                            TextEntry::make('status.name')
                                ->label('Status')
                                ->badge()
                                ->color(fn ($state) => $record->latestStatusLog?->status?->color ?? 'gray'),
                            IconEntry::make('latestStatusLog.status.order')
                                ->label('Icon Status')
                                ->icon(function ($state) {
                                    static $iconByOrder;

                                    if ($iconByOrder === null) {
                                        $iconByOrder = \App\Models\Status::query()
                                            ->pluck('icon', 'order')
                                            ->all();
                                    }

                                    $order = (int) $state;

                                    return $iconByOrder[$order] ?? 'heroicon-m-clock';
                                })
                                ->color(function ($state) {
                                    static $colorByOrder;

                                    if ($colorByOrder === null) {
                                        $colorByOrder = \App\Models\Status::query()
                                            ->pluck('color', 'order')
                                            ->all();
                                    }

                                    $order = (int) $state;
                                    $raw = strtolower((string) ($colorByOrder[$order] ?? ''));

                                    return match ($raw) {
                                        'green' => 'green',
                                        'blue' => 'blue',
                                        'yellow' => 'yellow',
                                        'red' => 'red',
                                        default => 'gray',
                                    };
                                })
                                ->default('red')
                                ->size(IconSize::Large),
                        ]),
                ])
                ->collapsible()
                ->columns(1),

            // Program Info Section
            Section::make('Program')
                ->description('Informasi Program')
                ->schema([
                    Fieldset::make('Detail Program')
                        ->schema([
                            TextEntry::make('type')
                                ->label('Program')
                                ->badge()
                                ->color('primary'),
                            TextEntry::make('periode')
                                ->label('Periode')
                                ->badge()
                                ->color('success'),
                            TextEntry::make('years')
                                ->label('Tahun')
                                ->badge()
                                ->color('info'),
                        ])
                        ->columns(3),
                ])
                ->collapsible(),

            // School Info Section
            Section::make('Sekolah')
                ->description('Detail Data Sekolah')
                ->schema([
                    Fieldset::make('Informasi Sekolah')
                        ->schema([
                            TextEntry::make('schools')
                                ->label('Nama Sekolah')
                                ->weight('bold'),
                            TextEntry::make('class')
                                ->label('Kelas'),
                            TextEntry::make('education_level')
                                ->label('Jenjang')
                                ->badge()
                                ->color('warning'),
                            TextEntry::make('description')
                                ->label('Keterangan')
                                ->badge()
                                ->color(fn ($state) => $state === 'ABK' ? 'danger' : 'gray'),
                            TextEntry::make('schools_type')
                                ->label('Negeri / Swasta')
                                ->badge()
                                ->color(fn ($state) => $state === 'NEGERI' ? 'success' : 'gray'),
                            TextEntry::make('student_count')
                                ->label('Jumlah Siswa')
                                ->numeric(),
                        ])
                        ->columns(3),
                ])
                ->collapsible(),

            // Location Section
            Section::make('Lokasi')
                ->description('Informasi Lokasi Sekolah')
                ->schema([
                    Fieldset::make('Detail Lokasi')
                        ->schema([
                            TextEntry::make('provinces')
                                ->label('Provinsi'),
                            TextEntry::make('regencies')
                                ->label('Kota / Kabupaten'),
                            TextEntry::make('area')
                                ->label('Wilayah')
                                ->default('-'),
                            TextEntry::make('district')
                                ->label('Kecamatan'),
                        ])
                        ->columns(2),
                ])
                ->collapsible(),

            // Personnel Section
            Section::make('Personil')
                ->description('Struktur Personil Sekolah')
                ->schema([
                    Fieldset::make('Kepala Sekolah')
                        ->schema([
                            TextEntry::make('principal')
                                ->label('Nama')
                                ->weight('bold'),
                            PhoneEntry::make('principal_phone')
                                ->label('No Handphone')
                                ->displayFormat(PhoneInputNumberType::NATIONAL),
                        ])
                        ->columns(2),

                    Fieldset::make('Wakakurikulum')
                        ->schema([
                            TextEntry::make('curriculum_deputies')
                                ->label('Nama')
                                ->weight('bold'),
                            PhoneEntry::make('curriculum_deputies_phone')
                                ->label('No Handphone')
                                ->displayFormat(PhoneInputNumberType::NATIONAL),
                        ])
                        ->columns(2),

                    Fieldset::make('Koordinator BK')
                        ->schema([
                            TextEntry::make('counselor_coordinators')
                                ->label('Nama')
                                ->weight('bold'),
                            PhoneEntry::make('counselor_coordinators_phone')
                                ->label('No Handphone')
                                ->displayFormat(PhoneInputNumberType::NATIONAL),
                        ])
                        ->columns(2),

                    Fieldset::make('Proktor')
                        ->schema([
                            TextEntry::make('proctors')
                                ->label('Nama')
                                ->weight('bold'),
                            PhoneEntry::make('proctors_phone')
                                ->label('No Handphone')
                                ->displayFormat(PhoneInputNumberType::NATIONAL),
                        ])
                        ->columns(2),
                ])
                ->collapsible()
                ->columns(2),

            // Schedule Section
            Section::make('Jadwal')
                ->description('Informasi Waktu Pelaksanaan')
                ->schema([
                    Fieldset::make('Detail Jadwal')
                        ->schema([
                            TextEntry::make('date_register')
                                ->label('Tanggal Pendaftaran')
                                ->dateTime('l, jS F Y H:i')
                                ->icon('heroicon-o-calendar')
                                ->iconPosition(IconPosition::Before),
                            TextEntry::make('implementation_estimate')
                                ->label('Estimasi Pelaksanaan')
                                ->dateTime('l, jS F Y H:i')
                                ->icon('heroicon-o-clock')
                                ->iconPosition(IconPosition::Before),
                        ])
                        ->columns(2),
                ])
                ->collapsible(),

            // Notes Section
            Section::make('Catatan')
                ->description('Catatan Tambahan')
                ->schema([
                    TextEntry::make('notes')
                        ->label('Catatan')
                        ->markdown()
                        ->default('-'),
                ])
                ->collapsible(),

            // User Info Section
            Section::make('Sales')
                ->description('Informasi Sales')
                ->schema([
                    Fieldset::make('Detail Sales')
                        ->schema([
                            TextEntry::make('users.name')
                                ->label('Nama Sales')
                                ->badge()
                                ->color('primary'),
                        ]),
                ])
                ->collapsible(),
        ]);
    }
}
