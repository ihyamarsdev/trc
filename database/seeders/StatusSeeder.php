<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Status;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing statuses
        DB::table('statuses')->delete();

        // Create statuses
        $statuses = [
        // SALES (kuning)
        [
            'order' => 1,
            'name' => 'Registrasi / Input Data Sekolah',
            'description' => 'Data awal sekolah diregistrasikan / diinput oleh tim sales.',
            'color' => 'yellow',
            'category' => 'sales',
            'icon' => 'heroicon-m-document-plus',
        ],

        // PREPARATION (kuning)
        [
            'order' => 2,
            'name' => 'Buat Grup',
            'description' => 'Pembuatan grup komunikasi (WA/Telegram) untuk koordinasi.',
            'color' => 'yellow',
            'category' => 'preparation',
            'icon' => 'heroicon-m-user-group',
        ],
        [
            'order' => 3,
            'name' => 'Zoom Sosialisasi',
            'description' => 'Sosialisasi awal via Zoom untuk seluruh pemangku kepentingan.',
            'color' => 'yellow',
            'category' => 'preparation',
            'icon' => 'heroicon-m-video-camera',
        ],
        [
            'order' => 4,
            'name' => 'Jadwal Zoom Bimtek',
            'description' => 'Penentuan dan konfirmasi jadwal pelaksanaan sesi Bimtek.',
            'color' => 'yellow',
            'category' => 'preparation',
            'icon' => 'heroicon-m-calendar-days',
        ],

        // SERVICE (biru)
        [
            'order' => 5,
            'name' => 'Zoom Bimtek',
            'description' => 'Pelaksanaan Bimbingan Teknis melalui Zoom.',
            'color' => 'blue',
            'category' => 'service',
            'icon' => 'heroicon-m-presentation-chart-line',
        ],
        [
            'order' => 6,
            'name' => 'Pembacaan Hasil',
            'description' => 'Pembacaan dan pembahasan hasil pelaksanaan layanan.',
            'color' => 'blue',
            'category' => 'service',
            'icon' => 'heroicon-m-chart-bar',
        ],
        [
            'order' => 7,
            'name' => 'Penguatan Materi',
            'description' => 'Sesi penguatan materi/pendampingan lanjutan.',
            'color' => 'blue',
            'category' => 'service',
            'icon' => 'heroicon-m-bolt',
        ],

        // FINANCE (hijau)
        [
            'order' => 8,
            'name' => 'Kirim Invoice',
            'description' => 'Penerbitan dan pengiriman invoice kepada sekolah.',
            'color' => 'green',
            'category' => 'finance',
            'icon' => 'heroicon-m-receipt-percent',
        ],
        [
            'order' => 9,
            'name' => 'Check Pembayaran',
            'description' => 'Verifikasi dan pengecekan pembayaran dari sekolah.',
            'color' => 'green',
            'category' => 'finance',
            'icon' => 'heroicon-m-credit-card',
        ],
        [
            'order' => 10,
            'name' => 'Kirim Kuitansi',
            'description' => 'Penerbitan serta pengiriman kuitansi pembayaran.',
            'color' => 'green',
            'category' => 'finance',
            'icon' => 'heroicon-m-document-currency-dollar',
        ],
        [
            'order' => 11,
            'name' => 'Kirim Report',
            'description' => 'Pengiriman laporan/rekap hasil kegiatan ke pihak sekolah.',
            'color' => 'green',
            'category' => 'finance',
            'icon' => 'heroicon-m-document-text',
        ],
        [
            'order' => 12,
            'name' => 'Support Sekolah',
            'description' => 'Dukungan pasca layanan untuk kebutuhan sekolah.',
            'color' => 'green',
            'category' => 'finance',
            'icon' => 'heroicon-m-life-buoy',
        ],
    ];


        foreach ($statuses as $status) {
            Status::create($status);
        }
    }
}
