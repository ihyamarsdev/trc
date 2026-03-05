<?php

namespace App\Filament\User\Resources\Service\Infolists;

use App\Models\Status;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconSize;
use Illuminate\Database\Eloquent\Model;

class ServiceInfolist
{
    public static function configure(Schema $schema, Model $record): Schema
    {
        $type = $record->type ?? 'apps';
        $meta = self::getMeta($type);

        return $schema
            ->schema([
                // Header Section dengan Status
                Section::make('Status')
                    ->description('Status aktivitas saat ini')
                    ->schema([
                        Fieldset::make('Informasi Status')
                            ->schema([
                                TextEntry::make('status.name')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn ($state) => $state?->color ?? 'gray')
                                    ->placeholder('Tidak Ada Status'),
                                IconEntry::make('latestStatusLog.status.order')
                                    ->label('Indikator')
                                    ->icon(function ($state) {
                                        $iconByOrder = Status::query()->pluck('icon', 'order')->all();
                                        $order = (int) $state;

                                        return $iconByOrder[$order] ?? 'heroicon-m-clock';
                                    })
                                    ->color(function ($state) {
                                        $colorByOrder = Status::query()->pluck('color', 'order')->all();
                                        $order = (int) $state;
                                        $raw = strtolower((string) ($colorByOrder[$order] ?? ''));

                                        return match ($raw) {
                                            'green' => 'success',
                                            'blue' => 'blue',
                                            'yellow' => 'warning',
                                            'red' => 'danger',
                                            default => 'gray',
                                        };
                                    })
                                    ->size(IconSize::Large)
                                    ->placeholder('Tidak Ada Status'),
                            ]),
                    ])
                    ->columns(2)
                    ->collapsible(),

                // Program Info
                Section::make($meta['name'])
                    ->description($meta['description'])
                    ->schema([
                        Fieldset::make('Periode & Tahun')
                            ->schema([
                                TextEntry::make('periode')
                                    ->label('Periode')
                                    ->badge()
                                    ->color('primary')
                                    ->placeholder('Tidak Ada Periode'),
                                TextEntry::make('years')
                                    ->label('Tahun Ajaran')
                                    ->placeholder('Tidak Ada Tahun'),
                            ])
                            ->columns(2),
                        Fieldset::make('Informasi Sekolah')
                            ->schema([
                                TextEntry::make('schools')
                                    ->label('Nama Sekolah')
                                    ->placeholder('Tidak Ada Sekolah'),
                                TextEntry::make('class')
                                    ->label('Kelas')
                                    ->placeholder('Tidak Ada Kelas'),
                                TextEntry::make('education_level')
                                    ->label('Jenjang')
                                    ->badge()
                                    ->color('info')
                                    ->placeholder('Tidak Ada Jenjang'),
                                TextEntry::make('student_count')
                                    ->label('Jumlah Siswa')
                                    ->numeric()
                                    ->placeholder('0'),
                            ])
                            ->columns(2),
                        Fieldset::make('Lokasi')
                            ->schema([
                                TextEntry::make('provinces')
                                    ->label('Provinsi')
                                    ->placeholder('Tidak Ada Provinsi'),
                                TextEntry::make('regencies')
                                    ->label('Kota / Kabupaten')
                                    ->placeholder('Tidak Ada Kota/Kab'),
                                TextEntry::make('area')
                                    ->label('Wilayah')
                                    ->placeholder('-'),
                            ])
                            ->columns(3),
                        Fieldset::make('Informasi User')
                            ->schema([
                                TextEntry::make('users.name')
                                    ->label('Sales')
                                    ->placeholder('Tidak Ada User'),
                            ])
                            ->columns(1),
                        Fieldset::make('Kontak Sekolah')
                            ->schema([
                                TextEntry::make('principal')
                                    ->label('Kepala Sekolah')
                                    ->placeholder('Tidak Ada Data'),
                                TextEntry::make('principal_phone')
                                    ->label('No. HP')
                                    ->placeholder('Tidak Ada Data')
                                    ->icon('heroicon-o-phone'),
                            ])
                            ->columns(2),
                    ])
                    ->columns(2)
                    ->collapsible(),

                // Service Details
                Section::make('Service')
                    ->description('Detail pelaksanaan service')
                    ->schema([
                        Fieldset::make('Jadwal')
                            ->schema([
                                TextEntry::make('group')
                                    ->label('Grup')
                                    ->dateTime('l, jS F Y')
                                    ->placeholder('Belum Terjadwal')
                                    ->icon('heroicon-o-calendar'),
                                TextEntry::make('bimtek')
                                    ->label('Bimtek')
                                    ->dateTime('l, jS F Y')
                                    ->placeholder('Belum Terjadwal')
                                    ->icon('heroicon-o-academic-cap'),
                            ])
                            ->columns(2),
                        Fieldset::make('Progress Akun')
                            ->schema([
                                TextEntry::make('account_count_created')
                                    ->label('Akun Dibuat')
                                    ->numeric()
                                    ->badge()
                                    ->color('success')
                                    ->placeholder('0'),
                                TextEntry::make('implementer_count')
                                    ->label('Pelaksanaan')
                                    ->numeric()
                                    ->badge()
                                    ->color('warning')
                                    ->placeholder('0'),
                                TextEntry::make('difference')
                                    ->label('Selisih')
                                    ->numeric()
                                    ->badge()
                                    ->color('danger')
                                    ->placeholder('0'),
                            ])
                            ->columns(3),
                        Fieldset::make('Konsultasi')
                            ->schema([
                                IconEntry::make('students_download')
                                    ->label('Download Siswa')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger')
                                    ->placeholder('Tidak Ada Data'),
                                IconEntry::make('schools_download')
                                    ->label('Download Sekolah')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger')
                                    ->placeholder('Tidak Ada Data'),
                                IconEntry::make('pm')
                                    ->label('PM')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger')
                                    ->placeholder('Tidak Ada Data'),
                            ])
                            ->columns(3),
                        Fieldset::make('Jadwal Konsultasi')
                            ->schema([
                                TextEntry::make('counselor_consultation_date')
                                    ->label('Konsul BK')
                                    ->dateTime('l, jS F Y')
                                    ->placeholder('Belum Terjadwal')
                                    ->icon('heroicon-o-phone'),
                                TextEntry::make('student_consultation_date')
                                    ->label('Konsul Siswa')
                                    ->dateTime('l, jS F Y')
                                    ->placeholder('Belum Terjadwal')
                                    ->icon('heroicon-o-user-group'),
                            ])
                            ->columns(2),
                    ])
                    ->columns(2)
                    ->collapsible(),

            ])
            ->columns(1);
    }

    protected static function getMeta(string $type): array
    {
        return match ($type) {
            'anbk' => [
                'name' => 'ANBK',
                'description' => 'Asesmen Nasional Berbasis Komputer',
            ],
            'apps' => [
                'name' => 'APPS',
                'description' => 'Asesmen Psikotes Potensi Siswa',
            ],
            'snbt' => [
                'name' => 'SNBT',
                'description' => 'Seleksi Nasional Berdasarkan Tes',
            ],
            'tka' => [
                'name' => 'TKA',
                'description' => 'Test Kemampuan Akademik',
            ],
            default => [
                'name' => 'Program',
                'description' => 'Detail Program',
            ],
        };
    }
}
