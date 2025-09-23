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
            [
                'name' => 'Input Data',
                'description' => 'Data awal telah diinput oleh tim Akademik',
                'color' => 'yellow',
                'order' => 1,
                'category' => 'akademik',
            ],
            [
                'name' => 'Membuat Grup WA',
                'description' => 'Grup WhatsApp telah dibuat untuk koordinasi',
                'color' => 'yellow',
                'order' => 2,
                'category' => 'akademik',
            ],
            [
                'name' => 'Bimtek',
                'description' => 'Pelatihan dan pembimbingan teknis telah dilaksanakan',
                'color' => 'yellow',
                'order' => 3,
                'category' => 'akademik',
            ],
            [
                'name' => 'Pelaksanaan',
                'description' => 'Pelaksanaan utama telah selesai oleh tim Teknisi',
                'color' => 'blue',
                'order' => 4,
                'category' => 'teknisi',
            ],
            [
                'name' => 'Pembacaan Hasil',
                'description' => 'Hasil telah dibaca dan dianalisis',
                'color' => 'blue',
                'order' => 5,
                'category' => 'teknisi',
            ],
            [
                'name' => 'Pelaporan dan Administrasi',
                'description' => 'Laporan dan administrasi telah selesai diproses',
                'color' => 'blue',
                'order' => 6,
                'category' => 'teknisi',
            ],
            [
                'name' => 'Pembayaran',
                'description' => 'Pembayaran telah selesai diproses oleh tim Finance',
                'color' => 'green',
                'order' => 7,
                'category' => 'finance',
            ],
            [
                'name' => 'Support Sekolah (SS)',
                'description' => 'Support sekolah telah diberikan',
                'color' => 'green',
                'order' => 8,
                'category' => 'finance',
            ],
        ];

        foreach ($statuses as $status) {
            Status::create($status);
        }
    }
}