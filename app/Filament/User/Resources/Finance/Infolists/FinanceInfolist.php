<?php

namespace App\Filament\User\Resources\Finance\Infolists;

use App\Models\Status;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Filament\Support\Enums\IconSize;

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
                                    ->color(fn($state) => $record->latestStatusLog?->status?->color ?? 'gray'),
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

                // Service Progress
                Section::make('Progress Service')
                    ->description('Status pelaksanaan service')
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
