<?php

namespace App\Filament\User\Resources\Salesforce\Infolists;

use App\Models\Status;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconSize;
use Illuminate\Database\Eloquent\Model;
use Ysfkaya\FilamentPhoneInput\Infolists\PhoneEntry;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;

class SalesInfolist
{
    public static function configure(Schema $schema, ?Model $record = null): Schema
    {
        $type = $record?->type ?? 'apps';
        $meta = self::getMeta($type);

        return $schema
            ->schema([
                Section::make('Status')
                    ->description('Status aktivitas saat ini')
                    ->schema([
                        Fieldset::make('Informasi Status')
                            ->schema([
                                TextEntry::make('status.name')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn ($state) => $record?->latestStatusLog?->status?->color ?? 'gray')
                                    ->placeholder('Tidak Ada Status'),
                                IconEntry::make('latestStatusLog.status.order')
                                    ->label('Indikator')
                                    ->icon(function ($state) {
                                        static $iconByOrder;

                                        if ($iconByOrder === null) {
                                            $iconByOrder = Status::query()->pluck('icon', 'order')->all();
                                        }

                                        return $iconByOrder[(int) $state] ?? 'heroicon-m-clock';
                                    })
                                    ->color(function ($state) {
                                        static $colorByOrder;

                                        if ($colorByOrder === null) {
                                            $colorByOrder = Status::query()->pluck('color', 'order')->all();
                                        }

                                        $raw = strtolower((string) ($colorByOrder[(int) $state] ?? ''));

                                        return match ($raw) {
                                            'green' => 'green',
                                            'blue' => 'blue',
                                            'yellow' => 'yellow',
                                            'red' => 'red',
                                            default => 'gray',
                                        };
                                    })
                                    ->default('red')
                                    ->size(IconSize::Large)
                                    ->placeholder('Tidak Ada Status'),
                            ]),
                    ])
                    ->columns(2)
                    ->collapsible(),

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
                                TextEntry::make('description')
                                    ->label('Keterangan')
                                    ->placeholder('Tidak Ada Keterangan'),
                                TextEntry::make('schools_type')
                                    ->label('Negeri / Swasta')
                                    ->placeholder('Tidak Ada Data'),
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
                                    ->placeholder('Tidak Ada Kota / Kabupaten'),
                                TextEntry::make('district')
                                    ->label('Kecamatan')
                                    ->placeholder('Tidak Ada Kecamatan'),
                                TextEntry::make('area')
                                    ->label('Wilayah')
                                    ->placeholder('-'),
                            ])
                            ->columns(2),
                        Fieldset::make('Informasi User')
                            ->schema([
                                TextEntry::make('users.name')
                                    ->label('Nama Sales')
                                    ->placeholder('Tidak Ada Sales'),
                            ])
                            ->columns(1),
                        Fieldset::make('Kontak Sekolah')
                            ->schema([
                                TextEntry::make('principal')
                                    ->label('Kepala Sekolah')
                                    ->placeholder('Tidak Ada Data'),
                                PhoneEntry::make('principal_phone')
                                    ->label('No Handphone')
                                    ->placeholder('Tidak Ada Data')
                                    ->displayFormat(PhoneInputNumberType::NATIONAL),
                            ])
                            ->columns(2),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Sales')
                    ->description('Detail aktivitas sales dan personil')
                    ->schema([
                        Fieldset::make('Personil Sekolah')
                            ->schema([
                                TextEntry::make('curriculum_deputies')
                                    ->label('Wakakurikulum')
                                    ->placeholder('Tidak Ada Data'),
                                PhoneEntry::make('curriculum_deputies_phone')
                                    ->label('No HP Wakakurikulum')
                                    ->placeholder('Tidak Ada Data')
                                    ->displayFormat(PhoneInputNumberType::NATIONAL),
                                TextEntry::make('counselor_coordinators')
                                    ->label('Koordinator BK')
                                    ->placeholder('Tidak Ada Data'),
                                PhoneEntry::make('counselor_coordinators_phone')
                                    ->label('No HP Koordinator BK')
                                    ->placeholder('Tidak Ada Data')
                                    ->displayFormat(PhoneInputNumberType::NATIONAL),
                                TextEntry::make('proctors')
                                    ->label('Proktor')
                                    ->placeholder('Tidak Ada Data'),
                                PhoneEntry::make('proctors_phone')
                                    ->label('No HP Proktor')
                                    ->placeholder('Tidak Ada Data')
                                    ->displayFormat(PhoneInputNumberType::NATIONAL),
                            ])
                            ->columns(2),
                        Fieldset::make('Jadwal')
                            ->schema([
                                TextEntry::make('date_register')
                                    ->label('Tanggal Pendaftaran')
                                    ->dateTime('l, jS F Y')
                                    ->icon('heroicon-o-calendar')
                                    ->placeholder('Belum Terjadwal'),
                                TextEntry::make('implementation_estimate')
                                    ->label('Estimasi Pelaksanaan')
                                    ->dateTime('l, jS F Y')
                                    ->icon('heroicon-o-clock')
                                    ->placeholder('Belum Terjadwal'),
                            ])
                            ->columns(2),
                        Fieldset::make('Catatan')
                            ->schema([
                                TextEntry::make('notes')
                                    ->label('Catatan')
                                    ->markdown()
                                    ->placeholder('-'),
                            ])
                            ->columns(1),
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
