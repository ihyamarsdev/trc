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
            // SALES (merah)
            [
                'order' => 1,
                'name' => 'Data Belum Diisi',
                'description' => 'Belum ada data yang diinput',
                'color' => 'red',
                'category' => 'general',
            ],

            // SALES (kuning)
            [
                'order' => 2,
                'name' => 'Registrasi / Input Data Sekolah',
                'description' => 'Data awal sekolah diregistrasikan / diinput oleh tim sales.',
                'color' => 'yellow',
                'category' => 'sales',
            ],

            // PREPARATION (kuning)
            [
                'order' => 3,
                'name' => 'Buat Grup',
                'description' => 'Pembuatan grup komunikasi (WA/Telegram) untuk koordinasi.',
                'color' => 'yellow',
                'category' => 'preparation',
            ],
            [
                'order' => 4,
                'name' => 'Zoom Sosialisasi',
                'description' => 'Sosialisasi awal via Zoom untuk seluruh pemangku kepentingan.',
                'color' => 'yellow',
                'category' => 'preparation',
            ],
            [
                'order' => 5,
                'name' => 'Jadwal Zoom Bimtek',
                'description' => 'Penentuan dan konfirmasi jadwal pelaksanaan sesi Bimtek.',
                'color' => 'yellow',
                'category' => 'preparation',
            ],

            // SERVICE (biru)
            [
                'order' => 6,
                'name' => 'Zoom Bimtek',
                'description' => 'Pelaksanaan Bimbingan Teknis melalui Zoom.',
                'color' => 'blue',
                'category' => 'service',
            ],
            [
                'order' => 7,
                'name' => 'Pembacaan Hasil',
                'description' => 'Pembacaan dan pembahasan hasil pelaksanaan layanan.',
                'color' => 'blue',
                'category' => 'service',
            ],
            [
                'order' => 8,
                'name' => 'Penguatan Materi',
                'description' => 'Sesi penguatan materi/pendampingan lanjutan.',
                'color' => 'blue',
                'category' => 'service',
            ],

            // FINANCE (hijau)
            [
                'order' => 9,
                'name' => 'Kirim Invoice',
                'description' => 'Penerbitan dan pengiriman invoice kepada sekolah.',
                'color' => 'green',
                'category' => 'finance',
            ],
            [
                'order' => 10,
                'name' => 'Check Pembayaran',
                'description' => 'Verifikasi dan pengecekan pembayaran dari sekolah.',
                'color' => 'green',
                'category' => 'finance',
            ],
            [
                'order' => 11,
                'name' => 'Kirim Kuitansi',
                'description' => 'Penerbitan serta pengiriman kuitansi pembayaran.',
                'color' => 'green',
                'category' => 'finance',
            ],
            [
                'order' => 12,
                'name' => 'Kirim Report',
                'description' => 'Pengiriman laporan/rekap hasil kegiatan ke pihak sekolah.',
                'color' => 'green',
                'category' => 'finance',
            ],
            [
                'order' => 13,
                'name' => 'Support Sekolah',
                'description' => 'Dukungan pasca layanan untuk kebutuhan sekolah.',
                'color' => 'green',
                'category' => 'finance',
            ],
        ];

        foreach ($statuses as $status) {
            Status::create($status);
        }
    }
}
