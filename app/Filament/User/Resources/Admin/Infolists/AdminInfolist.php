<?php

namespace App\Filament\User\Resources\Admin\Infolists;

use App\Models\Status;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconSize;
use Illuminate\Database\Eloquent\Model;

class AdminInfolist
{
    public static function configure(Schema $schema, Model $record): Schema
    {
        $type = $record->type ?? 'apps';
        $meta = self::getMeta($type);

        return $schema
            ->schema([
                // Header Section dengan Status
                Section::make('Status Aktivitas')
                    ->description('Status terkini dari aktivitas ini')
                    ->schema([
                        Fieldset::make('Status & Progress')
                            ->schema([
                                TextEntry::make('status.name')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn ($state) => $state?->color ?? 'gray')
                                    ->placeholder('Tidak Ada Status'),
                                IconEntry::make('latestStatusLog.status.order')
                                    ->label('Progress')
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
                                            'blue' => 'info',
                                            'yellow' => 'warning',
                                            'red' => 'danger',
                                            default => 'gray',
                                        };
                                    })
                                    ->size(IconSize::Large)
                                    ->placeholder('Menunggu Status'),
                            ])
                            ->columns(1),
                    ])
                    ->columns(2)
                    ->collapsible(false),

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
                                TextEntry::make('description')
                                    ->label('Keterangan')
                                    ->badge()
                                    ->placeholder('-'),
                                TextEntry::make('schools_type')
                                    ->label('Tipe Sekolah')
                                    ->badge()
                                    ->color(fn ($state) => $state === 'NEGERI' ? 'success' : 'warning')
                                    ->placeholder('-'),
                                TextEntry::make('student_count')
                                    ->label('Jumlah Siswa')
                                    ->numeric()
                                    ->placeholder('0'),
                            ])
                            ->columns(2),
                        Fieldset::make('Lokasi Sekolah')
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
                                TextEntry::make('district')
                                    ->label('Kecamatan')
                                    ->placeholder('Tidak Ada Kecamatan'),
                            ])
                            ->columns(2),
                    ])
                    ->columns(2)
                    ->collapsible(),

                // Service Details
                Section::make('Service & Akademik')
                    ->description('Detail pelaksanaan service akademik')
                    ->schema([
                        Fieldset::make('Jadwal Kegiatan')
                            ->schema([
                                TextEntry::make('group')
                                    ->label('Grup')
                                    ->dateTime('l, jS F Y')
                                    ->placeholder('Belum Terjadwal')
                                    ->icon('heroicon-o-calendar-days'),
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
                                    ->color('info')
                                    ->placeholder('0'),
                                TextEntry::make('difference')
                                    ->label('Selisih')
                                    ->numeric()
                                    ->badge()
                                    ->color('warning')
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
                                    ->placeholder('Belum'),
                                IconEntry::make('schools_download')
                                    ->label('Download Sekolah')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger')
                                    ->placeholder('Belum'),
                                IconEntry::make('pm')
                                    ->label('PM')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger')
                                    ->placeholder('Belum'),
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

                // Finance Info (Admin view)
                Section::make('Finance')
                    ->description('Data keuangan dan pembayaran')
                    ->schema([
                        Fieldset::make('Harga')
                            ->schema([
                                TextEntry::make('price')
                                    ->label('Harga SPJ')
                                    ->money('IDR')
                                    ->placeholder('Rp 0'),
                                TextEntry::make('net_2')
                                    ->label('Harga NET')
                                    ->money('IDR')
                                    ->placeholder('Rp 0'),
                            ])
                            ->columns(2),
                        Fieldset::make('Rincian Pembayaran')
                            ->schema([
                                TextEntry::make('option_price')
                                    ->label('Opsi Harga')
                                    ->badge()
                                    ->formatStateUsing(function (?string $state): string {
                                        if (! is_string($state) || $state === '') {
                                            return '-';
                                        }

                                        preg_match('/_(\d+)$/', $state, $matches);
                                        $count = $matches[1] ?? null;

                                        $label = match (true) {
                                            str_starts_with($state, 'implementer_') => 'JUMLAH PELAKSANAAN',
                                            str_starts_with($state, 'account_') => 'JUMLAH AKUN',
                                            default => strtoupper(str_replace('_', ' ', $state)),
                                        };

                                        return $count ? "{$label} ({$count})" : $label;
                                    })
                                    ->placeholder('-'),
                                TextEntry::make('total')
                                    ->label('Total')
                                    ->money('IDR')
                                    ->placeholder('Rp 0'),
                                TextEntry::make('total_net')
                                    ->label('Total Net')
                                    ->money('IDR')
                                    ->placeholder('Rp 0'),
                            ])
                            ->columns(3),
                        Fieldset::make('Jadwal Pembayaran')
                            ->schema([
                                TextEntry::make('invoice_date')
                                    ->label('Tanggal Invoice')
                                    ->dateTime('l, jS F Y')
                                    ->placeholder('Belum Ada'),
                                TextEntry::make('payment_date')
                                    ->label('Tanggal Pembayaran')
                                    ->dateTime('l, jS F Y')
                                    ->placeholder('Belum Lunas'),
                                TextEntry::make('spk')
                                    ->label('SPK Dikirim')
                                    ->dateTime('l, jS F Y')
                                    ->placeholder('Belum Dikirim'),
                                TextEntry::make('payment_name')
                                    ->label('Metode Pembayaran')
                                    ->badge()
                                    ->placeholder('-'),
                                IconEntry::make('payment_date')
                                    ->label('Status Pembayaran')
                                    ->boolean(fn ($state) => filled($state))
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-clock')
                                    ->trueColor('success')
                                    ->falseColor('warning'),
                            ])
                            ->columns(2),
                        Fieldset::make('Mitra')
                            ->schema([
                                TextEntry::make('mitra_difference')
                                    ->label('Selisih Siswa Sekolah')
                                    ->numeric()
                                    ->placeholder('0'),
                                TextEntry::make('mitra_net')
                                    ->label('Satuan Mitra')
                                    ->money('IDR')
                                    ->placeholder('Rp 0'),
                                TextEntry::make('mitra_subtotal')
                                    ->label('Subtotal Mitra')
                                    ->money('IDR')
                                    ->placeholder('Rp 0'),
                                TextEntry::make('ss_difference')
                                    ->label('SS')
                                    ->numeric()
                                    ->placeholder('0'),
                                TextEntry::make('ss_net')
                                    ->label('Satuan SS')
                                    ->money('IDR')
                                    ->placeholder('Rp 0'),
                                TextEntry::make('ss_subtotal')
                                    ->label('Subtotal SS')
                                    ->money('IDR')
                                    ->placeholder('Rp 0'),
                                TextEntry::make('dll_difference')
                                    ->label('DLL')
                                    ->numeric()
                                    ->placeholder('0'),
                                TextEntry::make('dll_net')
                                    ->label('Satuan DLL')
                                    ->money('IDR')
                                    ->placeholder('Rp 0'),
                                TextEntry::make('dll_subtotal')
                                    ->label('Subtotal DLL')
                                    ->money('IDR')
                                    ->placeholder('Rp 0'),
                            ])
                            ->columns(3),
                    ])
                    ->columns(2)
                    ->collapsible(),

                // Salesforce Info
                Section::make('Sales')
                    ->description('Detail data sales dan relasi sekolah')
                    ->schema([
                        Fieldset::make('Info Sales')
                            ->schema([
                                TextEntry::make('users.name')
                                    ->label('Sales')
                                    ->placeholder('Tidak Ada User'),
                                TextEntry::make('date_register')
                                    ->label('Tanggal Pendaftaran')
                                    ->dateTime('l, jS F Y H:i')
                                    ->placeholder('Tidak Ada Data'),
                                TextEntry::make('implementation_estimate')
                                    ->label('Estimasi Pelaksanaan')
                                    ->dateTime('l, jS F Y H:i')
                                    ->placeholder('Tidak Ada Data'),
                                TextEntry::make('notes')
                                    ->label('Catatan')
                                    ->markdown()
                                    ->placeholder('-'),
                            ])
                            ->columns(2),
                        Fieldset::make('Kontak Sekolah')
                            ->schema([
                                TextEntry::make('principal')
                                    ->label('Kepala Sekolah')
                                    ->placeholder('Tidak Ada Data'),
                                TextEntry::make('principal_phone')
                                    ->label('No. HP')
                                    ->placeholder('Tidak Ada Data')
                                    ->icon('heroicon-o-phone'),
                                TextEntry::make('curriculum_deputies')
                                    ->label('Wakakurikulum')
                                    ->placeholder('Tidak Ada Data'),
                                TextEntry::make('curriculum_deputies_phone')
                                    ->label('No. HP')
                                    ->placeholder('Tidak Ada Data')
                                    ->icon('heroicon-o-phone'),
                                TextEntry::make('counselor_coordinators')
                                    ->label('Koordinator BK')
                                    ->placeholder('Tidak Ada Data'),
                                TextEntry::make('counselor_coordinators_phone')
                                    ->label('No. HP')
                                    ->placeholder('Tidak Ada Data')
                                    ->icon('heroicon-o-phone'),
                                TextEntry::make('proctors')
                                    ->label('Proktor')
                                    ->placeholder('Tidak Ada Data'),
                                TextEntry::make('proctors_phone')
                                    ->label('No. HP')
                                    ->placeholder('Tidak Ada Data')
                                    ->icon('heroicon-o-phone'),
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
