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
            $table->string('color')->default('red'); // Warna indikator (yellow, blue, green)
            $table->integer('order')->default(0); // Urutan status dalam alur
            $table->string('category')->default('general');  // Status aktif atau tidak
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statuses');
    }
};
