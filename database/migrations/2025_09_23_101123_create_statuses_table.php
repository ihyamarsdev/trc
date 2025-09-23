<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama status
            $table->text('description')->nullable(); // Deskripsi status
            $table->string('color')->default('gray'); // Warna indikator (yellow, blue, green)
            $table->integer('order')->default(0); // Urutan status dalam alur
            $table->string('category')->default('general'); // Kategori status (akademik, teknisi, finance)
            $table->boolean('is_active')->default(true); // Status aktif atau tidak
            $table->timestamps();
        });

        // Insert default statuses
        DB::table('statuses')->insert([
            [
                'name' => 'Input Data',
                'description' => 'Data awal telah diinput oleh tim Akademik',
                'color' => 'yellow',
                'order' => 1,
                'category' => 'akademik',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Membuat Grup WA',
                'description' => 'Grup WhatsApp telah dibuat untuk koordinasi',
                'color' => 'yellow',
                'order' => 2,
                'category' => 'akademik',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bimtek',
                'description' => 'Pelatihan dan pembimbingan teknis telah dilaksanakan',
                'color' => 'yellow',
                'order' => 3,
                'category' => 'akademik',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pelaksanaan',
                'description' => 'Pelaksanaan utama telah selesai oleh tim Teknisi',
                'color' => 'blue',
                'order' => 4,
                'category' => 'teknisi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pembacaan Hasil',
                'description' => 'Hasil telah dibaca dan dianalisis',
                'color' => 'blue',
                'order' => 5,
                'category' => 'teknisi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pelaporan dan Administrasi',
                'description' => 'Laporan dan administrasi telah selesai diproses',
                'color' => 'blue',
                'order' => 6,
                'category' => 'teknisi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pembayaran',
                'description' => 'Pembayaran telah selesai diproses oleh tim Finance',
                'color' => 'green',
                'order' => 7,
                'category' => 'finance',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Support Sekolah (SS)',
                'description' => 'Support sekolah telah diberikan',
                'color' => 'green',
                'order' => 8,
                'category' => 'finance',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statuses');
    }
};
