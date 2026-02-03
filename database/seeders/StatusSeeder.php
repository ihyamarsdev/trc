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
        // Create statuses
        $statuses = [
            [
                'id' => 28,
                'order' => 1,
                'name' => 'Aktifitas Marketing',
                'description' => 'Sales Bergerak ke sekolah untuk mencari registrasi baru.',
                'color' => 'red',
                'category' => 'sales',
                'icon' => 'heroicon-s-megaphone',
            ],
            [
                'id' => 29,
                'order' => 2,
                'name' => 'Registrasi / Input Data Sekolah',
                'description' => 'Data awal sekolah diregistrasikan / diinput oleh tim sales.',
                'color' => 'yellow',
                'category' => 'sales',
                'icon' => 'heroicon-s-document-plus',
            ],
            [
                'id' => 30,
                'order' => 3,
                'name' => 'Buat grup WA sekolah',
                'description' => 'Membuat grup WhatsApp resmi untuk koordinasi dengan pihak sekolah.',
                'color' => 'blue',
                'category' => 'service',
                'icon' => 'heroicon-s-chat-bubble-left-right',
            ],
            [
                'id' => 31,
                'order' => 4,
                'name' => 'Bimtek',
                'description' => 'Bimbingan teknis kepada sekolah mengenai alur dan penggunaan sistem.',
                'color' => 'blue',
                'category' => 'service',
                'icon' => 'heroicon-s-wrench-screwdriver',
            ],
            [
                'id' => 32,
                'order' => 5,
                'name' => 'Input data siswa',
                'description' => 'Pengumpulan dan input data peserta/siswa ke dalam sistem.',
                'color' => 'blue',
                'category' => 'service',
                'icon' => 'heroicon-s-clipboard-document',
            ],
            [
                'id' => 33,
                'order' => 6,
                'name' => 'Akun',
                'description' => 'Pembuatan dan distribusi akun untuk sekolah/koordinator/proktor.',
                'color' => 'blue',
                'category' => 'service',
                'icon' => 'heroicon-s-key',
            ],
            [
                'id' => 34,
                'order' => 7,
                'name' => 'Event',
                'description' => 'Penjadwalan kegiatan/ujian/simulasi sesuai kalender pelaksanaan.',
                'color' => 'blue',
                'category' => 'service',
                'icon' => 'heroicon-s-calendar-days',
            ],
            [
                'id' => 35,
                'order' => 8,
                'name' => 'Susulan',
                'description' => 'Penjadwalan ulang bagi peserta yang belum mengikuti kegiatan.',
                'color' => 'blue',
                'category' => 'service',
                'icon' => 'heroicon-s-clock',
            ],
            [
                'id' => 36,
                'order' => 9,
                'name' => 'Rekap download',
                'description' => 'Rekapitulasi dan unduh data hasil pelaksanaan dari sistem.',
                'color' => 'blue',
                'category' => 'service',
                'icon' => 'heroicon-s-arrow-down-tray',
            ],
            [
                'id' => 37,
                'order' => 10,
                'name' => 'Zoom fasilitas',
                'description' => 'Sesi Zoom untuk pendampingan teknis dan pengecekan fasilitas.',
                'color' => 'blue',
                'category' => 'service',
                'icon' => 'heroicon-s-video-camera',
            ],
            [
                'id' => 38,
                'order' => 11,
                'name' => 'Invoice',
                'description' => 'Penerbitan tagihan sesuai kesepakatan dan hasil rekap.',
                'color' => 'green',
                'category' => 'finance',
                'icon' => 'heroicon-s-document-text',
            ],
            [
                'id' => 39,
                'order' => 12,
                'name' => 'Check Bukti Transfer',
                'description' => 'Verifikasi bukti pembayaran/transfer dari pihak sekolah.',
                'color' => 'green',
                'category' => 'finance',
                'icon' => 'heroicon-s-banknotes',
            ],
            [
                'id' => 40,
                'order' => 13,
                'name' => 'SPJ',
                'description' => 'Penyusunan Surat Pertanggungjawaban dan kelengkapan administrasi.',
                'color' => 'green',
                'category' => 'finance',
                'icon' => 'heroicon-s-clipboard-document-check',
            ],
            [
                'id' => 41,
                'order' => 14,
                'name' => 'Kirim Invoice',
                'description' => 'Pengiriman invoice resmi ke sekolah/instansi terkait.',
                'color' => 'green',
                'category' => 'finance',
                'icon' => 'heroicon-s-paper-airplane',
            ],
            [
                'id' => 42,
                'order' => 15,
                'name' => 'Support Sekolah',
                'description' => 'Dukungan purnajual dan respons isu/permintaan bantuan sekolah.',
                'color' => 'green',
                'category' => 'finance',
                'icon' => 'heroicon-s-lifebuoy',
            ],
    ];


        foreach ($statuses as $status) {
            Status::updateOrCreate($status);
        }
    }
}
