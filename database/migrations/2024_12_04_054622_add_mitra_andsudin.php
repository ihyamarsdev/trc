<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('registration_data', function (Blueprint $table) {
            $table->string('sudin')->nullable();

            $table->string('mitra_difference')->nullable();
            $table->string('mitra_net')->nullable();
            $table->string('mitra_subtotal')->nullable();

            $table->string('ss_difference')->nullable();
            $table->string('ss_net')->nullable();
            $table->string('ss_subtotal')->nullable();

            $table->string('dll_difference')->nullable();
            $table->string('dll_net')->nullable();
            $table->string('dll_subtotal')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registration_data', function (Blueprint $table) {
            //
        });
    }
};
