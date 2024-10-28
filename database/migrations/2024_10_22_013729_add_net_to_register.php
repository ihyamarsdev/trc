<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('registration_data', function (Blueprint $table) {
            $table->string('net_2')->nullable();
            $table->string('student_count_1')->nullable();
            $table->string('student_count_2')->nullable();
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
