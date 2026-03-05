<?php

namespace App\Filament\User\Resources\Finance\Infolists;

use App\Models\Status;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconSize;
use Illuminate\Database\Eloquent\Model;

class FinanceInfolist
{
    public static function configure(Schema $schema, Model $record): Schema
    {
        $type = $record->type ?? 'apps';
        $meta = self::getMeta($type);

        return $schema
            ->schema([
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
                    ->columns(2),

                // Program Info
                Section::make($meta['name'])
                    ->description($meta['description'])
                    ->schema([
                        Fieldset::make('Periode')
                            ->schema([
                                TextEntry::make('periode')
                                    ->label('Periode')
                                    ->badge()
                                    ->color('primary')
                                    ->placeholder('Tidak Ada Periode'),
                                TextEntry::make('years')
                                    ->label('Tahun')
                                    ->placeholder('Tidak Ada Tahun'),
                            ])
                            ->columns(2),
                        Fieldset::make('Informasi Sekolah')
                            ->schema([
                                TextEntry::make('schools')
                                    ->label('Nama Sekolah')
                                    ->placeholder('Tidak Ada Sekolah'),
                                TextEntry::make('student_count')
                                    ->label('Jumlah Siswa')
                                    ->numeric()
                                    ->placeholder('0'),
                            ])
                            ->columns(2),
                        Fieldset::make('Detail Sales')
                            ->schema([
                                TextEntry::make('users.name')
                                    ->label('Nama Sales')
                                    ->badge()
                                    ->color('primary')
                                    ->placeholder('Tidak Ada Sales'),
                                TextEntry::make('principal')
                                    ->label('Kepala Sekolah')
                                    ->placeholder('Tidak Ada Data'),
                                TextEntry::make('principal_phone')
                                    ->label('No. HP Kepala Sekolah')
                                    ->placeholder('Tidak Ada Data')
                                    ->icon('heroicon-o-phone'),
                            ])
                            ->columns(3),
                        Fieldset::make('Detail Service')
                            ->schema([
                                TextEntry::make('group')
                                    ->label('Jadwal Grup')
                                    ->dateTime('l, jS F Y')
                                    ->placeholder('Belum Terjadwal')
                                    ->icon('heroicon-o-calendar'),
                                TextEntry::make('bimtek')
                                    ->label('Jadwal Bimtek')
                                    ->dateTime('l, jS F Y')
                                    ->placeholder('Belum Terjadwal')
                                    ->icon('heroicon-o-academic-cap'),
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
                            ])
                            ->columns(2),
                    ])
                    ->collapsible(),

                // Finance Details
                Section::make('Detail Keuangan')
                    ->description('Informasi pembayaran dan keuangan')
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
                        Fieldset::make('Rincian')
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
                                    ->label('Total Dana')
                                    ->money('IDR')
                                    ->placeholder('Rp 0'),
                                TextEntry::make('total_net')
                                    ->label('Total Net')
                                    ->money('IDR')
                                    ->placeholder('Rp 0'),
                            ])
                            ->columns(3),
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
                        Fieldset::make('Pembayaran')
                            ->schema([
                                IconEntry::make('payment_date')
                                    ->label('Status Pembayaran')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger')
                                    ->placeholder('Belum Lunas'),
                                TextEntry::make('invoice_date')
                                    ->label('Tanggal Invoice')
                                    ->dateTime('l, jS F Y')
                                    ->placeholder('Belum Ada'),
                                TextEntry::make('spk')
                                    ->label('SPK Dikirim')
                                    ->dateTime('l, jS F Y')
                                    ->placeholder('Belum Dikirim'),
                                TextEntry::make('payment_name')
                                    ->label('Metode')
                                    ->badge()
                                    ->placeholder('-'),
                            ])
                            ->columns(2),
                    ])
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
